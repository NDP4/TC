# TC - Talent Connect API

Talent Connect adalah platform yang menghubungkan freelancer dengan klien untuk berbagai layanan profesional. API ini dibangun menggunakan Laravel dan menyediakan endpoint untuk manajemen user, autentikasi, dan berbagai fitur lainnya.

## 🚀 Fitur Utama

-   **JWT Authentication** - Sistem autentikasi berbasis JSON Web Token
-   **User Management** - Registrasi, login, dan manajemen profil user
-   **Avatar Upload** - Upload dan manajemen avatar user
-   **Skill Level System** - Sistem level keahlian user
-   **Reputation System** - Sistem reputasi berbasis rating
-   **Code Quality** - Menggunakan PHP-CS-Fixer untuk konsistensi kode
-   **Request Validation** - Validasi komprehensif untuk semua input
-   **Error Handling** - Response error yang konsisten dan informatif

## 📋 Requirements

-   PHP >= 8.1
-   Composer
-   Laravel 11.x
-   SQLite/MySQL/PostgreSQL
-   JWT Auth Package (php-open-source-saver/jwt-auth)

## 🛠 Installation

1. Clone repository

```bash
git clone https://github.com/NDP4/TC.git
cd TC
```

2. Install dependencies

```bash
composer install
```

3. Copy environment file

```bash
cp .env.example .env
```

4. Generate application key

```bash
php artisan key:generate
```

5. Generate JWT secret

```bash
php artisan jwt:secret
```

6. Run migrations

```bash
php artisan migrate
```

7. Run seeders (optional)

```bash
php artisan db:seed
# Or run specific seeders
php artisan db:seed --class=SkillLevelSeeder
php artisan db:seed --class=UserSeeder
```

8. Start development server

```bash
php artisan serve
```

```bash
php artisan migrate
```

7. Run seeders (optional)

```bash
php artisan db:seed
# Or run specific seeders
php artisan db:seed --class=SkillLevelSeeder
php artisan db:seed --class=UserSeeder
```

8. Start development server

```bash
php artisan serve
```

## 📚 API Documentation

**Base URL:** `http://localhost:8000/api/v1`

**Authentication:** Bearer Token (JWT)

**Pagination:** Gunakan parameter `?page=1&limit=10` pada endpoint GET

**Content-Type:** `application/json` (untuk JSON requests) atau `multipart/form-data` (untuk file uploads)

### 🔐 Authentication Endpoints

#### Login User

```http
POST /auth/login
Content-Type: application/json
```

**Request Body:**

