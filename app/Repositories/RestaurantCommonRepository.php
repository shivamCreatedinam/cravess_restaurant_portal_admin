<?php

namespace App\Repositories;

use App\Interfaces\RestaurantCommonInterface;
use App\Models\StoreDetails;
use App\Models\StoreMedia;
use App\Models\User;
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

class RestaurantCommonRepository implements RestaurantCommonInterface
{
    use ApiResponseTrait;
    use ImageUploadTrait;

    public function updateRestoDetails($request)
    {
        try {
            $user = Auth::user();
            $getStoreData = StoreDetails::where(['user_id' => $user->uuid])->first();
            if ($getStoreData) {
                $unique_id = $getStoreData->unique_id;
            } else {
                $unique_id = generateRestaurantId($request->store_name, $request->store_mobile_no);
            }
            StoreDetails::updateOrCreate(
                [
                    "user_id" => $user->uuid,
                ],
                [
                    "unique_id" => $unique_id,
                    'store_name' => $request->store_name,
                    'store_type' => $request->store_type,
                    'store_mobile_no' => $request->store_mobile_no,
                    'store_phone_no' => $request->store_phone_no,
                    'store_address' => $request->store_address,
                    'store_city' => $request->store_city,
                    'store_state' => $request->store_state,
                    'store_pincode' => $request->store_pincode,
                    'store_desc' => $request->store_desc,
                ]
            );
            return $this->successResponse([], "Restaurant details successfully update.");
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    public function updateRestoImages($request)
    {
        try {
            $user = Auth::user();
            $getRestoMedia = StoreMedia::where("user_id", $user->uuid)->first();
            $store_image = null;
            $banner_image = null;

            if ($request->hasFile("logo")) {
                $path = "resto_logo/" . $user->uuid;
                if ($getRestoMedia && !is_null($getRestoMedia->store_image)) {
                    $this->deleteImage($getRestoMedia->store_image);
                }
                $store_image = $this->uploadImage($request->file('logo'), $path);
            }

            if ($request->hasFile("banner_image")) {
                $path = "resto_banner_image/" . $user->uuid;
                if ($getRestoMedia && !is_null($getRestoMedia->banner_image)) {
                    $this->deleteImage($getRestoMedia->banner_image);
                }
                $banner_image = $this->uploadImage($request->file('banner_image'), $path);
            }

            StoreMedia::updateOrCreate(
                [
                    'user_id' => $user->uuid
                ],
                [
                    'store_image' => $store_image,
                    'banner_image' => $banner_image,
                ]
            );

            return response()->json(['status' => true, 'message' => 'Images updated successfully']);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    // public function aadharPanCardUpdate($request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $user = Auth::user();

    //         $aadhar = null;
    //         if ($request->has("aadhar_number") && !is_null($request->aadhar_number)) {
    //             $aadhar = "Aadhar";
    //             UserAadharVerification::updateOrCreate(
    //                 [
    //                     'user_id' => $user->uuid,
    //                 ],
    //                 [
    //                     'aadhar_no' => $request->aadhar_number,
    //                     'verify_status' => 1,
    //                     'verified_at' => now()
    //                 ]
    //             );
    //             $user->update([
    //                 "aadhar_verified" => 1
    //             ]);
    //         }
    //         $pan = null;
    //         if ($request->has("pan_number") && !is_null($request->pan_number)) {
    //             $pan = "PAN";

    //             UserPanCardVerification::updateOrCreate(
    //                 [
    //                     'user_id' => $user->uuid,
    //                 ],
    //                 [
    //                     "pan_no" => strtolower($request->pan_number),
    //                     "pan_verify_status" => 1,
    //                     "pan_verified_at" => now()
    //                 ]
    //             );
    //             $user->update([
    //                 "pan_verified" => 1
    //             ]);
    //         }
    //         DB::commit();
    //         return $this->successResponse([], "{$aadhar} {$pan} Details Successfully Updated.");
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return $this->errorResponse("Error: " . $e->getMessage());
    //     }
    // }

    // public function gstUpdate($request)
    // {
    //     try {
    //         $user = Auth::user();

    //         $getGSTData = StoreVerification::where("user_id", $user->uuid)->first();

    //         $path = "gst_image/" . $user->uuid;
    //         if ($request->hasFile("gst_cert_image")) {
    //             if (!is_null($getGSTData->gst_image)) {
    //                 $this->deleteImage($getGSTData->gst_image);
    //             }
    //             $gst_image = $this->uploadImage($request->file('gst_cert_image'), $path);
    //         }
    //         $getGSTData->updateOrCreate(
    //             [
    //                 'user_id' => $user->uuid,
    //                 "gst_image" => $gst_image,
    //                 "gst_no" => strtolower($request->gst_number),
    //                 "gst_verification" => "pending"
    //             ]
    //         );
    //         return $this->successResponse([], "GST details successfully update.");
    //     } catch (Exception $e) {
    //         return $this->errorResponse($e->getMessage());
    //     }
    // }

    // public function fssaiDetailsUpdate($request)
    // {
    //     try {
    //         $user = Auth::user();
    //         $getFssaiData = StoreVerification::where("user_id", $user->uuid)->first();
    //         $path = "fssai_image/" . $user->uuid;
    //         if ($request->hasFile("fssai_image")) {
    //             if (!is_null($getFssaiData->fssai_image)) {
    //                 $this->deleteImage($getFssaiData->fssai_image);
    //             }
    //             $fssai_image = $this->uploadImage($request->file('fssai_image'), $path);
    //         }
    //         $getFssaiData->updateOrCreate(
    //             [
    //                 'user_id' => $user->uuid,
    //                 "fssai_image" => $fssai_image,
    //                 "fssai_verification" => "pending"
    //             ]
    //         );
    //         return $this->successResponse([], "FSSAI details successfully update.");
    //     } catch (Exception $e) {
    //         return $this->errorResponse($e->getMessage());
    //     }
    // }
}
