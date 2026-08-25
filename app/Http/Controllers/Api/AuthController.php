<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Services\Api\AuthService;
use App\Traits\ApiResponseTrait;

class AuthController extends Controller
{
    use ApiResponseTrait;
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    // -------------------------------------------------------------------------------------------
    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());
        return $this->success('User registered successfully', $user, 201);
    }
    // -------------------------------------------------------------------------------------------
    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());
        
        return match ($result) {
            'unauthorized' => $this->error('Email or password not correct!', null, 422),

            default => $this->success('User login successfully',[
                'access_token' => $result['access_token'],
                'access_token_expires_at' => $result['access_token_expires_at'],
                'token_type' => $result['token_type'],
            ],200)
        };
    }
    // -------------------------------------------------------------------------------------------
    public function logout()
    {
        auth()->user()->tokens()->delete();
        return $this->success('User logout successfully', null, 200);
    }
}
