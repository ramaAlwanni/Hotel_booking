<?php

namespace App\Services\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService 
{
    public function register(array $data)
    {
        User::where('email', $data['email'])->first();

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
            return 'unauthorized';
        }

        $user = User::where('email', $data['email'])->first();

        if ($user->email_verified_at == null) {
            return 'email_not_verified';
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
