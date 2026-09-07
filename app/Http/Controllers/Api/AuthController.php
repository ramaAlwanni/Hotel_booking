<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\OTP\ResendOTPRequest;
use App\Http\Requests\Api\OTP\VerifyOTPRequest;
use App\Http\Resources\UserResource;
use App\Services\Api\AuthService;
use App\Services\Api\OTPService;
use App\Traits\ApiResponseTrait;

class AuthController extends Controller
{
    use ApiResponseTrait;
    protected AuthService $authService;
    protected OTPService $otpService;

    public function __construct(AuthService $authService, OTPService $otpService)
    {
        $this->authService = $authService;
        $this->otpService = $otpService;
    }
    // -------------------------------------------------------------------------------------------
    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());
        $this->otpService->sendOTP($user);
        return $this->success('User registered successfully', new UserResource($user), 201);
    }
    // -------------------------------------------------------------------------------------------
    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());
        
        return match ($result) {
            'unauthorized' => $this->error('Email or password not correct!', null, 422),
            'email_not_verified' => $this->error('Please verify your email address before logging in.', null, 422),
            default => $this->success('User login successfully', $result ,200)
        };
    }
    // -------------------------------------------------------------------------------------------
    public function logout()
    {
        auth()->user()->tokens()->delete();
        return $this->success('User logout successfully', null, 200);
    }
    //*************************************** */
    public function verifyOtp(VerifyOTPRequest $request)
    {
        $result = $this->otpService->verifyOtp($request->validated());

        if ($result === 'CorrectOTP') {
            return $this->success('OTP verified successfully', ['email' => $request->email],  200);
        }

        return match ($result) {
            'UserNotFound' => $this->error('User not found!', null, 422),
            'NotValidOTP' => $this->error('Not valid OTP!', null, 422),
            'OTPHasExpired' => $this->error('OTP has expired!', null, 422),
            'EmailVerified' => $this->error('Your email address has already been verified.', null, 422),
            default => $this->success('OTP verified successfully', null, 200)
        };
    }
    // -------------------------------------------------------------------------------------------
    public function resendOtp(ResendOTPRequest $request)
    {
        $result = $this->otpService->resendOtp($request->validated());

        return match ($result) {
            'UserNotFound' => $this->error('User not found!', null, 422),
            default => $this->success('OTP resent successfully. Please check your email.', null, 200)
        };
    }
}