```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response (200):**

```json
{
    "status": "success",
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "phone_number": "+628123456789",
            "avatar": "avatars/avatar.jpg",
            "skill_level": "Advanced",
            "reputation_score": 4.8,
            "is_active": true,
            "created_at": "2025-01-15T10:30:00Z"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

#### Login with Google

```http
GET /auth/google/redirect
```

**Description:** Redirects user to Google OAuth authorization page.

**Response:** Redirect to Google OAuth consent screen.

```http
GET /auth/google/callback
```

**Description:** Handles Google OAuth callback after user authorization.

**Response (200):**

```json
{
    "status": "success",
    "message": "Login with Google successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@gmail.com",
            "phone_number": null,
            "avatar": "https://lh3.googleusercontent.com/a/...",
            "skill_level": "Beginner",
            "reputation_score": 0.0,
            "is_active": true,
            "created_at": "2025-01-15T10:30:00Z"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

#### Register User

```http
POST /auth/register
POST /users/register
Content-Type: application/json
```

**Request Body:**

```json
{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "SecurePassword123!",
    "password_confirmation": "SecurePassword123!",
    "phone_number": "+628987654321",
    "skill_level_id": 2
}
```

**Response (201):**

```json
{
    "status": "success",
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 2,
            "name": "Jane Doe",
            "email": "jane@example.com",
            "phone_number": "+628987654321",
            "avatar": null,
            "skill_level": "Intermediate",
            "reputation_score": 0.0,
            "is_active": true,
            "created_at": "2025-01-15T10:30:00Z"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

#### Logout User

```http
POST /auth/logout
Authorization: Bearer {token}
```

**Response (200):**

```json
{
    "status": "success",
    "message": "Successfully logged out"
}
```

#### Refresh Token

```http
POST /auth/refresh
Authorization: Bearer {token}
```

**Response (200):**

```json
{
    "status": "success",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

#### Get Current User Profile

```http
GET /auth/me
Authorization: Bearer {token}
```

**Response (200):**

```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "phone_number": "+628123456789",
        "avatar": "avatars/avatar.jpg",
        "skill_level": "Advanced",
        "reputation_score": 4.8,
        "is_active": true,
        "created_at": "2025-01-15T10:30:00Z"
    }
}
```

### 👤 User Management Endpoints

#### Get User Profile by ID

```http
GET /users/{id}
Authorization: Bearer {token}
```

**Response (200):**

```json
{
    "id": 123,
    "name": "John Doe",
    "email": "john@example.com",
    "phone_number": "+628123456789",
    "avatar": "http://localhost:8000/storage/avatars/avatar.jpg",
    "skill_level": "Advanced",
    "reputation_score": 4.8,
    "is_active": true,
    "created_at": "2025-01-15T10:30:00Z"
}
```

#### Update User Profile

```http
PUT /users/{id}
POST /users/{id}/update
Authorization: Bearer {token}
Content-Type: multipart/form-data
Content-Type: application/json
```

**Request Body (Form Data):**

```
name: "John Updated"
phone_number: "+6281122334455"
skill_level_id: 3
avatar: [file upload - jpeg/png/jpg/gif, max 2MB]
password: "newpassword123"
password_confirmation: "newpassword123"
```

**Request Body (JSON - without file upload):**

```json
{
    "name": "John Updated",
    "phone_number": "+6281122334455",
    "skill_level_id": 3,
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Response (200):**

```json
{
    "status": "success",
    "message": "Profile updated successfully",
    "data": {
        "id": 123,
        "name": "John Updated",
        "email": "john@example.com",
        "phone_number": "+6281122334455",
        "avatar": "http://localhost:8000/storage/avatars/new_avatar.jpg",
        "skill_level": "Expert",
        "reputation_score": 4.8,
        "is_active": true,
        "created_at": "2025-01-15T10:30:00Z"
    }
}
```

#### Get Users List (with Pagination)

```http
GET /users?page=1&limit=10&search=john&skill_level_id=2&is_active=true
Authorization: Bearer {token}
```

**Query Parameters:**

-   `page` (optional): Page number (default: 1)
-   `limit` (optional): Items per page (default: 10, max: 100)
-   `search` (optional): Search by name or email
-   `skill_level_id` (optional): Filter by skill level ID
-   `is_active` (optional): Filter by active status (true/false)

**Response (200):**

```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "phone_number": "+628123456789",
            "avatar": "avatars/avatar.jpg",
            "skill_level": "Advanced",
            "reputation_score": 4.8,
            "is_active": true,
            "created_at": "2025-01-15T10:30:00Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 10,
        "total": 50,
        "from": 1,
        "to": 10
    }
}
```

### 🔔 Notification Endpoints

#### Get All Notifications for Authenticated User

```http
GET /notifications
Authorization: Bearer {token}
```

**Response (200):**

```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "message": "Your level up request has been approved!",
            "read_status": false,
            "created_at": "2025-01-15T10:30:00Z"
        }
        // ...more notifications
    ]
}
```

#### Mark Notification as Read

```http
POST /notifications/{id}/read
Authorization: Bearer {token}
```

**Response (200):**

```json
{
    "status": "success",
    "message": "Notification marked as read",
    "data": {
        "id": 1,
        "message": "Your level up request has been approved!",
        "read_status": true,
        "created_at": "2025-01-15T10:30:00Z"
    }
}
```

**Error (404):**

```json
{
    "status": "error",
    "message": "Notification not found"
}
```

### 📝 Request Validation Rules

#### Login Request

-   `email`: required, valid email format
-   `password`: required, minimum 6 characters

#### Register Request

-   `name`: required, string, max 255 characters
-   `email`: required, valid email, unique in users table
-   `password`: required, min 6 characters, must be confirmed
-   `password_confirmation`: required, must match password
-   `phone_number`: optional, string, max 20 characters
-   `skill_level_id`: optional, must exist in skill_levels table

#### Update Profile Request

-   `name`: optional, string, max 255 characters
-   `phone_number`: optional, string, max 20 characters
-   `skill_level_id`: optional, must exist in skill_levels table
-   `avatar`: optional, image file (jpeg/png/jpg/gif), max 2MB
-   `password`: optional, min 6 characters, must be confirmed
-   `password_confirmation`: required if password is provided

### 📝 Error Responses

#### Validation Error (422):

```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 6 characters."]
    }
}
```

#### Authentication Error (401):

```json
{
    "status": "error",
    "message": "Invalid credentials"
}
```

#### Token Expired (401):

```json
{
    "status": "error",
    "message": "Token expired"
}
```

#### Token Invalid (401):

```json
{
    "status": "error",
    "message": "Token invalid"
}
```

#### Token Absent (401):

```json
{
    "status": "error",
    "message": "Token absent"
}
```

#### Not Found (404):

```json
{
    "status": "error",
    "message": "User not found"
}
```

#### Forbidden (403):

```json
{
    "status": "error",
    "message": "Unauthorized to update this profile"
}
```

#### Server Error (500):

```json
{
    "status": "error",
    "message": "Could not create token"
}
```

## 🧪 Testing

### Unit Testing

Jalankan test dengan perintah:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AuthTest.php

# Run with coverage
php artisan test --coverage

# Run with parallel execution
php artisan test --parallel
```

### API Testing dengan cURL

#### 1. Register User

```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone_number": "+628123456789",
    "skill_level_id": 2
  }'
