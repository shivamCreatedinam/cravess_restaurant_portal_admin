<?php
namespace App\Interfaces;

interface RestaurantKycInterface extends BaseInterface
{
    public function aadharPanCardUpdate($request);
    public function gstUpdate($request);
    public function fssaiDetailsUpdate($request);
}
