<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validate());

        return response()->json([
            'message' => 'Register success',
            'user' => $result['user'],
            'token' => $result['token'],
        ]);
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validate());

        return response()->json([
            'message' => 'Login success',
            'user' => $result['user'],
            'token' => $result['token'],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout success',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => $user->only(['id', 'name', 'email']),
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions(),
        ]);
    }
}
