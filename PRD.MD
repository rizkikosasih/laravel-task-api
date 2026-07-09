# PRD: OAuth2 Hybrid (Account Linking via Email Matching)
Versi: 1.0
Tanggal: 2026-07-09
Pemilik Produk: Rizki Kosasih

## Ringkasan
Implementasi OAuth2 hybrid untuk aplikasi Laravel agar user yang sudah terdaftar via email/password tidak membuat akun duplikat ketika login lewat provider OAuth (mis. Google/GitHub). Sistem akan:
- Melacak koneksi provider per user (oauth_providers)
- Menautkan akun berdasarkan kecocokan email
- Menyimpan metadata provider untuk audit dan profile
- Menyediakan endpoint untuk redirect, callback, dan link provider tambahan

## Tujuan dan Keberhasilan
- Mencegah duplikasi akun (email tetap unik)
- Pengalaman login mulus (seamless) bagi user
- Menyediakan jejak audit (oauth_providers)
- Kompatibel dengan RBAC (Spatie) dan Sanctum token

Key success metrics:
- 100% user OAuth dengan email terverifikasi di-link ke akun existing ketika email sama
- 0 duplicate accounts pada email yang sama setelah rollout
- Tes integrasi automated lulus (CI)

## Ruang Lingkup
In-scope:
- DB migrations (users columns + oauth_providers)
- Model: OAuthProvider, update User
- Service layer: AuthService + OAuthService
- Controller: SocialAuthController
- Routes API tambahan, konfigurasi Socialite, .env vars
- Unit & integration tests, security checks

Out-of-scope:
- Single Sign-On SAML
- Federated login enterprise (beyond Google/GitHub) — dapat ditambah nanti
- UI frontend (hanya API)

## Pemangku Kepentingan
- Product Owner: Rizki
- Engineering: Backend team
- Security: InfoSec reviewer
- QA: Test automation engineer

## User Stories (prioritas)
1. Sebagai user yang sudah register via email/password, saya ingin login via Google dengan email yang sama dan terhubung ke akun lama sehingga tidak perlu membuat akun baru.
2. Sebagai user, saya ingin menambah (link) provider tambahan di akun saya.
3. Sebagai admin, saya ingin melihat daftar provider yang terhubung ke user untuk audit.

## Acceptance Criteria
- Migrasi berhasil tanpa menghapus data existing
- Callback OAuth mengembalikan token Sanctum valid
- Jika provider_id ada: login ke user terkait
- Jika provider_id tidak ada tetapi email ditemukan: buat oauth_providers record dan link ke user (return message "Account linked successfully")
- Jika email & provider baru: buat user baru + oauth_providers
- Role default di-assign (member)
- Unit & integration tests cover cases di atas

## Risikio & Mitigasi
- Account takeover via email spoofing => Mitigasi: verifikasi email optional, log aktivitas linking, rate-limiting
- Leak secret client_id/client_secret => Mitigasi: env, vault, no commit ke repo
