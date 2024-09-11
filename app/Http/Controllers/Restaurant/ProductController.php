<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Interfaces\ProductInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use ApiResponseTrait;
    public function __construct(private ProductInterface $productInterface) {}


    /**
     * @OA\Post(
     *     path="/get-items",
     *     tags={"Products"},
     *     summary="Get paginated products with optional filters",
     *     description="Fetch products with optional filtering by featured status, pagination, and categories.",
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="is_featured",
     *                     type="boolean",
     *                     description="Whether to return featured products only (true) or all products (false)"
     *                 ),
     *                 @OA\Property(
     *                     property="per_page_item",
     *                     type="integer",
     *                     description="Number of products per page for pagination"
     *                 ),
     *                 @OA\Property(
     *                     property="category_id",
     *                     type="integer",
     *                     description="Filter products by category ID"
     *                 ),
     *                 @OA\Property(
     *                     property="sub_category_id",
     *                     type="integer",
     *                     description="Filter products by sub-category ID"
     *                 ),
     *                 @OA\Property(
     *                     property="child_category_id",
     *                     type="integer",
     *                     description="Filter products by child category ID"
     *                 ),
     *                  @OA\Property(
     *                     property="restaurant_id",
     *                     type="string",
     *                     example="dfb8464f-e2f6-4ae0-9f40-b525b688ecaf",
     *                     description="Filter products by restaurant"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *   )
     * )
     */
    public function allProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "is_featured" => "nullable",
            "category_id" => "nullable|numeric",
            "sub_category_id" => "nullable|numeric",
            "child_category_id" => "nullable|numeric",
            "per_page_item" => "nullable|numeric",
            "restaurant_id" => "nullable",
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->first());
        }

        $category_id = null;
        $sub_category_id = null;
        $child_category_id = null;
        $featured = null;
        $restaurant_id = null;
        $per_page_item = 50;

        if ($request->has("is_featured") && !empty($request->is_featured)) {
            $featured = $request->is_featured;
        }

        if ($request->has("category_id") && !empty($request->category_id) && $request->category_id != 0) {
            $category_id = $request->category_id;
        }

        if ($request->has("sub_category_id") && !empty($request->sub_category_id) && $request->sub_category_id != 0) {
            $sub_category_id = $request->sub_category_id;
        }

        if ($request->has("child_category_id") && !empty($request->child_category_id) && $request->child_category_id != 0) {
            $child_category_id = $request->child_category_id;
        }

        if ($request->has("per_page_item") && !empty($request->per_page_item) && $request->per_page_item != 0) {
            $per_page_item = $request->per_page_item;
        }

        if ($request->has("restaurant_id") && !empty($request->restaurant_id)) {
            $restaurant_id = $request->restaurant_id;
        }

        return $this->productInterface->getAllproducts($featured, $category_id, $sub_category_id, $child_category_id, $restaurant_id, $per_page_item);
    }
}
