<?php

namespace App\Exceptions\General;

use App\Exceptions\BaseException;

class EmailNotVerifiedException extends BaseException
{
    public function __construct()
    {
        parent::__construct(
            'Please verify your email.',
            403
        );
    }
}
