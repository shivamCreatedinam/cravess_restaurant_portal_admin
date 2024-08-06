<?php

namespace App\Repositories;

use App\Events\EmailOTPEvent;
use App\Events\RegistrationOTPSendEvent;
use App\Interfaces\CommonServiceInterface;
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

class CommonServiceRepository implements CommonServiceInterface
{
    use ApiResponseTrait;
    use ImageUploadTrait;

    public function sendMobileOTP($request)
    {
        try {
            $user = Auth::user();
            $mobile_otp = generateOTP();
            $expiresAt = now()->addMinutes(5);
            $temp_token =  bin2hex(openssl_random_pseudo_bytes(16));
            $verificationCode = VerificationCodes::updateOrCreate(["user_id" => $user->uuid], [
                "mobile_otp" => $mobile_otp,
                "email_otp" => null,
                "expire_at" => $expiresAt,
                "token" => $temp_token
            ]);

            // event(new sendMobileOTP($user));
            $mobile_no = $user->mobile_no;
            if ($request->has('new_mobile') && !is_null($request->new_mobile)) {
                $mobile_no = $request->new_mobile;
            }

            $data = [
                "temp_token" => $temp_token,
                "mobile_otp" => $verificationCode->mobile_otp,
                "mobile_no" => $mobile_no,
                "expire_at" => $expiresAt->format('d M Y h:i:s A'),
            ];
            $mobileMask = Str::mask($user->mobile_no, '*', 2, 5);
            $message = "We have sent OTP your registered mobile number({$mobileMask}). OTPs expire within 5 min.";
            return $this->successResponse($data, $message);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function sendEmailOTP($request)
    {
        try {
            $user = Auth::user();
            $email_otp = generateOTP();
            $expiresAt = now()->addMinutes(5);
            $temp_token =  bin2hex(openssl_random_pseudo_bytes(16));
            $verificationCode = VerificationCodes::updateOrCreate(["user_id" => $user->uuid], [
                "email_otp" => $email_otp,
                "mobile_otp" => null,
                "expire_at" => $expiresAt,
                "token" => $temp_token
            ]);
            $email = $user->email;
            if ($request->has('new_email') && !is_null($request->new_email)) {
                $email = $request->new_email;
            }


            event(new EmailOTPEvent($user, $email));

            $data = [
                "temp_token" => $temp_token,
                "email" => $email,
                "email_otp" => $verificationCode->email_otp,
                "expire_at" => $expiresAt->format('d M Y h:i:s A'),
            ];
            $emailMask = Str::mask($email, '*', 2, 5);
            $message = "We have sent OTP your registered email({$emailMask}). OTPs expire within 5 min.";
            return $this->successResponse($data, $message);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function verifyEmailOTPAndSendNewMailOTP($request)
    {
        try {
            $getOTP = VerificationCodes::where("token", $request->temp_token)->first();
            if ($getOTP) {
                if (now()->greaterThan($getOTP->expire_at)) {
                    return $this->errorResponse("OTP expired. Please resend OTP.");
                }
                if ($request->otp !== $getOTP->email_otp) {
                    return $this->errorResponse("Email OTP invalid. Please resend OTP.");
                }
                $getOTP->delete();
                return $this->sendEmailOTP($request);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function verifyNewMailOTP($request){
        try {
            $getOTP = VerificationCodes::where("token", $request->temp_token)->first();
            if ($getOTP) {
                if (now()->greaterThan($getOTP->expire_at)) {
                    return $this->errorResponse("OTP expired. Please resend OTP.");
                }
                if ($request->otp !== $getOTP->email_otp) {
                    return $this->errorResponse("Email OTP invalid. Please resend OTP.");
                }

                $user = auth()->user();
                if ($user) {
                    $user->update([
                        "email" => $request->email
                    ]);
                    $getOTP->delete();
                    return $this->successResponse($user, "Email successfully updated. Please use new email for login.");
                } else {
                    return $this->errorResponse("Something went wrong.");
                }
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function verifyMobileOTPAndSendNewMobileOTP($request){
        try {
            $getOTP = VerificationCodes::where("token", $request->temp_token)->first();
            if ($getOTP) {
                if (now()->greaterThan($getOTP->expire_at)) {
                    return $this->errorResponse("OTP expired. Please resend OTP.");
                }
                if ($request->otp !== $getOTP->mobile_otp) {
                    return $this->errorResponse("Mobile OTP invalid. Please resend OTP.");
                }
                $getOTP->delete();
                return $this->sendMobileOTP($request);
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function verifyNewMobileOTP($request){
        try {
            $getOTP = VerificationCodes::where("token", $request->temp_token)->first();
            if ($getOTP) {
                if (now()->greaterThan($getOTP->expire_at)) {
                    return $this->errorResponse("OTP expired. Please resend OTP.");
                }
                if ($request->otp !== $getOTP->mobile_otp) {
                    return $this->errorResponse("Mobile OTP invalid. Please resend OTP.");
                }

                $user = auth()->user();
                if ($user) {
                    $user->update([
                        "mobile_no" => $request->mobile
                    ]);
                    $getOTP->delete();
                    return $this->successResponse($user, "Mobile number successfully updated.");
                } else {
                    return $this->errorResponse("Something went wrong.");
                }
            }
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
