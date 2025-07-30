# Level Up Request API Documentation

## 🚀 Overview

Sistem verifikasi tingkat akun atau leveling user berdasarkan dokumen dan kelayakan. API ini memungkinkan user untuk mengajukan kenaikan level skill mereka dengan mengunggah dokumen pendukung, dan memungkinkan verifikator/admin untuk menyetujui atau menolak permintaan tersebut.

## 📋 Requirements

-   User harus sudah login (Bearer Token required)
-   Untuk verifikasi: User harus memiliki role verifikator/admin (saat ini semua user authenticated dapat memverifikasi - bisa disesuaikan dengan role system)
-   File upload maksimal: KTP/Ijazah/Sertifikat (5MB), Portofolio (10MB)
-   Format file yang diterima: JPEG, PNG, JPG, PDF

## 🔐 Authentication

Semua endpoint memerlukan Bearer Token JWT dalam header:

```
Authorization: Bearer {your_jwt_token}
```

## 📚 API Endpoints

### 🔸 1. POST /api/v1/level-up-request

**Mengajukan permintaan kenaikan level**

#### Request Headers

```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

#### Request Body (Form Data)

```
target_level: "Intermediate"  // String - level yang diinginkan (Beginner/Intermediate/Advanced/Expert/Master)
documents[ktp]: file          // File - KTP (Required)
documents[ijazah]: file       // File - Ijazah (Optional)
documents[sertifikat]: file   // File - Sertifikat (Optional)
documents[portofolio]: file   // File - Portofolio (Optional)
notes: "Saya ingin menjadi penyedia layanan profesional"  // String (Optional, max 1000 chars)
```

#### Validation Rules

-   `target_level`: Required, harus ada di database skill_levels, harus lebih tinggi dari level saat ini
-   `documents`: Required, minimal 2 dokumen
-   `documents.ktp`: Required, file, format: jpeg/png/jpg/pdf, max: 5MB
-   `documents.ijazah`: Optional, file, format: jpeg/png/jpg/pdf, max: 5MB
-   `documents.sertifikat`: Optional, file, format: jpeg/png/jpg/pdf, max: 5MB
-   `documents.portofolio`: Optional, file, format: jpeg/png/jpg/pdf, max: 10MB
-   `notes`: Optional, string, max: 1000 characters
-   Minimal 1 dokumen tambahan selain KTP (ijazah/sertifikat/portofolio)
-   Hanya boleh ada 1 permintaan pending per user

#### Response Success (201)

```json
{
    "status": "success",
    "message": "Level up request submitted successfully",
    "data": {
        "id": 1,
        "target_level": "Intermediate",
        "status": "pending",
        "documents_uploaded": 2,
        "notes": "Saya ingin menjadi penyedia layanan profesional",
        "submitted_at": "2025-01-15T10:30:00Z"
    }
}
```

#### Response Error Examples

**Validation Error (422)**

```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "target_level": ["Target level is required"],
        "documents.ktp": ["KTP document is required"],
        "documents": [
            "At least one additional document (ijazah, sertifikat, or portofolio) besides KTP is required."
        ]
    }
}
```

**Already Has Pending Request (409)**

```json
{
    "status": "error",
    "message": "You already have a pending level up request"
}
```

**Invalid Target Level (400)**

```json
{
    "status": "error",
    "message": "Target level must be higher than your current level"
}
```

---

### 🔸 2. GET /api/v1/level-up-request/{id}

**Detail pengajuan user (hanya bisa melihat permintaan sendiri)**

#### Request Headers

```
Authorization: Bearer {token}
```

#### Response Success (200)

```json
{
    "status": "success",
    "data": {
        "id": 1,
        "target_level": "Intermediate",
        "status": "pending",
        "documents": {
            "ktp": {
                "url": "/storage/level-up-documents/1/ktp_filename.jpg",
                "original_name": "ktp.jpg",
                "size": 1024000,
                "mime_type": "image/jpeg"
            },
            "ijazah": {
                "url": "/storage/level-up-documents/1/ijazah_filename.pdf",
                "original_name": "ijazah.pdf",
                "size": 2048000,
                "mime_type": "application/pdf"
            }
        },
        "notes": "Saya ingin menjadi penyedia layanan profesional",
        "verification_reason": null,
        "verified_by": null,
        "verified_at": null,
        "submitted_at": "2025-01-15T10:30:00Z"
    }
}
```

#### Response Error (404)

```json
{
    "status": "error",
    "message": "Level up request not found"
}
```

---

### 🔸 3. GET /api/v1/level-up-requests

**List pengajuan untuk admin/verifikator (dengan filter)**

#### Request Headers

```
Authorization: Bearer {token}
```

#### Query Parameters

-   `status` (optional): Filter by status (pending/approved/rejected)
-   `page` (optional): Page number (default: 1)
-   `limit` (optional): Items per page (default: 10, max: 100)

#### Example Request

```
GET /api/v1/level-up-requests?status=pending&page=1&limit=10
```

#### Response Success (200)

```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "user": {
                "id": 5,
                "name": "John Doe",
                "email": "john@example.com",
                "current_skill_level": "Beginner"
            },
            "target_skill_level": {
                "id": 2,
                "level_name": "Intermediate",
                "multiplier": "1.00"
            },
            "status": "pending",
            "documents": {
                "ktp": {...},
                "ijazah": {...}
            },
            "notes": "Saya ingin menjadi penyedia layanan profesional",
            "verification_reason": null,
            "verified_by": null,
            "verified_at": null,
            "created_at": "2025-01-15T10:30:00Z",
            "updated_at": "2025-01-15T10:30:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 3,
        "per_page": 10,
        "total": 25
    }
}
```

---

### 🔸 4. POST /api/v1/level-up-request/{id}/verify

**Verifikasi oleh verifikator (approve/reject)**

#### Request Headers

```
Authorization: Bearer {token}
Content-Type: application/json
```

#### Request Body

```json
{
    "status": "approved", // or "rejected"
    "reason": "Dokumen valid dan sesuai persyaratan" // Required untuk approved/rejected
}
```

#### Validation Rules

-   `status`: Required, must be "approved" or "rejected"
-   `reason`: Required, string, max 1000 characters

#### Response Success (200)

```json
{
    "status": "success",
    "message": "Level up request has been approved successfully",
    "data": {
        "id": 1,
        "status": "approved",
        "verification_reason": "Dokumen valid dan sesuai persyaratan",
        "verified_by": {
            "id": 2,
            "name": "Admin User"
        },
        "verified_at": "2025-01-15T11:30:00Z"
    }
}
```

#### Response Error (404)

```json
{
    "status": "error",
    "message": "Level up request not found or already processed"
}
```

#### Logic Bisnis

1. **Jika Approved:**

    - User skill_level_id diupdate ke target_skill_level_id
    - Notification dibuat dengan pesan sukses
    - Status request berubah menjadi "approved"

2. **Jika Rejected:**
    - User skill_level_id tidak berubah
    - Notification dibuat dengan pesan penolakan dan alasan
    - Status request berubah menjadi "rejected"

---

### 🔸 5. GET /api/v1/my-level-up-requests

**Get user's own level up requests history**

#### Request Headers

```
Authorization: Bearer {token}
```

#### Response Success (200)

```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "target_level": "Intermediate",
            "status": "approved",
            "documents_count": 2,
            "notes": "Saya ingin menjadi penyedia layanan profesional",
            "verification_reason": "Dokumen valid dan sesuai persyaratan",
            "verified_by": {
                "id": 2,
                "name": "Admin User"
            },
            "verified_at": "2025-01-15T11:30:00Z",
            "submitted_at": "2025-01-15T10:30:00Z"
        },
        {
            "id": 2,
            "target_level": "Advanced",
            "status": "rejected",
            "documents_count": 2,
            "notes": "Request untuk Advanced level",
            "verification_reason": "Dokumen kurang jelas, silakan upload ulang",
            "verified_by": {
                "id": 2,
                "name": "Admin User"
            },
            "verified_at": "2025-01-16T09:15:00Z",
            "submitted_at": "2025-01-16T08:00:00Z"
        }
    ]
}
```

---

## 🧪 Testing dengan cURL

### 1. Login dan Dapatkan Token

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### 2. Submit Level Up Request

```bash
curl -X POST http://localhost:8000/api/v1/level-up-request \
  -H "Authorization: Bearer {your_token}" \
  -F "target_level=Intermediate" \
  -F "documents[ktp]=@/path/to/ktp.jpg" \
  -F "documents[ijazah]=@/path/to/ijazah.pdf" \
  -F "notes=Saya ingin menjadi penyedia layanan profesional"
