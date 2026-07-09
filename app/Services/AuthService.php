<?php

namespace App\Services;

use App\Models\User;
use App\Models\OAuthProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_oauth_user' => false,
        ]);

        $user->assignRole($data['role'] ?? 'member');

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(array $data)
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            abort(401, 'Invalid credentials');
        }

        $user->tokens()->delete();
        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Handle OAuth2 Login/Register
     *
     * LOGIC FOR EMAIL MATCHING:
     * 1. Check if provider_id and provider match an existing oauth_providers record -> Login.
     * 2. Else, check if the email exists in users table -> Link provider to user and login.
     * 3. Else, email & provider are new -> Create a new user and login.
     */
    public function loginOrRegisterViaOAuth(
        string $provider,
        string $providerId,
        string $email,
        string $name,
        array $profileData = []
    ): array {
        // Step 1: Check if OAuth provider already connected
        $oauthProvider = OAuthProvider::where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        if ($oauthProvider) {
            $user = $oauthProvider->user;
            $user->update(['last_login_at' => now()]);
            $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token,
                'is_new_account' => false,
                'is_new_provider' => false,
            ];
        }

        // Step 2: Check if email already exists in our database
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            OAuthProvider::create([
                'user_id' => $existingUser->id,
                'provider' => $provider,
                'provider_id' => $providerId,
                'email' => $email,
                'profile_data' => $profileData,
            ]);

            $existingUser->update([
                'last_login_at' => now(),
                'is_oauth_user' => true,
            ]);

            $existingUser->tokens()->delete();
            $token = $existingUser->createToken('auth_token')->plainTextToken;

            return [
                'user' => $existingUser,
                'token' => $token,
                'is_new_account' => false,
                'is_new_provider' => true,
                'message' => 'Account linked successfully',
            ];
        }

        // Step 3: New user & new provider
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(32)),
            'is_oauth_user' => true,
            'last_login_at' => now(),
        ]);

        OAuthProvider::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $providerId,
            'email' => $email,
            'profile_data' => $profileData,
        ]);

        $user->assignRole('member');

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'is_new_account' => true,
            'is_new_provider' => true,
        ];
    }
}