```

#### 2. Login User

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

#### 3. Get User Profile (Replace {token} with actual token)

```bash
curl -X GET http://localhost:8000/api/v1/users/1 \
  -H "Authorization: Bearer {token}"
```

#### 4. Update User Profile

```bash
curl -X PUT http://localhost:8000/api/v1/users/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Name",
    "phone_number": "+628987654321",
    "skill_level_id": 3
  }'
```

### API Testing dengan Postman

Import file `TC_API.postman_collection.json` ke Postman untuk testing API endpoints.

**Steps:**

1. Buka Postman
2. Click **Import**
3. Pilih file `TC_API.postman_collection.json`
4. Set environment variables:
    - `base_url`: `http://localhost:8000/api/v1`
    - `token`: Bearer token dari response login

**Collection includes:**

-   Authentication endpoints (login, register, logout, refresh, me)
-   User management endpoints (get profile, update profile, list users)
-   Pre-configured request bodies dan headers
-   Environment variables untuk base URL dan token

### Sample Data untuk Testing

Setelah menjalankan seeder, tersedia sample users untuk testing:

| Email             | Password    | Name          | Skill Level  | Status   |
| ----------------- | ----------- | ------------- | ------------ | -------- |
| john@example.com  | password123 | John Doe      | Advanced     | Active   |
| jane@example.com  | password123 | Jane Smith    | Intermediate | Active   |
| bob@example.com   | password123 | Bob Wilson    | Beginner     | Active   |
| alice@example.com | password123 | Alice Johnson | Expert       | Inactive |

### Writing Tests

Contoh test untuk API authentication:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'phone_number',
                            'avatar',
                            'skill_level',
                            'reputation_score',
                            'is_active',
                            'created_at'
                        ],
                        'token',
                        'token_type',
                        'expires_in'
                    ]
                ]);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ]);
    }
}
```

## 🎨 Code Quality & PHP-CS-Fixer

Proyek ini menggunakan **PHP-CS-Fixer** untuk menjaga konsistensi dan kualitas kode sesuai dengan standar coding yang ditetapkan.

### 🔧 Instalasi PHP-CS-Fixer

PHP-CS-Fixer sudah terinstall sebagai dev dependency. Jika belum terinstall, jalankan:

```bash
composer require --dev friendsofphp/php-cs-fixer
```

### ⚙️ Konfigurasi PHP-CS-Fixer

File konfigurasi: `.php-cs-fixer.php`

```php
<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@PSR12' => true,
        'declare_strict_types' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'not_operator_with_successor_space' => true,
        'trailing_comma_in_multiline' => true,
        'phpdoc_scalar' => true,
        'unary_operator_spaces' => true,
        'binary_operator_spaces' => true,
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_var_without_name' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => true,
        ],
        'single_trait_insert_per_statement' => true,
    ])
    ->setFinder($finder)
    ->setUsingCache(false);
