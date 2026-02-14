<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $user = $this->getUser();

        return $this->response(
            message: 'User details retrieved successfully',
            data: $user
        );
    }
}
