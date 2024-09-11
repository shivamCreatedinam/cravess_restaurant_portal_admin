<?php

namespace App\Interfaces;

interface RestaurantCommonInterface extends BaseInterface
{
    public function getAllRestaurant($per_page_item, $store_type);
}