```

### 🚀 Menjalankan PHP-CS-Fixer

#### Memeriksa kode tanpa melakukan perubahan:

```bash
vendor/bin/php-cs-fixer fix --dry-run --diff
```

#### Memperbaiki kode secara otomatis:

```bash
vendor/bin/php-cs-fixer fix
```

#### Memeriksa folder spesifik:

```bash
vendor/bin/php-cs-fixer fix app/ --dry-run --diff
vendor/bin/php-cs-fixer fix database/ --dry-run --diff
vendor/bin/php-cs-fixer fix routes/ --dry-run --diff
```

#### Memperbaiki file spesifik:

```bash
vendor/bin/php-cs-fixer fix app/Http/Controllers/AuthController.php --diff
```

#### Menampilkan verbose output:

```bash
vendor/bin/php-cs-fixer fix --dry-run --diff --verbose
```

### 📋 Rules yang Diimplementasi

**Core Standards:**

-   `@PSR12` - PSR-12 Extended Coding Style compliance
-   `declare_strict_types` - Enforce strict types declaration

**Array & Syntax:**

-   `array_syntax` - Use short array syntax `[]` instead of `array()`
-   `trailing_comma_in_multiline` - Add trailing comma in multiline arrays

**Imports & Namespaces:**

-   `ordered_imports` - Sort imports alphabetically
-   `no_unused_imports` - Remove unused imports
-   `single_trait_insert_per_statement` - Each trait import on separate line

**Operators & Spacing:**

-   `binary_operator_spaces` - Proper spacing around binary operators
-   `unary_operator_spaces` - Proper spacing for unary operators
-   `not_operator_with_successor_space` - Space after `!` operator

**Documentation:**

-   `phpdoc_scalar` - Use correct PHPDoc scalar types
-   `phpdoc_single_line_var_spacing` - Single line var PHPDoc spacing
-   `phpdoc_var_without_name` - Remove variable name from @var annotations

**Control Flow:**

-   `blank_line_before_statement` - Blank line before control statements
-   `method_argument_space` - Proper spacing in method arguments

### 🎯 Contoh Sebelum dan Sesudah PHP-CS-Fixer

**Sebelum:**

```php
<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller{
    public function show(Request $request,$id):JsonResponse
    {
        $user=User::find($id);
        if(!$user){
            return response()->json(['error'=>'User not found'],404);
        }
        return response()->json($user);
    }
}
```

**Sesudah:**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(Request $request, string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user);
    }
}
```

### 🔄 Pre-commit Hook (Opsional)

Untuk memastikan kode selalu ter-format dengan baik sebelum commit, buat pre-commit hook:

1. Buat file `.git/hooks/pre-commit`:

```bash
#!/bin/sh

echo "Running PHP-CS-Fixer..."

# Run PHP-CS-Fixer
vendor/bin/php-cs-fixer fix --dry-run --diff --stop-on-violation

if [ $? != 0 ]
then
    echo "Fix the code style issues before committing."
    exit 1
fi

exit $?
```

2. Buat file executable:

```bash
chmod +x .git/hooks/pre-commit
```

### 📊 GitHub Actions (CI/CD)

Tambahkan PHP-CS-Fixer check di `.github/workflows/ci.yml`:

```yaml
name: CI

on: [push, pull_request]

jobs:
    code-style:
        runs-on: ubuntu-latest
        steps:
            - uses: actions/checkout@v3

            - name: Setup PHP
              uses: shivammathur/setup-php@v2
              with:
                  php-version: "8.1"

            - name: Install dependencies
              run: composer install --prefer-dist --no-progress

            - name: Run PHP-CS-Fixer
              run: vendor/bin/php-cs-fixer fix --dry-run --diff --stop-on-violation
```

### 🛡️ Editor Integration

#### VS Code

Install extension: **PHP CS Fixer**
Tambahkan ke `settings.json`:

```json
{
    "php-cs-fixer.executablePath": "${workspaceFolder}/vendor/bin/php-cs-fixer",
    "php-cs-fixer.config": "${workspaceFolder}/.php-cs-fixer.php",
    "editor.formatOnSave": true
}
```

#### PHPStorm

1. Go to **Settings** → **Tools** → **External Tools**
2. Add new tool:
    - **Name:** PHP-CS-Fixer
    - **Program:** `$ProjectFileDir$/vendor/bin/php-cs-fixer`
    - **Arguments:** `fix $FilePath$ --config=$ProjectFileDir$/.php-cs-fixer.php`
    - **Working directory:** `$ProjectFileDir$`

### 📝 Coding Standards Checklist

Sebelum commit, pastikan kode Anda memenuhi standar berikut:

-   [ ] ✅ Menggunakan `declare(strict_types=1)` di semua file PHP
-   [ ] ✅ Import statements diurutkan secara alfabetis
-   [ ] ✅ Tidak ada unused imports
-   [ ] ✅ Menggunakan short array syntax `[]`
-   [ ] ✅ Proper spacing di sekitar operators
-   [ ] ✅ PHPDoc yang benar dan konsisten
-   [ ] ✅ Method visibility selalu dideklarasikan
-   [ ] ✅ Trailing comma pada multiline arrays
-   [ ] ✅ Blank line sebelum return statements
-   [ ] ✅ Konsisten dengan PSR-12 standards

### 🚨 Common Issues & Solutions

**Issue 1:** Permission denied error

```bash
chmod +x vendor/bin/php-cs-fixer
```

**Issue 2:** Memory limit exceeded

```bash
php -d memory_limit=256M vendor/bin/php-cs-fixer fix
```

