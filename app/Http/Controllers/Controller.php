<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use JsonSerializable;

abstract class Controller
{
    final protected function getUser(): ?User
    {
        return auth('api')->user();
    }

    final public function response(
        string $message,
        null|array|JsonSerializable $data = null,
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'success' => ! ($status >= 300),
            'message' => $message,
            'data' => $data
        ], $status);
    }
}
