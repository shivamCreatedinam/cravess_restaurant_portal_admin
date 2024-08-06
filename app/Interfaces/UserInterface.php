<?php
namespace App\Interfaces;

interface UserInterface extends BaseInterface
{
    // API Auth Controller
    public function register($request);
    public function verifyRegistrationOTP($request);
    public function resendRegistrationOTP($request);
    public function forgotPassword($request);
    public function verifyforgotPasswordOtp($request);
    public function updatePassword($request);
    public function loginOTPSend($request);
    public function verifyLoginOTP($request);
    public function loginUsingEmail($request);
    public function logout();

    // UserController
    public function updateProfile($request);
}
