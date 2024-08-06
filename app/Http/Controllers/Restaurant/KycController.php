<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Interfaces\RestaurantKycInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KycController extends Controller
{
    use ApiResponseTrait;
    public function __construct(private RestaurantKycInterface $restaurantKycInterface)
    {
    }

    /**
     * @OA\Post(
     *     path="/store/aadhar-pan-card-update",
     *     tags={"Restaurant - KYC Submit"},
     *     summary="Update Aadhar and PAN Card information",
     *     description="Update the Aadhar number and PAN card number for the user",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="aadhar_number",
     *                     type="string",
     *                     description="Aadhar number of the user",
     *                     example="123412341234"
     *                 ),
     *                 @OA\Property(
     *                     property="pan_number",
     *                     type="string",
     *                     description="PAN card number of the user",
     *                     example="ABCDE1234F"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful update"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function aadharPanCardUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'aadhar_number' => 'nullable|min:12|max:12',
            'pan_number' => ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }
        return $this->restaurantKycInterface->aadharPanCardUpdate($request);
    }

      /**
     * @OA\Post(
     *     path="/store/gst-update",
     *     tags={"Restaurant - KYC Submit"},
     *     summary="Update GST information",
     *     description="Update the GST number and GST certificate image for the user",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="gst_number",
     *                     type="string",
     *                     description="GST number of the user",
     *                     example="22ABCDE1234F1Z5"
     *                 ),
     *                 @OA\Property(
     *                     property="gst_cert_image",
     *                     type="string",
     *                     format="binary",
     *                     description="GST certificate image",
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful update"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function gstUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gst_number' => ['required', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'],
            'gst_cert_image' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }
        return $this->restaurantKycInterface->gstUpdate($request);
    }

 /**
     * @OA\Post(
     *     path="/store/fssai-details-update",
     *     tags={"Restaurant - KYC Submit"},
     *     summary="Update FSSAI details",
     *     description="Update the FSSAI certificate image for the user",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="fssai_image",
     *                     type="string",
     *                     format="binary",
     *                     description="FSSAI certificate image",
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful update"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function fssaiDetailsUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fssai_image' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }
        return $this->restaurantKycInterface->fssaiDetailsUpdate($request);
    }
}
