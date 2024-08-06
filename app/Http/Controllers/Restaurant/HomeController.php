<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Interfaces\RestaurantCommonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{

    public function __construct(private RestaurantCommonInterface $restaurantCommonInterface)
    {
    }

    /**
     * @OA\Post(
     *     path="/store/resto-details-update",
     *     tags={"Restaurant - Images and Details Update"},
     *     summary="Update restaurant details",
     *     description="Update the details of the restaurant including store name, type, contact information, address, etc.",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="store_name",
     *                     type="string",
     *                     description="Store name",
     *                     example="Store Name"
     *                 ),
     *                 @OA\Property(
     *                     property="store_type",
     *                     type="string",
     *                     enum={"veg", "non_veg", "both"},
     *                     description="Store type",
     *                 ),
     *                 @OA\Property(
     *                     property="store_mobile_no",
     *                     type="string",
     *                     description="Store mobile number",
     *                 ),
     *                 @OA\Property(
     *                     property="store_phone_no",
     *                     type="string",
     *                     description="Store phone number",
     *                 ),
     * @OA\Property(
     *                     property="store_email",
     *                     type="string",
     *                     description="Store Email Address",
     *                 ),
     * @OA\Property(
     *                     property="website",
     *                     type="string",
     *                     description="Store Website",
     *                 ),
     *                 @OA\Property(
     *                     property="store_address",
     *                     type="string",
     *                     description="Store address",
     *                 ),
     *                 @OA\Property(
     *                     property="store_city",
     *                     type="string",
     *                     description="Store city",
     *                 ),
     *                 @OA\Property(
     *                     property="store_state",
     *                     type="string",
     *                     description="Store state",
     *                 ),
     *                 @OA\Property(
     *                     property="store_pincode",
     *                     type="string",
     *                     description="Store pincode",
     *                 ),
     *                 @OA\Property(
     *                     property="store_desc",
     *                     type="string",
     *                     description="Store description",
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
    public function restoDetailsUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_name' => 'required|string',
            'store_type' => 'required|in:veg,non_veg,both',
            'store_mobile_no' => 'required|min:10|max:10',
            'store_phone_no' => 'nullable',
            'store_email' => 'nullable',
            'website' => 'nullable',
            'store_address' => 'required',
            'store_city' => 'required',
            'store_state' => 'required',
            'store_pincode' => 'required|min:6|max:6',
            'store_desc' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }
        return $this->restaurantCommonInterface->updateRestoDetails($request);
    }

    /**
     * @OA\Post(
     *     path="/store/resto-images-upload",
     *     tags={"Restaurant - Images and Details Update"},
     *     summary="Upload restaurant logo and banner image",
     *     security={{"Bearer":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Image files to be uploaded",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="logo",
     *                     type="string",
     *                     format="binary",
     *                     description="Restaurant logo image file"
     *                 ),
     *                 @OA\Property(
     *                     property="banner_image",
     *                     type="string",
     *                     format="binary",
     *                     description="Restaurant banner image file"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Images uploaded successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function restoImagesUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "logo" => 'required|image|mimes:png,jpg,jpeg|max:2048',
            "banner_image" => 'required|image|mimes:png,jpg,jpeg|max:6000',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }
        return $this->restaurantCommonInterface->updateRestoImages($request);
    }
}
