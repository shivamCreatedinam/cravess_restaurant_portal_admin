<?php
namespace App\Interfaces;

interface RestaurantCommonInterface extends BaseInterface
{
    public function updateRestoDetails($request);
    public function updateRestoImages($request);
}
