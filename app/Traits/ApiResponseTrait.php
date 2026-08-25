<?php

namespace App\Traits;

trait ApiResponseTrait
{
    public function success($message = 'Success', $data = null, $statusCode = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
    
    public function error($message = 'Error', $errors = null, $statusCode = 400)
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