**Issue 3:** Cache issues

```bash
vendor/bin/php-cs-fixer fix --using-cache=no
```

## 📁 Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php       # Authentication endpoints
│   │   │   └── UserController.php       # User management endpoints
│   │   ├── Middleware/
│   │   │   └── JwtMiddleware.php        # JWT authentication middleware
│   │   └── Requests/
│   │       ├── LoginRequest.php         # Login validation rules
│   │       ├── RegisterRequest.php      # Registration validation rules
│   │       └── UpdateProfileRequest.php # Profile update validation rules
│   └── Models/
│       ├── User.php                     # User model with JWT interface
│       ├── SkillLevel.php              # Skill level model
│       └── ...other models
├── database/
│   ├── migrations/
│   │   ├── 2025_07_25_173307_create_skill_levels_table.php
│   │   ├── 2025_07_25_174402_update_users_table_add_fields.php
│   │   └── ...other migrations
│   └── seeders/
│       ├── SkillLevelSeeder.php        # Seed skill levels data
│       ├── UserSeeder.php              # Seed sample users
│       └── DatabaseSeeder.php          # Main seeder
├── routes/
│   └── api.php                         # API routes definition
├── tests/
│   ├── Feature/                        # Feature tests
│   └── Unit/                          # Unit tests
├── storage/
│   └── app/
│       └── public/
│           └── avatars/               # User avatar uploads
├── .php-cs-fixer.php                  # PHP-CS-Fixer configuration
├── phpunit.xml                        # PHPUnit configuration
└── composer.json                      # Dependencies and scripts
```

## 🔧 Environment Variables

Tambahkan ke file `.env`:

```env
# Application
APP_NAME="TC - Talent Connect API"
APP_ENV=local
APP_KEY=base64:your-app-key-here
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
# Or for MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=tc_database
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# JWT Configuration
JWT_SECRET=your-jwt-secret-key-here
JWT_TTL=60                             # Token TTL in minutes
JWT_REFRESH_TTL=20160                  # Refresh token TTL in minutes
JWT_ALGO=HS256                         # JWT algorithm
JWT_REQUIRED_CLAIMS=iss,iat,exp,nbf,sub,jti
JWT_PERSISTENT_CLAIMS=
JWT_LOCK_SUBJECT=true
JWT_LEEWAY=0
JWT_BLACKLIST_ENABLED=true
JWT_BLACKLIST_GRACE_PERIOD=0
JWT_DECRYPT_COOKIES=false
JWT_PROVIDERS_USER=Illuminate\Auth\EloquentUserProvider
JWT_PROVIDERS_JWT=PHPOpenSourceSaver\JWTAuth\Providers\JWT\Lcobucci
JWT_PROVIDERS_AUTH=PHPOpenSourceSaver\JWTAuth\Providers\Auth\Illuminate
JWT_PROVIDERS_STORAGE=PHPOpenSourceSaver\JWTAuth\Providers\Storage\Illuminate

# Google OAuth Configuration
GOOGLE_CLIENT_ID=your-google-client-id-here
GOOGLE_CLIENT_SECRET=your-google-client-secret-here
GOOGLE_REDIRECT_URI=http://localhost:8001/api/v1/auth/google/callback

# Storage Configuration
FILESYSTEM_DISK=local
STORAGE_PUBLIC_DISK=public

# Cache Configuration
CACHE_STORE=file
CACHE_PREFIX=tc_cache

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Queue Configuration
QUEUE_CONNECTION=sync

# Mail Configuration (for notifications)
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@tc-api.local"
MAIL_FROM_NAME="${APP_NAME}"

# Logging
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# Broadcasting (if needed for real-time features)
BROADCAST_CONNECTION=null

# File Upload Limits
UPLOAD_MAX_FILESIZE=2048                # 2MB in KB
UPLOAD_ALLOWED_EXTENSIONS=jpeg,png,jpg,gif
```

### 🔑 Environment Setup Commands

```bash
# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Create storage link for public file access
php artisan storage:link

# Clear various caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🤝 Contributing

1. Fork repository
2. Buat feature branch (`git checkout -b feature/amazing-feature`)
3. Commit perubahan (`git commit -m 'Add amazing feature'`)
4. Push ke branch (`git push origin feature/amazing-feature`)
5. Buat Pull Request

### Code Style

Pastikan untuk menjalankan PHP-CS-Fixer sebelum commit:

```bash
vendor/bin/php-cs-fixer fix
```

## 📄 License

Proyek ini menggunakan lisensi [MIT](https://opensource.org/licenses/MIT).
