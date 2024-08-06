<?php

namespace App\Repositories;

use App\Events\RegistrationOTPSendEvent;
use App\Interfaces\UserInterface;
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

class EloquentUserRepository extends EloquentBaseRepository implements UserInterface
{
    use ApiResponseTrait;
    use ImageUploadTrait;

    public function register($request)
    {
        DB::beginTransaction();
        try {
            $user = $this->model::create([
                "name" => $request->name,
                "email" => $request->email,
                "mobile_no" => $request->mobile,
                "role" => "user",
                "password" => Hash::make($request->password),
            ]);
            $email_otp = generateOTP();
            $mobile_otp = generateOTP();
            $expiresAt = now()->addMinutes(5);
            $verificationCode = VerificationCodes::updateOrCreate(["user_id" => $user->uuid], [
                "mobile_otp" => $mobile_otp,
                "email_otp" => $email_otp,
                "expire_at" => $expiresAt
            ]);

            event(new RegistrationOTPSendEvent($user));

            $data = [
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
            $user = $this->model::where('mobile_no', $request->mobile)->first();
            $verificationCode = VerificationCodes::where('user_id', $user->uuid)->first();

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

                $user->update([
                    'email_verified_at' => now(),
                    'mobile_verified_at' => now(),
                ]);
                $verificationCode->delete();

                // Generate JWT token for the user
                $token = JWTAuth::fromUser($user);
                $authenticatedUser = JWTAuth::setToken($token)->toUser();
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
            $user = $this->model::where(["mobile_no" => $request->mobile])->first();
            $email_otp = generateOTP();
            $mobile_otp = generateOTP();
            $expiresAt = now()->addMinutes(5);
            $verificationCode = VerificationCodes::updateOrCreate(["user_id" => $user->uuid], [
                "mobile_otp" => $mobile_otp,
                "email_otp" => $email_otp,
                "expire_at" => $expiresAt
            ]);

            event(new RegistrationOTPSendEvent($user));

            $data = [
                "mobile_otp" => $verificationCode->mobile_otp,
                "email_otp" => $verificationCode->email_otp,
                "expire_at" => $expiresAt->format('d M Y h:i:s A'),
            ];
            $emailMask = Str::mask($user->email, '*', 2, 5);
            $mobileMask = Str::mask($user->mobile_no, '*', 2, 5);
            $message = "We have sent OTP your registered mobile number({$mobileMask}) & email({$emailMask}). OTPs expire within 5 min.";
            DB::commit();
            return $this->successResponse($data, $message);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function forgotPassword($request)
    {
        DB::beginTransaction();
        try {
            $user = $this->model::where(["mobile_no" => $request->mobile])->first();
            $email_otp = generateOTP();
            $mobile_otp = generateOTP();
            $expiresAt = now()->addMinutes(5);
            $verificationCode = VerificationCodes::updateOrCreate(["user_id" => $user->uuid], [
                "mobile_otp" => $mobile_otp,
                "email_otp" => $email_otp,
                "expire_at" => $expiresAt
            ]);

            event(new RegistrationOTPSendEvent($user));

            $data = [
                "mobile_otp" => $verificationCode->mobile_otp,
                "email_otp" => $verificationCode->email_otp,
                "expire_at" => $expiresAt->format('d M Y h:i:s A'),
            ];
            $emailMask = Str::mask($user->email, '*', 2, 5);
            $mobileMask = Str::mask($user->mobile_no, '*', 2, 5);
            $message = "We have sent OTP your registered mobile number({$mobileMask}) & email({$emailMask}). OTPs expire within 5 min.";
            DB::commit();
            return $this->successResponse($data, $message);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function verifyforgotPasswordOtp($request)
    {

        DB::beginTransaction();

        try {
            $user = $this->model::where('mobile_no', $request->mobile)->first();
            $verificationCode = VerificationCodes::where('user_id', $user->uuid)->first();

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

                $temp_token = mt_rand(1111, 9999) . Hash::make($user->email);
                $user->update([
                    'temp_token' => $temp_token,
                ]);
                $verificationCode->delete();

                $data = [
                    "temp_token" => $temp_token,
                ];
                DB::commit();
                return $this->successResponse($data, "OTP verified successfully.");
            } else {
                DB::rollBack();
                return $this->errorResponse("Some error occurred. Please resend OTP.");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse("Error: " . $e->getMessage());
        }
    }

    public function updatePassword($request)
    {

        DB::beginTransaction();

        try {
            $user = $this->model::where('temp_token', $request->temp_token)->first();

            if ($user) {

                $user->update([
                    "password" => Hash::make($request->password),
                    "temp_token" => null,
                ]);
                DB::commit();
                return $this->successResponse([], "Your password successfully changed. Please login using new password.");
            } else {
                DB::rollBack();
                return $this->errorResponse("Some error occurred.");
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse("Error: " . $e->getMessage());
        }
    }

    public function loginOTPSend($request)
    {
        DB::beginTransaction();
        try {
            $user = $this->model::where(["mobile_no" => $request->mobile])->first();
            if ($user->email_verified_at == null || $user->mobile_verified_at == null) {
                return $this->errorResponse("Please verify your mobile number and email address.");
            }
            $mobile_otp = generateOTP();
            $expiresAt = now()->addMinutes(5);
            $verificationCode = VerificationCodes::updateOrCreate(["user_id" => $user->uuid], [
                "mobile_otp" => $mobile_otp,
                "expire_at" => $expiresAt
            ]);

            // event(new RegistrationOTPSendEvent($user));

            $data = [
                "mobile_otp" => $verificationCode->mobile_otp,
                "expire_at" => $expiresAt->format('d M Y h:i:s A'),
            ];
            $mobileMask = Str::mask($user->mobile_no, '*', 2, 5);
            $message = "We have sent OTP your registered mobile number({$mobileMask}). OTPs expire within 5 min.";
            DB::commit();
            return $this->successResponse($data, $message);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    public function verifyLoginOTP($request)
    {
        DB::beginTransaction();

        try {
            $user = $this->model::where('mobile_no', $request->mobile)->first();
            $verificationCode = VerificationCodes::where('user_id', $user->uuid)->first();

            if ($verificationCode) {
                if (now()->greaterThan($verificationCode->expire_at)) {
                    return $this->errorResponse("OTP expired. Please resend OTP.");
                }
                if ($request->mobile_otp !== $verificationCode->mobile_otp) {
                    return $this->errorResponse("Mobile OTP invalid. Please resend OTP.");
                }

                $verificationCode->delete();

                // Generate JWT token for the user
                $token = JWTAuth::fromUser($user);
                $authenticatedUser = JWTAuth::setToken($token)->toUser();
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

    public function loginUsingEmail($request)
    {
        try {
            $credentials = $request->only(["email", "password"]);
            if (Auth::attempt($credentials)) {
                $user = Auth::user();

                if ($user->email_verified_at == null || $user->mobile_verified_at == null) {
                    Auth::logout();
                    return $this->errorResponse("Please verify your mobile number and email address.");
                }
                // Generate JWT token for the user
                $token = JWTAuth::fromUser($user);
                $authenticatedUser = JWTAuth::setToken($token)->toUser();
                // $this->authenticatedUser($request, $user);
                $data = [
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60,
                    'access_token' => $token,
                    'user' => $authenticatedUser
                ];

                return $this->successResponse($data, "User Logged-in successfully.");
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


    public function updateProfile($request)
    {
        try {
            $user = Auth::user();
            $name = $user->name;
            if ($request->has("name") && !is_null($request->has("name"))) {
                $name = $request->name;
            }

            $profile_image = $user->profile_image;
            $path = "profile_image/" . $user->uuid;
            if ($request->hasFile("profile_image")) {
                if (!is_null($user->profile_image)) {
                    $this->deleteImage($user->profile_image);
                }
                $profile_image = $this->uploadImage($request->file('profile_image'), $path);
            }
            $user->update([
                "name" => $name,
                "profile_image" => $profile_image
            ]);
            return $this->successResponse([], "User Profile successfully update.");
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function sendMobileOTP($request){

    }
}
