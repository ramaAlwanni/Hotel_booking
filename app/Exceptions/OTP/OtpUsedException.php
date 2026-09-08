<?php

namespace App\Exceptions\OTP;

use App\Exceptions\BaseException;

class OtpUsedException extends BaseException
{
    public function __construct()
    {
        parent::__construct(
            'OTP has already been used.',
            422
        );
    }
}
