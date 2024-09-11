<?php

namespace App\Repositories;

use App\Interfaces\RestaurantCommonInterface;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;


class RestaurantCommonRepository implements RestaurantCommonInterface
{
    use ApiResponseTrait;

    public function getAllRestaurant($per_page_item, $store_type)
    {
        try {
            $data = User::with(['store' => function ($query) use ($store_type) {
                if ($store_type === 'veg') {
                    $query->where('store_type', 'veg');
                } elseif ($store_type === 'non_veg') {
                    $query->where('store_type', 'non_veg');
                }
                // No filter applied if $store_type is 'all' or null
            }])
                ->where(['user_status' => "active", 'role' => 'store'])
                ->select(['users.uuid']);

            // If the store_type is 'all' or null, just get all active users with role 'store'
            $data = $data->paginate($per_page_item);

            return $this->successResponse($data, "Items successfully fetched.");
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
