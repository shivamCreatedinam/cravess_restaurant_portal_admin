<?php
namespace App\Interfaces;

interface RestaurantAuthInterface extends BaseInterface
{
    // API Auth Controller
    public function register($request);
    public function verifyRegistrationOTP($request);
    public function resendRegistrationOTP($request);
    public function loginUsingEmail($request);
    public function logout();
}