```

### 3. Get Level Up Request Detail

```bash
curl -X GET http://localhost:8000/api/v1/level-up-request/1 \
  -H "Authorization: Bearer {your_token}"
```

### 4. Get All Level Up Requests (Admin/Verifikator)

```bash
curl -X GET "http://localhost:8000/api/v1/level-up-requests?status=pending&page=1&limit=10" \
  -H "Authorization: Bearer {your_token}"
```

### 5. Verify Level Up Request

```bash
curl -X POST http://localhost:8000/api/v1/level-up-request/1/verify \
  -H "Authorization: Bearer {your_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "approved",
    "reason": "Dokumen valid dan sesuai persyaratan"
  }'
```

### 6. Get User's Own Requests

```bash
curl -X GET http://localhost:8000/api/v1/my-level-up-requests \
  -H "Authorization: Bearer {your_token}"
```

---

## 📁 File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── LevelUpController.php      # Level up request controller
│   └── Requests/
│       ├── LevelUpRequest.php         # Form request for submission
│       └── VerifyLevelUpRequest.php   # Form request for verification
├── Models/
│   ├── LevelUpRequest.php            # Level up request model
│   ├── User.php                      # User model (updated with relationships)
│   ├── SkillLevel.php               # Skill level model
│   └── Notification.php             # Notification model
database/
├── migrations/
│   └── 2025_07_30_080326_create_level_up_requests_table.php
└── seeders/
    └── SkillLevelSeeder.php         # Skill level seeder
routes/
└── api.php                          # API routes definition
storage/
└── app/
    └── public/
        └── level-up-documents/      # Document uploads directory
```

