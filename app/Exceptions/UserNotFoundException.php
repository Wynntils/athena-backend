<?php

namespace App\Exceptions;

class UserNotFoundException extends \Exception
{
    public function render($request)
    {
        return response()->json([
            'message' => 'User not found',
        ], 404);
    }
}
