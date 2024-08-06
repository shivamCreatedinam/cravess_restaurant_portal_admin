<?php

namespace App\Repositories;

use App\Interfaces\RestaurantKycInterface;
use App\Models\StoreVerification;
use App\Models\User;
use App\Models\UserAadharVerification;
use App\Models\UserPanCardVerification;
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

class RestaurantKycRepository implements RestaurantKycInterface
{
    use ApiResponseTrait;
    use ImageUploadTrait;

    public function aadharPanCardUpdate($request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $aadhar = null;
            if ($request->has("aadhar_number") && !is_null($request->aadhar_number)) {
                $aadhar = "Aadhar";
                UserAadharVerification::updateOrCreate(
                    [
                        'user_id' => $user->uuid,
                    ],
                    [
                        'aadhar_no' => $request->aadhar_number,
                        'verify_status' => 1,
                        'verified_at' => now()
                    ]
                );
                $user->update([
                    "aadhar_verified" => 1
                ]);
            }
            $pan = null;
            if ($request->has("pan_number") && !is_null($request->pan_number)) {
                $pan = "PAN";

                UserPanCardVerification::updateOrCreate(
                    [
                        'user_id' => $user->uuid,
                    ],
                    [
                        "pan_no" => strtolower($request->pan_number),
                        "pan_verify_status" => 1,
                        "pan_verified_at" => now()
                    ]
                );
                $user->update([
                    "pan_verified" => 1
                ]);
            }
            DB::commit();
            return $this->successResponse([], "{$aadhar} {$pan} Details Successfully Updated.");
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse("Error: " . $e->getMessage());
        }
    }

    public function gstUpdate($request)
    {
        try {
            $user = Auth::user();

            $getGSTData = StoreVerification::where("user_id", $user->uuid)->first();

            $path = "gst_image/" . $user->uuid;
            if ($request->hasFile("gst_cert_image")) {
                if ($getGSTData && !is_null($getGSTData->gst_image)) {
                    $this->deleteImage($getGSTData->gst_image);
                }
                $gst_image = $this->uploadImage($request->file('gst_cert_image'), $path);
            }
            StoreVerification::updateOrCreate(
                [
                    'user_id' => $user->uuid,
                ],
                [
                    "gst_image" => $gst_image,
                    "gst_no" => strtolower($request->gst_number),
                    "gst_verification" => "pending"
                ]
            );
            return $this->successResponse([], "GST details successfully update.");
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function fssaiDetailsUpdate($request)
    {
        try {
            $user = Auth::user();
            $getFssaiData = StoreVerification::where("user_id", $user->uuid)->first();
            $path = "fssai_image/" . $user->uuid;
            if ($request->hasFile("fssai_image")) {
                if ($getFssaiData && !is_null($getFssaiData->fssai_image)) {
                    $this->deleteImage($getFssaiData->fssai_image);
                }
                $fssai_image = $this->uploadImage($request->file('fssai_image'), $path);
            }
            StoreVerification::updateOrCreate(
                [
                    'user_id' => $user->uuid,
                ],
                [
                    "fssai_image" => $fssai_image,
                    "fssai_verification" => "pending"
                ]
            );
            return $this->successResponse([], "FSSAI details successfully update.");
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
