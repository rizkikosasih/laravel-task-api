# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added
- Added `laravel/socialite` dependency for OAuth2 client support.
- Added database migrations for OAuth column updates and `oauth_providers` tracking.
- Added `OAuthProvider` model and relations to `User` model.
- Added `OAuthService` and `SocialAuthController`.
- Added routing endpoints `/api/oauth/{provider}/redirect`, `/api/oauth/{provider}/callback`, and `/api/oauth/{provider}/link`.
- Added unit and integration tests for OAuth login/linking.
