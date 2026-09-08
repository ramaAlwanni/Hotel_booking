<?php

namespace App\Exceptions\General;

use App\Exceptions\BaseException;

class EmailAlreadyVerifiedException extends BaseException
{
    public function __construct()
    {
        parent::__construct(
            'Email already verified!',
            422
        );
    }
}