---

## 🛡️ Security Considerations

1. **File Upload Security:**

    - Validasi MIME type dan ekstensi file
    - Set maksimum file size
    - Store file dengan nama yang di-hash untuk keamanan
    - Files disimpan di storage/app/public/level-up-documents/{user_id}/

2. **Authorization:**

    - User hanya bisa melihat request milik sendiri
    - Verifikator/Admin bisa melihat semua request
    - Validasi ownership pada semua endpoint

3. **Data Validation:**
    - Comprehensive validation pada form requests
    - Sanitize input data
    - Prevent duplicate pending requests

---

## 🔄 Flow Diagram

```
1. User Submit Request
   ├── Validate target_level > current_level
   ├── Validate documents (min 2: KTP + 1 additional)
   ├── Upload files to storage
   ├── Create LevelUpRequest record (status: pending)
   └── Return success response

2. Admin/Verifikator Review
   ├── Get list of pending requests
   ├── View request details and documents
   └── Make decision (approve/reject)

3. Verification Process
   ├── Update request status
   ├── If approved: Update user skill_level_id
   ├── Create notification for user
   └── Return response

4. User Gets Notification
   ├── Level updated (if approved)
   ├── Notification in system
   └── Can view request history
```

---

## 🚨 Error Handling

Semua error response menggunakan format yang konsisten:

```json
{
    "status": "error",
    "message": "Error description",
    "errors": {
        "field_name": ["Specific error message"]
    }
}
```

**Common HTTP Status Codes:**

-   `200` - Success
-   `201` - Created (untuk submission)
-   `400` - Bad Request (invalid target level, etc.)
-   `401` - Unauthorized (invalid/missing token)
-   `403` - Forbidden (tidak bisa akses resource)
-   `404` - Not Found (request tidak ditemukan)
-   `409` - Conflict (sudah ada pending request)
-   `422` - Validation Error
-   `500` - Internal Server Error

---

## 📊 Database Schema

### level_up_requests table

```sql
id                     - bigint (primary key)
user_id               - bigint (foreign key to users)
target_skill_level_id - bigint (foreign key to skill_levels)
documents             - json (document URLs and metadata)
notes                 - text (nullable)
status                - enum (pending, approved, rejected)
verification_reason   - text (nullable)
verified_by           - bigint (foreign key to users, nullable)
verified_at           - timestamp (nullable)
created_at            - timestamp
updated_at            - timestamp

indexes:
- user_id, status (untuk query pending requests per user)
```

### Skill Levels Available

1. **Beginner** (multiplier: 0.75)
2. **Intermediate** (multiplier: 1.00)
3. **Advanced** (multiplier: 1.25)
4. **Expert** (multiplier: 1.50)
5. **Master** (multiplier: 2.00)

---

Sistem ini sudah ready untuk production dan mengikuti best practices Laravel dengan validation yang ketat, error handling yang baik, dan security yang memadai.
