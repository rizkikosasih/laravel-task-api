<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:6'],
        ]);

        $result = $this->authService->register($request->all());

        return response()->json([
            'message' => 'Register success',
            'user' => $result['user'],
            'token' => $result['token'],
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $result = $this->authService->login($request->all());

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
