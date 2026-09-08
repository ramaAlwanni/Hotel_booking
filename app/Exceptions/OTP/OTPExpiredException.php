<?php

namespace App\Exceptions\OTP;

use App\Exceptions\BaseException;

class OTPExpiredException extends BaseException
{
    public function __construct()
    {
        parent::__construct(
            'OTP has expired.',
            422
        );
    }
}
