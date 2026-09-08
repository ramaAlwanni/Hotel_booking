<?php

namespace App\Services\Api;

use App\Exceptions\General\EmailAlreadyVerifiedException;
use App\Exceptions\OTP\InvalidOTPException;
use App\Exceptions\OTP\OTPExpiredException;
use App\Exceptions\OTP\OtpUsedException;
use App\Models\User;
use App\Notifications\OTPNotification;
use Carbon\Carbon;

class OTPService
{
    public function sendOTP(object $user)
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = Carbon::now()->addMinutes(5);

        $user->notify((new OTPNotification($otp))->onQueue('default'));

        $user->update([
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);
    }
    //************************************************* */
    public function verifyOtp(array $request)
    {
        $user = User::where('email', $request['email'])->firstOrFail();

        if ($user->email_verified_at) {
            throw new EmailAlreadyVerifiedException;
        }

        if ($user->otp != $request['otp']) {
            throw new InvalidOTPException;
        }

        if ($user->expires_at->isPast()) {
            throw new OTPExpiredException;
        }

        if ($user->otp == null) {
            throw new OtpUsedException;
        }

        $user->update([
            'otp' => null,
            'expires_at' => null,
            'email_verified_at' => now()
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
        $expiresAt = Carbon::now()->addMinutes(5);

        $user->update([
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);
        $user->notify(new OTPNotification($otp));
    }
}
