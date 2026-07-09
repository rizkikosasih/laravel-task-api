<?php

namespace App\Services;

use Laravel\Socialite\Facades\Socialite;

class OAuthService
{
    /**
     * Get redirect URL for OAuth provider.
     */
    public function getRedirectUrl(string $provider): string
    {
        return Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
    }

    /**
     * Handle OAuth callback & get user data.
     */
    public function handleCallback(string $provider): array
    {
        try {
            $oauthUser = Socialite::driver($provider)->stateless()->user();

            return [
                'provider' => $provider,
                'provider_id' => $oauthUser->getId(),
                'email' => $oauthUser->getEmail(),
                'name' => $oauthUser->getName(),
                'profile_data' => [
                    'avatar' => $oauthUser->getAvatar(),
                    'nickname' => $oauthUser->getNickname(),
                    'raw' => $oauthUser->getRaw(),
                ],
            ];
        } catch (\Exception $e) {
            throw new \Exception("OAuth authentication failed: {$e->getMessage()}");
        }
    }
}
