<?php

namespace App\Http\Controllers\API;

use App\Events\RegistrationOTPSendEvent;
use App\Http\Controllers\Controller;
use App\Interfaces\UserInterface;
use App\Models\User;
use App\Models\UserLoginActivity;
use App\Models\VerificationCodes;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use GuzzleHttp\Client;


class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private UserInterface $userInterface)
    {
    }

    /**
     * @OA\Post(
     *     path="/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     description="User's name",
     *                     example="John Doe"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     description="User's email address",
     *                     example="john@example.com"
     *                 ),
     *                 @OA\Property(
     *                     property="mobile",
     *                     type="string",
     *                     description="User's mobile number",
     *                     example="1234567890"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     format="password",
     *                     description="User's password (min 8 characters)",
     *                     example="password123"
     *                 ),
     *                 @OA\Property(
     *                     property="confirm_password",
     *                     type="string",
     *                     format="password",
     *                     description="Confirmation of user's password",
     *                     example="password123"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registration successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Registration successful. OTPs sent."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|min:10|max:10|unique:users,mobile_no',
            'password' => 'required|string|min:8|same:confirm_password',
            'confirm_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }

        return $this->userInterface->register($request);
    }

    /**
     * @OA\Post(
     *     path="/verify-registration-otp",
     *     summary="Verify Registration OTP",
     *     description="Verify the registration OTPs for email and mobile, then log in the user using JWT.",
     *     operationId="verifyRegistrationOTP",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     description="User's email",
     *                     example="user@example.com"
     *                 ),
     *                 @OA\Property(
     *                     property="mobile",
     *                     type="string",
     *                     description="User's mobile number",
     *                     example="1234567890"
     *                 ),
     *                 @OA\Property(
     *                     property="mobile_otp",
     *                     type="string",
     *                     description="OTP sent to the user's mobile",
     *                     example="123456"
     *                 ),
     *                 @OA\Property(
     *                     property="email_otp",
     *                     type="string",
     *                     description="OTP sent to the user's email",
     *                     example="654321"
     *                 ),
     *                 required={"email", "mobile", "mobile_otp", "email_otp"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully and user logged in",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="access_token",
     *                 type="string",
     *                 description="JWT access token",
     *                 example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
     *             ),
     *             @OA\Property(
     *                 property="token_type",
     *                 type="string",
     *                 description="Type of the token",
     *                 example="bearer"
     *             ),
     *             @OA\Property(
     *                 property="expires_in",
     *                 type="integer",
     *                 description="Token expiration time in seconds",
     *                 example=3600
     *             ),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 description="Authenticated user details"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Validation error message"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized or invalid OTP",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="OTP expired. Please resend OTP."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Error message"
     *             )
     *         )
     *     )
     * )
     */
    public function verifyRegistrationOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'mobile' => 'required|digits:10|exists:users,mobile_no',
            'mobile_otp' => 'required|string',
            'email_otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }

        return $this->userInterface->verifyRegistrationOTP($request);
    }

    /**
     * @OA\Post(
     *     path="/resend-registration-otp",
     *     summary="Resend Registration OTP",
     *     description="Resend the registration OTPs for email and mobile.",
     *     operationId="resendRegistrationOTP",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="mobile",
     *                     type="string",
     *                     description="User's mobile number",
     *                     example="1234567890"
     *                 ),
     *                 required={"mobile"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP resent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="mobile_otp",
     *                 type="string",
     *                 description="OTP sent to the user's mobile",
     *                 example="123456"
     *             ),
     *             @OA\Property(
     *                 property="email_otp",
     *                 type="string",
     *                 description="OTP sent to the user's email",
     *                 example="654321"
     *             ),
     *             @OA\Property(
     *                 property="expire_at",
     *                 type="string",
     *                 description="OTP expiration time",
     *                 example="01 Jan 2024 01:23:45 PM"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="Notification message",
     *                 example="We have sent OTP your registered mobile number(******7890) & email(***@example.com). OTPs expire within 5 min."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Validation error message"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Error message"
     *             )
     *         )
     *     )
     * )
     */
    public function resendRegistrationOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|min:10|max:10|exists:users,mobile_no',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }
        return $this->userInterface->resendRegistrationOTP($request);
    }

    /**
     * @OA\Post(
     *     path="/forgot-password",
     *     summary="Forgot Password OTP Send",
     *     description="Forgot Password send OTPs for email and mobile.",
     *     operationId="forgotPassword",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="mobile",
     *                     type="string",
     *                     description="User's mobile number",
     *                     example="1234567890"
     *                 ),
     *                 required={"mobile"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP resent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="mobile_otp",
     *                 type="string",
     *                 description="OTP sent to the user's mobile",
     *                 example="123456"
     *             ),
     *             @OA\Property(
     *                 property="email_otp",
     *                 type="string",
     *                 description="OTP sent to the user's email",
     *                 example="123456"
     *             ),
     *             @OA\Property(
     *                 property="expire_at",
     *                 type="string",
     *                 description="OTP expiration time",
     *                 example="01 Jan 2024 01:23:45 PM"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="Notification message",
     *                 example="We have sent OTP your registered mobile number(******7890) & email(***@example.com). OTPs expire within 5 min."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Validation error message"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Error message"
     *             )
     *         )
     *     )
     * )
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|min:10|max:10|exists:users,mobile_no',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }

        return $this->userInterface->forgotPassword($request);
    }

    /**
     * @OA\Post(
     *     path="/verify-forgot-password-otp",
     *     summary="Verify OTP for forgot password",
     *     description="Verify the OTP sent to the user's mobile and email for resetting the password.",
     *     operationId="verifyForgotPasswordOTP",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Pass mobile number, mobile OTP, and email OTP",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="mobile",
     *                     type="string",
     *                     description="User's mobile number",
     *                     example="1234567890"
     *                 ),
     *                 @OA\Property(
     *                     property="mobile_otp",
     *                     type="string",
     *                     description="OTP received on mobile",
     *                     example="123456"
     *                 ),
     *                 @OA\Property(
     *                     property="email_otp",
     *                     type="string",
     *                     description="OTP received on email",
     *                     example="123456"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="OTP verified successfully. Verification completed."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or OTP expired",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="OTP expired. Please resend OTP."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Some error occurred. Please resend OTP."
     *             )
     *         )
     *     )
     * )
     */
    public function verifyforgotPasswordOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|min:10|max:10|exists:users,mobile_no',
            'mobile_otp' => 'required|string',
            'email_otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }
        return $this->userInterface->verifyforgotPasswordOtp($request);
    }

    /**
     * @OA\Post(
     *     path="/update-password",
     *     summary="Update user password",
     *     description="Update the user's password using a temporary token.",
     *     operationId="updatePassword",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Pass temporary token and new password details",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="temp_token",
     *                     type="string",
     *                     description="Temporary token received for password reset",
     *                     example="abcd1234"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     description="New password",
     *                     example="newpassword"
     *                 ),
     *                 @OA\Property(
     *                     property="confirm_password",
     *                     type="string",
     *                     description="Confirmation of the new password",
     *                     example="confirm new password"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Your password successfully changed. Please login using new password."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or invalid temporary token",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Validation error message or invalid temporary token."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Some error occurred."
     *             )
     *         )
     *     )
     * )
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'temp_token' => 'required|exists:users,temp_token',
            'password' => 'required|string|min:8|same:confirm_password',
            'confirm_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }
        return $this->userInterface->updatePassword($request);
    }

    /**
     * @OA\Post(
     *     path="/login-otp-send",
     *     summary="Send & Resend OTP for Login. Use Both (Send OTP & Resend OTP)",
     *     description="Sends & Resend OTP to the registered mobile number for login authentication.",
     *     operationId="loginOTPSend",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Provide mobile number for sending OTP.",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="mobile",
     *                     type="string",
     *                     description="User's mobile number",
     *                     example="1234567890"
     *                 ),
     *                 required={"mobile"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="mobile_otp",
     *                 type="string",
     *                 example="123456"
     *             ),
     *             @OA\Property(
     *                 property="expire_at",
     *                 type="string",
     *                 example="01 Jul 2024 10:00:00 AM"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Validation error message"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Error message"
     *             )
     *         )
     *     )
     * )
     */
    public function loginOTPSend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|min:10|max:10|exists:users,mobile_no',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }

        return $this->userInterface->loginOTPSend($request);
    }

    /**
     * @OA\Post(
     *     path="/verify-login-otp",
     *     summary="Verify Login OTP",
     *     description="Verifies the OTP sent to the user's mobile number for login.",
     *     operationId="verifyLoginOTP",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Provide mobile number and OTP for verification.",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="mobile",
     *                     type="string",
     *                     description="User's mobile number",
     *                     example="1234567890"
     *                 ),
     *                 @OA\Property(
     *                     property="mobile_otp",
     *                     type="string",
     *                     description="OTP received on mobile",
     *                     example="123456"
     *                 ),
     *                 required={"mobile", "mobile_otp"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="token_type",
     *                 type="string",
     *                 example="bearer"
     *             ),
     *             @OA\Property(
     *                 property="expires_in",
     *                 type="integer",
     *                 example=3600
     *             ),
     *             @OA\Property(
     *                 property="access_token",
     *                 type="string",
     *                 example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
     *             ),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 description="Authenticated user details",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="John Doe"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     example="john.doe@example.com"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Validation error message"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Unauthorized"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Error message"
     *             )
     *         )
     *     )
     * )
     */
    public function verifyLoginOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|digits:10|exists:users,mobile_no',
            'mobile_otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }

        return $this->userInterface->verifyLoginOTP($request);
    }


    /**
     * @OA\Post(
     *     path="/login-using-email",
     *     summary="Login using email",
     *     description="Logs in a user using their email and password and returns a JWT token.",
     *     operationId="loginUsingEmail",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Pass user credentials",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     description="User's email",
     *                     example="user@example.com"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     description="User's password",
     *                     example="password123"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="token_type",
     *                 type="string",
     *                 example="bearer"
     *             ),
     *             @OA\Property(
     *                 property="expires_in",
     *                 type="integer",
     *                 example=3600
     *             ),
     *             @OA\Property(
     *                 property="access_token",
     *                 type="string",
     *                 example="your_jwt_token"
     *             ),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="John Doe"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     example="user@example.com"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Please check your password."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Validation error message."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Error: Internal Server Error."
     *             )
     *         )
     *     )
     * )
     */
    public function loginUsingEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }

        return $this->userInterface->loginUsingEmail($request);
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     summary="Logout user",
     *     description="Logs out the authenticated user and invalidates the JWT token.",
     *     operationId="logout",
     *     tags={"Authentication"},
     *     security={{"Bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Successfully logged out"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Unauthorized"
     *             )
     *         )
     *     )
     * )
     */
    public function logout()
    {
       return $this->userInterface->logout();
    }

    /**
     * @OA\Get(
     *     path="/get-authenticate-user",
     *     summary="Get Authenticated User",
     *     description="Fetches the authenticated user's details.",
     *     operationId="getAuthenticateUser",
     *     tags={"Authentication"},
     *     security={{"Bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User details fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User Fetched"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="John Doe"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     example="john.doe@example.com"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Unauthorized"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Error message"
     *             )
     *         )
     *     )
     * )
     */
    public function getAuthenticateUser()
    {
        return $this->successResponse([auth()->user()], "User Fatched");
    }

    // Login Activity
    private function authenticatedUser(Request $request, $user)
    {
        // $agent = new Agent();
        $client = new Client();
        $response = $client->get('https://api.ipgeolocation.io/ipgeo', [
            'query' => [
                'apiKey' => '944ab8c4b6394e67aa7a5ac22a23780c',
                'ip' => $request->ip(),
            ],
        ]);

        $geoData = json_decode($response->getBody(), true);
        dd($geoData);

        UserLoginActivity::create([
            'user_id' => $user->id,
            'last_login_ip' => $request->ip(),
            // 'last_login_device' => $agent->device(),
            // 'last_login_browser' => $agent->browser(),
            // 'last_login_os' => $agent->platform(),
            'last_login_country' => $geoData['country_name'] ?? '',
            'last_login_region' => $geoData['state_prov'] ?? '',
            'last_login_city' => $geoData['city'] ?? '',
            'last_login_timezone' => $geoData['time_zone']['name'] ?? '',
            'last_login_latitude' => $geoData['latitude'] ?? '',
            'last_login_longitude' => $geoData['longitude'] ?? '',
            'last_login_at' => now(),
        ]);
    }
}
