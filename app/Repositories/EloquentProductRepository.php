<?php

namespace App\Repositories;

use App\Interfaces\ProductInterface;
use App\Models\Product;
use App\Traits\ApiResponseTrait;
use App\Traits\ImageUploadTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class EloquentProductRepository extends EloquentBaseRepository implements ProductInterface
{
    use ApiResponseTrait;
    use ImageUploadTrait;

    public function getAllproducts($featured, $category_id, $sub_category_id, $child_category_id, $restaurant_id, $per_page_item)
    {
        try {
            // Initialize the query
            $data = Product::where('status', 1);

            // Check for featured and handle the case for true/false
            if (!is_null($featured)) {
                $featured = filter_var($featured, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($featured !== null) {
                    $data->where('is_featured', $featured ? 1 : 0);
                }
            }

            // Apply filters for categories
            if (!empty($category_id) && empty($sub_category_id) && empty($child_category_id)) {
                $data->where('category_id', $category_id);
            }

            if (!empty($sub_category_id) && empty($category_id) && empty($child_category_id)) {
                $data->where('sub_category_id', $sub_category_id);
            }

            if (!empty($child_category_id) && empty($sub_category_id) && empty($category_id)) {
                $data->where('child_category_id', $child_category_id);
            }

            if (!empty($category_id) && !empty($sub_category_id)) {
                $data->where([
                    'category_id' => $category_id,
                    'sub_category_id' => $sub_category_id
                ]);
            }

            if (!empty($category_id) && !empty($sub_category_id) && !empty($child_category_id)) {
                $data->where([
                    'category_id' => $category_id,
                    'sub_category_id' => $sub_category_id,
                    'child_category_id' => $child_category_id
                ]);
            }

            if (!empty($restaurant_id)) {
                $data->where('restaurant_id', $restaurant_id);
            }

            // Paginate the results
            $data = $data->paginate($per_page_item);

            return $this->successResponse($data, "Items successfully fetched.");
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    // public function getCategoryWiseProduct($category_id, $sub_category_id, $child_category_id, $per_page_item)
    // {
    //     try {

    //         $data = Product::where('status', 1);
    //         if (!empty($category_id) && empty($sub_category_id) && empty($child_category_id)) {
    //             $data->where('category_id', $category_id);
    //         }

    //         if (!empty($sub_category_id) && empty($category_id) && empty($child_category_id)) {
    //             $data->where('sub_category_id', $sub_category_id);
    //         }

    //         if (!empty($child_category_id) && empty($sub_category_id) && empty($category_id)) {
    //             $data->where('child_category_id', $child_category_id);
    //         }

    //         if (!empty($category_id) && !empty($sub_category_id)) {
    //             $data->where(['category_id' => $category_id, 'sub_category_id' => $sub_category_id]);
    //         }

    //         if (!empty($category_id) && !empty($sub_category_id) && !empty($child_category_id)) {
    //             $data->where(['category_id' => $category_id, 'sub_category_id' => $sub_category_id, 'child_category_id' => $child_category_id]);
    //         }



    //         $data = $data->paginate($per_page_item);
    //         return $this->successResponse($data, "Items successfully fetched.");
    //     } catch (Exception $e) {
    //         return $this->errorResponse($e->getMessage());
    //     }
    // }
}
