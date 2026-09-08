<?php

namespace App\Services\Api;

use App\Exceptions\General\EmailAlreadyVerifiedException;
use App\Exceptions\OTP\InvalidOTPException;
use App\Exceptions\OTP\OTPExpiredException;
use App\Models\User;
use App\Notifications\OTPNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class OTPService
{
    public function sendOTP(object $user)
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put( "otp:user:{$user->id}",
                    Hash::make($otp),
                    now()->addMinutes(5)
        );
        $user->notify((new OTPNotification($otp))->onQueue('default'));
    }
    //************************************************* */
    public function verifyOtp(array $request)
    {
        $user = User::where('email', $request['email'])->firstOrFail();

        if ($user->email_verified_at) {
            throw new EmailAlreadyVerifiedException;
        }

        $hashedOtp = Cache::get("otp:user:{$user->id}");

        if (!$hashedOtp) {
            throw new OTPExpiredException;
        }

        if (!Hash::check($request['otp'], $hashedOtp)) {
            throw new InvalidOTPException;
        }

        Cache::forget("otp:user:{$user->id}");

        $user->update([
            'email_verified_at' => now(),
        ]);
       
        return $user;
    }
    //************************************************* */
    public function resendOtp(array $request)
    {
        $user = User::where('email', $request['email'])->firstOrFail();

        if ($user->email_verified_at) {
            throw new EmailAlreadyVerifiedException;
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put(
            "otp:user:{$user->id}",
            Hash::make($otp),
            now()->addMinutes(5)
        );
        $user->notify(new OTPNotification($otp));
    }
}
