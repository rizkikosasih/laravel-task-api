# 🚀 OAuth2 Hybrid Implementation Plan
## Isu Utama: Email yang Sama dari Register & OAuth2
### Skenario:

1. User register dengan email `john@gmail.com` + password
2. User logout
3. User mencoba login via Google OAuth2 dengan email `john@gmail.com` yang sama

**Solusi:** Implementasi **Account Linking dengan Email Matching** + **OAuth Provider Tracking**

---

## Phase 1: Database Schema Updates
### 1.1 Migration: Add OAuth Columns to Users Table

```bash
php artisan make:migration add_oauth_columns_to_users_table
```

```PHP
// database/migrations/[timestamp]_add_oauth_columns_to_users_table.php
Schema::table('users', function (Blueprint $table) {
    $table->boolean('is_oauth_user')->default(false)->after('password');
    $table->timestamp('last_login_at')->nullable()->after('is_oauth_user');
});
```

### 1.2 Migration: Create OAuth Providers Table

```bash
php artisan make:migration create_oauth_providers_table
```

```PHP
// database/migrations/[timestamp]_create_oauth_providers_table.php
Schema::create('oauth_providers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->string('provider'); // 'google', 'github', dll
    $table->string('provider_id')->unique(); // OAuth provider's user ID
    $table->string('email'); // Email from OAuth provider
    $table->json('profile_data')->nullable(); // Store name, avatar, etc
    $table->timestamp('connected_at')->useCurrent();
    $table->timestamps();

    $table->index(['provider', 'provider_id']);
});
```

---

## Phase 2: Model Updates
### 2.1 Update User Model

```PHP
// app/Models/User.php
<?php

namespace App\Models;

// ... existing imports ...

class User extends Authenticatable
{
    // ... existing code ...

    protected $fillable = ['name', 'email', 'password', 'is_oauth_user', 'last_login_at'];

    // Relations
    public function oauthProviders()
    {
        return $this->hasMany(OAuthProvider::class);
    }

    // Check if user has specific OAuth provider
    public function hasOAuthProvider(string $provider)
    {
        return $this->oauthProviders()
            ->where('provider', $provider)
            ->exists();
    }
}
```

### 2.2 Create OAuthProvider Model

```bash
php artisan make:model OAuthProvider
```

```PHP
// app/Models/OAuthProvider.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OAuthProvider extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'email',
        'profile_data',
    ];

    protected $casts = [
        'profile_data' => 'json',
        'connected_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

---

## Phase 3: Service Layer
### 3.1 Update AuthService untuk Support OAuth2

```PHP
// app/Services/AuthService.php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\OAuthProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Register with email & password (existing)
     */
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

    /**
     * Login with email & password (existing)
     */
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
     * NEW: Handle OAuth2 Login/Register
     *
     * LOGIC UNTUK EMAIL YANG SAMA:
     * 1. Cek apakah provider_id sudah ada? → Login (existing user)
     * 2. Cek apakah email sudah ada? → Link provider ke user (account merging)
     * 3. Email & provider baru? → Create new user
     */
    public function loginOrRegisterViaOAuth(
        string $provider,
        string $providerId,
        string $email,
        string $name,
        array $profileData = []
    ) {
        // Step 1: Check if OAuth provider already connected
        $oauthProvider = OAuthProvider::where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        if ($oauthProvider) {
            // User sudah login via provider ini sebelumnya
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
            // Email sudah ada → LINK provider ke existing user
            // Scenario: User register dengan email, sekarang login via OAuth dengan email sama

            OAuthProvider::create([
                'user_id' => $existingUser->id,
                'provider' => $provider,
                'provider_id' => $providerId,
                'email' => $email,
                'profile_data' => $profileData,
            ]);

            $existingUser->update([
                'last_login_at' => now(),
                'is_oauth_user' => true, // Mark as OAuth-capable
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

        // Step 3: New user & new provider → Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(32)), // Random password
            'is_oauth_user' => true,
        ]);

        OAuthProvider::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $providerId,
            'email' => $email,
            'profile_data' => $profileData,
        ]);

        // Assign default role
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
```

### 3.2 Create OAuthService untuk Handle Provider Integration

```bash
php artisan make:service OAuthService
```

```PHP
// app/Services/OAuthService.php
<?php

namespace App\Services;

use Laravel\Socialite\Facades\Socialite;

class OAuthService
{
    /**
     * Get redirect URL untuk OAuth provider
     */
    public function getRedirectUrl(string $provider)
    {
        return Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
    }

