<?php

namespace App\Repositories;

use App\Events\RegistrationOTPSendEvent;
use App\Interfaces\RestaurantAuthInterface;
use App\Models\User;
use App\Models\VerificationCodes;
use App\Traits\ApiResponseTrait;
use App\Traits\ImageUploadTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class RestaurantAuthRepository implements RestaurantAuthInterface
{
    use ApiResponseTrait;
    use ImageUploadTrait;

    public function register($request)
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                "name" => ucfirst($request->name),
                "email" => $request->email,
                "mobile_no" => $request->mobile,
                "role" => "store",
                "password" => Hash::make($request->password),
                'resto_rider_status' => 'pending'
            ]);
            $email_otp = generateOTP();
            $mobile_otp = generateOTP();
            $expiresAt = now()->addMinutes(5);
            $temp_token =  bin2hex(openssl_random_pseudo_bytes(16));
            $verificationCode = VerificationCodes::updateOrCreate(["user_id" => $user->uuid], [
                "mobile_otp" => $mobile_otp,
                "email_otp" => $email_otp,
                "expire_at" => $expiresAt,
                "token" => $temp_token
            ]);

            event(new RegistrationOTPSendEvent($user));

            $data = [
                "temp_token" => $temp_token,
                "mobile_otp" => $verificationCode->mobile_otp,
                "email_otp" => $verificationCode->email_otp,
                "expire_at" => $expiresAt->format('d M Y h:i:s A'),
            ];
            DB::commit();
            return $this->successResponse($data, "We have sent OTP your mobile number & email. OTPs expire within 5 min.");
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function verifyRegistrationOTP($request)
    {
        DB::beginTransaction();

        try {
            $verificationCode = VerificationCodes::where('token', $request->temp_token)->first();

            if ($verificationCode) {
                if (now()->greaterThan($verificationCode->expire_at)) {
                    return $this->errorResponse("OTP expired. Please resend OTP.");
                }
                if ($request->mobile_otp !== $verificationCode->mobile_otp) {
                    return $this->errorResponse("Mobile OTP invalid. Please resend OTP.");
                }
                if ($request->email_otp !== $verificationCode->email_otp) {
                    return $this->errorResponse("Email OTP invalid. Please resend OTP.");
                }
                $user = User::find($verificationCode->user_id);

                $user->update([
                    'email_verified_at' => now(),
                    'mobile_verified_at' => now(),
                ]);
                $verificationCode->delete();

                // Generate JWT token for the user
                $token = JWTAuth::fromUser($user);
                $authenticatedUser = JWTAuth::setToken($token)->toUser();
                $authenticatedUser->load(['restoDetails', 'restoMedia','restoVerifications']);
                $data = [
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60,
                    'access_token' => $token,
                    'user' => $authenticatedUser
                ];
                DB::commit();
                return $this->successResponse($data, "OTP verified successfully. Verification completed.");
            } else {
                DB::rollBack();
                return $this->errorResponse("Some error occurred. Please resend OTP.");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse("Error: " . $e->getMessage());
        }
    }

    public function resendRegistrationOTP($request)
    {
        DB::beginTransaction();
        try {

            $contact = $request->input('contact');
            $user = null;

            if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
                $user = User::where('email', $contact)->first();
            } elseif (preg_match('/^\d{10}$/', $contact)) {
                $user = User::where('mobile_no', $contact)->first();
            }
            if ($user) {
                if (!is_null($user->email_verified_at) && !is_null($user->mobile_verified_at)) {
                    return $this->errorResponse("Your account already verified. Please login.");
                }
                $email_otp = generateOTP();
                $mobile_otp = generateOTP();
                $expiresAt = now()->addMinutes(5);
                $temp_token =  bin2hex(openssl_random_pseudo_bytes(16));
                $verificationCode = VerificationCodes::updateOrCreate(["user_id" => $user->uuid], [
                    "mobile_otp" => $mobile_otp,
                    "email_otp" => $email_otp,
                    "expire_at" => $expiresAt,
                    "token" => $temp_token
                ]);

                event(new RegistrationOTPSendEvent($user));

                $data = [
                    "temp_token" => $temp_token,
                    "mobile_otp" => $verificationCode->mobile_otp,
                    "email_otp" => $verificationCode->email_otp,
                    "expire_at" => $expiresAt->format('d M Y h:i:s A'),
                ];
                $emailMask = Str::mask($user->email, '*', 2, 5);
                $mobileMask = Str::mask($user->mobile_no, '*', 2, 5);
                $message = "We have sent OTP your registered mobile number({$mobileMask}) & email({$emailMask}). OTPs expire within 5 min.";
                DB::commit();
                return $this->successResponse($data, $message);
            } else {
                return $this->errorResponse("Your Account Not Found.");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function loginUsingEmail($request)
    {
        try {
            $credentials = $request->only(["email", "password"]);
            if (Auth::attempt($credentials)) {
                $user = Auth::user();

                if ($user->role != 'store') {
                    Auth::logout();
                    return $this->errorResponse("You are not be authorized in this portal.");
                }

                if ($user->email_verified_at == null || $user->mobile_verified_at == null) {
                    Auth::logout();
                    return $this->errorResponse("Please verify your mobile number and email address.");
                }

                if ($user->user_status != 'active') {
                    Auth::logout();
                    return $this->errorResponse("Your account is not active. Please contact administrator.");
                }

                // if ($user->resto_rider_status == 'pending') {
                //     Auth::logout();
                //     return $this->errorResponse("Your request is pending. Please wait for approval.");
                // }

                // Generate JWT token for the user
                $token = JWTAuth::fromUser($user);
                $authenticatedUser = JWTAuth::setToken($token)->toUser();
                $authenticatedUser->load(['restoDetails', 'restoMedia','restoVerifications']);
                // $this->authenticatedUser($request, $user);
                $data = [
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60,
                    'access_token' => $token,
                    'user' => $authenticatedUser
                ];

                return $this->successResponse($data, "Store Logged-in successfully.");
            } else {
                return $this->errorResponse("Please check your password.");
            }
        } catch (Exception $e) {
            return $this->errorResponse("Error: " . $e->getMessage());
        }
    }

    public function logout()
    {
        try {
            $token = JWTAuth::getToken();

            if (!$token) {
                return $this->errorResponse('Token not provided', 400);
            }

            JWTAuth::invalidate($token);

            return $this->successResponse(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return $this->errorResponse('Failed to logout', 500);
        }
    }
}
