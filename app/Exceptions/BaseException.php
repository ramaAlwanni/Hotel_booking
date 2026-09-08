<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

abstract class BaseException extends Exception
{
    public function __construct(string $message, protected int $httpCode) {
        parent::__construct($message);
    }
//-----------------------------------------------
    public function render(Request $request)
    {
        //Api
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Error',
                'error' =>  $this->getMessage(),
            ],  $this->httpCode);
        }

        //Web
        return response()->view('errors.custom', [
            'message' => 'Error',
            'error' => $this->getMessage()
        ], $this->httpCode);
    }
}
