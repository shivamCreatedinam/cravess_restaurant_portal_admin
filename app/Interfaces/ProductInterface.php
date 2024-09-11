<?php

namespace App\Interfaces;

interface ProductInterface extends BaseInterface
{
    public function getAllproducts($featured, $category_id, $sub_category_id, $child_category_id, $restaurant_id, $per_page_item);

    // public function getCategoryWiseProduct($category_id, $sub_category_id, $child_category_id, $per_page_item);
}
