<?php
namespace App\Interfaces;

interface CommonServiceInterface extends BaseInterface
{
    public function sendMobileOTP($request);
    public function sendEmailOTP($request);
    public function verifyEmailOTPAndSendNewMailOTP($request);
    public function verifyNewMailOTP($request);
    public function verifyMobileOTPAndSendNewMobileOTP($request);
    public function verifyNewMobileOTP($request);
}
