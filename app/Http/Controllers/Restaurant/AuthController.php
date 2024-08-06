<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Interfaces\RestaurantAuthInterface;
use App\Rules\EmailOrMobile;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponseTrait;
    public function __construct(private RestaurantAuthInterface $restaurantAuthInterface)
    {
    }


    /**
     * @OA\Post(
     *     path="/store/register",
     *     tags={"Restaurant - Registration and Login Module"},
     *     summary="Register a new restaurant",
     *     description="Register a new restaurant with name, email, mobile, and password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     description="Name of the restaurant owner",
     *                     example="John Doe"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     description="Email address of the user",
     *                     example="john.doe@example.com"
     *                 ),
     *                 @OA\Property(
     *                     property="mobile",
     *                     type="string",
     *                     description="Mobile number of the user",
     *                     example="9876543210"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     description="Password of the user",
     *                     example="password123"
     *                 ),
     *                 @OA\Property(
     *                     property="confirm_password",
     *                     type="string",
     *                     description="Confirm password",
     *                     example="password123"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful registration"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
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

        return $this->restaurantAuthInterface->register($request);
    }

    /**
     * @OA\Post(
     *     path="/store/verify-registration-otp",
     *     summary="Verify Registration OTP",
     *     description="Verify the registration OTPs for email and mobile, then log in the restaurant using JWT.",
     *     tags={"Restaurant - Registration and Login Module"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="temp_token",
     *                     type="string",
     *                     description="Temp Token",
     *                     example="DFGRTFD4F5DGD"
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
     *                 required={"temp_token", "mobile_otp", "email_otp"}
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
            'temp_token' => 'required|exists:verification_codes,token',
            'mobile_otp' => 'required|string',
            'email_otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }

        return $this->restaurantAuthInterface->verifyRegistrationOTP($request);
    }

       /**
     * @OA\Post(
     *     path="/store/resend-registration-otp",
     *     summary="Resend Registration OTP",
     *     description="Resend the registration OTPs for email and mobile.",
     *     tags={"Restaurant - Registration and Login Module"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="contact",
     *                     type="string",
     *                     description="Enter Registered mobile number or email address",
     *                     example="Email or Mobile"
     *                 ),
     *                 required={"contact"}
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
            'contact' => ['required', new EmailOrMobile],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }
        return $this->restaurantAuthInterface->resendRegistrationOTP($request);
    }


    /**
     * @OA\Post(
     *     path="/store/login",
     *     tags={"Restaurant - Registration and Login Module"},
     *     summary="Login Restaurant",
     *     description="Login a Restaurant using email and password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     description="Email address of the Restaurant",
     *                     example="john.doe@example.com"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     description="Password of the Restaurant",
     *                     example="password123"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email"=>"required|exists:users,email",
            "password"=>"required"
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }
        return $this->restaurantAuthInterface->loginUsingEmail($request);
    }
}