    /**
     * Handle OAuth callback & get user data
     */
    public function handleCallback(string $provider)
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
```

---

## Phase 4: Controller Implementation
### 4.1 Create SocialAuthController

```bash
php artisan make:controller Http/Controllers/SocialAuthController
```

```PHP
// app/Http/Controllers/SocialAuthController.php
<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\AuthService;
use App\Services\OAuthService;
use Illuminate\Http\Request;

class SocialAuthController extends Controller
{
    public function __construct(
        protected OAuthService $oauthService,
        protected AuthService $authService,
    ) {}

    /**
     * Redirect ke OAuth provider
     * GET /api/oauth/{provider}/redirect
     */
    public function redirect(string $provider)
    {
        $validProviders = ['google', 'github'];

        if (!in_array($provider, $validProviders)) {
            return ApiResponse::error('Invalid OAuth provider', 400);
        }

        try {
            $redirectUrl = $this->oauthService->getRedirectUrl($provider);
            return ApiResponse::success(['redirect_url' => $redirectUrl]);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Handle OAuth callback
     * GET /api/oauth/{provider}/callback?code=...&state=...
     */
    public function callback(string $provider, Request $request)
    {
        try {
            // Step 1: Get user data from OAuth provider
            $oauthData = $this->oauthService->handleCallback($provider);

            // Step 2: Login or register user
            // This handles the email matching logic automatically
            $result = $this->authService->loginOrRegisterViaOAuth(
                provider: $oauthData['provider'],
                providerId: $oauthData['provider_id'],
                email: $oauthData['email'],
                name: $oauthData['name'],
                profileData: $oauthData['profile_data'],
            );

            $statusCode = $result['is_new_account'] ? 201 : 200;
            $message = match (true) {
                $result['is_new_account'] => 'Account created and logged in successfully',
                $result['is_new_provider'] => $result['message'] ?? 'Account linked and logged in',
                default => 'Logged in successfully',
            };

            return ApiResponse::success(
                [
                    'user' => $result['user'],
                    'token' => $result['token'],
                    'is_new_account' => $result['is_new_account'],
                    'is_new_provider' => $result['is_new_provider'] ?? false,
                ],
                $message,
                $statusCode,
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Link additional OAuth provider to existing account
     * POST /api/oauth/{provider}/link
     */
    public function link(string $provider, Request $request)
    {
        $user = $request->user();

        if ($user->hasOAuthProvider($provider)) {
            return ApiResponse::error(
                "Account already linked to {$provider}",
                400
            );
        }

        try {
            $oauthData = $this->oauthService->handleCallback($provider);

            // Check if this provider_id is already linked to another user
            $existingLink = OAuthProvider::where('provider', $provider)
                ->where('provider_id', $oauthData['provider_id'])
                ->first();

            if ($existingLink && $existingLink->user_id !== $user->id) {
                return ApiResponse::error(
                    'This account is already linked to another user',
                    400
                );
            }

            // Link provider
            $user->oauthProviders()->create([
                'provider' => $oauthData['provider'],
                'provider_id' => $oauthData['provider_id'],
                'email' => $oauthData['email'],
                'profile_data' => $oauthData['profile_data'],
            ]);

            $user->update(['is_oauth_user' => true]);

            return ApiResponse::success(
                ['user' => $user],
                "{$provider} linked successfully",
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
```

---

## Phase 5: Routes Update

```PHP
// routes/api.php
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
    });
});

// ✨ NEW: OAuth2 Routes
Route::prefix('oauth')->group(function () {
    // Public routes
    Route::get('/{provider}/redirect', [SocialAuthController::class, 'redirect'])
        ->where('provider', 'google|github');

    Route::get('/{provider}/callback', [SocialAuthController::class, 'callback'])
        ->where('provider', 'google|github');

    // Protected: Link additional OAuth provider to existing account
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/{provider}/link', [SocialAuthController::class, 'link'])
            ->where('provider', 'google|github');
    });
});

// ... rest of routes ...
```

---

## Phase 6: Environment & Package Setup
### 6.1 Install Socialite Package

```bash
composer require laravel/socialite
```

### 6.2 Update `.env.example`

```Dotenv
# ... existing config ...

# OAuth2 Google
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/oauth/google/callback

# OAuth2 GitHub
GITHUB_CLIENT_ID=your-github-client-id
GITHUB_CLIENT_SECRET=your-github-client-secret
GITHUB_REDIRECT_URI=http://localhost:8000/api/oauth/github/callback
```

### 6.3 Config Socialite

Buat file `config/services.php` atau update jika sudah ada:

```PHP
// config/services.php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],

'github' => [
    'client_id' => env('GITHUB_CLIENT_ID'),
    'client_secret' => env('GITHUB_CLIENT_SECRET'),
    'redirect' => env('GITHUB_REDIRECT_URI'),
],
```

---

## Phase 7: Flow Diagram - Email Matching

```Code
┌─────────────────────────────────────────────────────────────┐
│  User Login via OAuth2 (e.g., Google)                       │
│  Email: john@gmail.com                                      │
└─────────────────────────────────────────────────────────────┘
                        ↓
        ┌───────────────────────────────────┐
        │ Check OAuthProvider table          │
        │ provider_id + provider combo       │
        └───────────────────────────────────┘
                ↙                    ↘
              YES                    NO
               ↓                      ↓
        ┌──────────────┐      ┌──────────────────────────┐
        │ User exists  │      │ Check users table        │
        │ & logged in  │      │ WHERE email = input      │
        └──────────────┘      └──────────────────────────┘
                                  ↙              ↘
                                YES              NO
                                 ↓               ↓
                        ┌─────────────────┐ ┌──────────────────┐
                        │ LINK provider   │ │ CREATE new user  │
                        │ to existing     │ │ + provider entry │
                        │ account         │ │ Assign role      │
                        └─────────────────┘ └──────────────────┘
```

---

## Phase 8: API Usage Examples
### Scenario 1: Register User dengan Email/Password

```bash
POST /api/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@gmail.com",
    "password": "secure-password",
    "role": "member"
}

Response 201:
{
    "success": true,
    "data": {
        "user": { "id": 1, "name": "John Doe", "email": "john@gmail.com" },
        "token": "auth_token_here"
    }
}
```

### Scenario 2: Login via Google (Email Baru)

```bash
GET /api/oauth/google/redirect
→ Redirect to Google consent screen

Google callback:
GET /api/oauth/google/callback?code=...&state=...

Response 201:
{
    "success": true,
    "data": {
        "user": { "id": 2, "name": "Jane Doe", "email": "jane@gmail.com" },
        "token": "auth_token_here",
        "is_new_account": true,
        "is_new_provider": true
    }
}
```

### Scenario 3: Login via Google (Email Sama)

```Code
Kondisi:
- User sudah register dengan email john@gmail.com + password
- User sekarang login via Google dengan email john@gmail.com

GET /api/oauth/google/callback?code=...&state=...

Response 200:
{
    "success": true,
    "data": {
        "user": { "id": 1, "name": "John Doe", "email": "john@gmail.com" },
        "token": "auth_token_here",
        "is_new_account": false,
        "is_new_provider": true,
        "message": "Account linked successfully"
    }
}

✅ User berhasil login dengan provider baru yang di-link ke akun existing!
```

### Scenario 4: Link Provider ke Akun Existing

```bash
POST /api/oauth/github/link
Authorization: Bearer {user_token}

Response 200:
{
    "success": true,
    "data": {
        "user": { ... },
        "message": "github linked successfully"
    }
}
```

---

## Phase 9: Database Changes Summary

|File|Action|Details|
|---|---|---|
|`users` table|ADD columns|`is_oauth_user`, `last_login_at`|
|`oauth_providers`|CREATE table|Track provider connections|
|User model|UPDATE|Add `oauthProviders()` relation|
|OAuthProvider|CREATE|New model|
|AuthService|UPDATE|Add `loginOrRegisterViaOAuth()`|
|OAuthService|CREATE|Handle OAuth logic|
|SocialAuthController|CREATE|Endpoints|
|routes/api.php|UPDATE|Add OAuth routes|
|config/services.php|UPDATE|OAuth credentials|
|.env.example|UPDATE|Add OAuth env vars|

---

## Phase 10: Security Considerations ✅

1. **CSRF Protection**: Socialite handles state parameter automatically
2. **Email Verification**: Opcional - dapat diimplementasikan untuk OAuth users
3. **Rate Limiting**: Tambahkan ke `/api/oauth/*` routes
4. **Account Takeover**:
    - Email verification sebelum link provider
    - Logging semua account linking events
5. **Data Privacy**: Store minimal data dari OAuth provider

---

## Key Benefits dari Implementasi Ini

✅ **Seamless Account Linking**: Jika email sama, otomatis di-link
✅ **No Duplicate Accounts**: Email unique constraint tetap terjaga
✅ **Flexible OAuth**: Support multiple providers (Google, GitHub, dll)
✅ **Backward Compatible**: Existing email/password auth tetap berfungsi
✅ **RBAC Maintained**: Spatie permission tetap jalan dengan OAuth users
✅ **Audit Trail**: OAuthProvider table track semua provider connections
