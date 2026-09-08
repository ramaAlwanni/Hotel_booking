<?php

namespace App\Services\Api;

use App\Exceptions\Auth\InvalidCredentialsException;
use App\Http\Resources\UserResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\General\EmailNotVerifiedException;

class AuthService 
{
    public function register(array $data)
    {
        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        // $user->assignRole('user');
        return $user;
    }

    // ///////////////////////////////////////////////////////////////////////////////////
    public function login(array $data)
    {
        $credentials = [
            'email' => $data['email'],
            'password' => $data['password']
        ];

        if (!Auth::attempt($credentials)) {
            throw new InvalidCredentialsException;
        }

        $user = User::where('email', $data['email'])->first();

        if ($user->email_verified_at == null) {
            throw new EmailNotVerifiedException;
        }

        $user->tokens()->delete();
        $accessTokenExpiresAt = Carbon::now()->addDays(1);
        $accessToken = $user->createToken('access_token', ['patient'], $accessTokenExpiresAt)->plainTextToken;
        return [
            'user' => new UserResource($user),
            'access_token' =>  $accessToken,
            'access_token_expires_at' => '1 day',
            'token_type' => 'Bearer',
        ];
    }
    //************************************ */

}
