<?php

namespace App\Exceptions\OTP;

use App\Exceptions\BaseException;

class InvalidOTPException extends BaseException
{
    public function __construct()
    {
        parent::__construct(
            'Invalid OTP!',
            422
        );
    }
}
