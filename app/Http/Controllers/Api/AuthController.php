<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

final class AuthController extends Controller
{
    private int $ttl;

    private JWTGuard|Guard $auth;

    public function __construct()
    {
        $this->ttl = 4320;
        $this->auth = auth('api');
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        if (! $token = $this->auth->attempt($credentials)) {
            return $this->response(
                message: 'Invalid login credentials',
                status: 401
            );
        }

        $data = $this->tokenData($token);

        return $this->response(
            message: 'Login successful',
            data: $data
        );
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'role' => $validated['role'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password'])
        ]);

        $token = $this->auth->setTTL($this->ttl)->login($user);

        return $this->response(
            status: 201,
            message: 'Registration completed successfully',
            data: $this->tokenData($token)
        );
    }

    public function logout(): JsonResponse
    {
        $this->auth->logout();

        return $this->response(message: 'Logout successful');
    }

    public function refresh(): JsonResponse
    {
        return $this->response(
            message: 'Token refreshed',
            data: $this->tokenData(
                $this->auth->setTTL($this->ttl)->refresh()
            )
        );
    }

    private function tokenData(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->ttl * 60
        ];
    }
}
