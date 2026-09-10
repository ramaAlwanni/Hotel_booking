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
use Illuminate\Auth\AuthenticationException;

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
        return $this->success('User login successfully', $result, 200);
    }
    // -------------------------------------------------------------------------------------------
    public function logout()
    {
        $user = auth()->user();
        if(!$user){
            throw new AuthenticationException();
        }
        $user->tokens()->delete();
        return $this->success('User logout successfully', null, 200);
    }
    //*************************************** */
    public function verifyOtp(VerifyOTPRequest $request)
    {
        $result = $this->otpService->verifyOtp($request->validated());
        if($result){
            return $this->success('OTP verified successfully', ['email' => $result->email], 200);
        }
    }
    // ---------------------------------------------------------
    public function resendOtp(ResendOTPRequest $request)
    {
        $this->otpService->resendOtp($request->validated());
        return $this->success('OTP resent successfully. Please check your email.', null, 200);
    }
}
