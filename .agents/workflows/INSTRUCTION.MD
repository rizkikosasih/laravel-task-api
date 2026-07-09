---
description: Implementasi: OAuth2 Hybrid — Instruksi Teknis
---

## Ringkasan langkah
1. Install package Socialite
   - composer require laravel/socialite
2. Tambahkan env vars (lihat .env.example)
3. Buat 2 migrasi:
   - add_oauth_columns_to_users_table
   - create_oauth_providers_table
4. Buat model OAuthProvider; update User model
5. Tambah Service: AuthService.loginOrRegisterViaOAuth, OAuthService
6. Tambah Controller: SocialAuthController
7. Tambah routes di routes/api.php
8. Tes: unit & integration
9. Review keamanan & CI pipeline

## Migration (recommended filenames)
- database/migrations/2026_07_09_000001_add_oauth_columns_to_users_table.php
- database/migrations/2026_07_09_000002_create_oauth_providers_table.php

## Database changes
- users:
  - is_oauth_user BOOLEAN default false
  - last_login_at TIMESTAMP nullable
- oauth_providers:
  - id, user_id (FK), provider, provider_id (unique), email, profile_data(json), connected_at, timestamps

## Konfigurasi .env
Tambahkan:
- GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_REDIRECT_URI
- GITHUB_CLIENT_ID, GITHUB_CLIENT_SECRET, GITHUB_REDIRECT_URI
Jangan commit nilai nyata ke repo.

## Config services.php
Pastikan config/services.php memuat konfigurasi google & github dari env.

## Security & Policy
- Lakukan rate-limit pada route /api/oauth/*
- Simpan minimal data profile_data (no sensitive tokens)
- Log semua event linking (user_id, provider, provider_id, ip, timestamp)
- Opsional: minta re-verifikasi email sebelum menautkan apabila risk tinggi

## Testing
- Unit tests untuk AuthService.loginOrRegisterViaOAuth kasus: existing provider, existing email (link), new user
- Integration test untuk SocialAuthController callback (mock Socialite driver)
- E2E token validity with Sanctum

## Deployment
- Run migrations in staging first:
  - php artisan migrate
- Run tests
- Rollout canary to subset of users or enable feature flag before full release

## Rollback
- Buat migration drop jika rollback diperlukan
- Pastikan backup DB sebelum migrasi pada production
