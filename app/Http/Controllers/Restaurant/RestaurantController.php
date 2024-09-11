<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Interfaces\RestaurantCommonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RestaurantController extends Controller
{
    public function __construct(private RestaurantCommonInterface $restaurantCommonInterface) {}

    /**
     * @OA\Post(
     *     path="/get-stores",
     *     summary="Get paginated list of stores",
     *     description="Retrieve a list of stores with pagination. The `per_page_item` parameter defines how many stores should be returned per page, and `store_type` filters the stores by type (all, veg, non_veg).",
     *     tags={"Stores"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="per_page_item",
     *                     description="Number of stores per page",
     *                     type="integer",
     *                     example=10,
     *                     nullable=true
     *                 ),
     *                 @OA\Property(
     *                     property="store_type",
     *                     description="Type of store (all, veg, non_veg)",
     *                     type="string",
     *                     enum={"all", "veg", "non_veg"},
     *                     example="all",
     *                     nullable=true
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *     )
     * )
     */

    public function getAllStores(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "per_page_item" => "nullable|numeric",
            "store_type" => "nullable|string|in:all,veg,non_veg",
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }
        $per_page_item = 50;
        if ($request->has("per_page_item") && !empty($request->per_page_item) && $request->per_page_item != 0) {
            $per_page_item = $request->per_page_item;
        }
        $store_type = $request->store_type;

        return $this->restaurantCommonInterface->getAllRestaurant($per_page_item, $store_type);
    }
}
