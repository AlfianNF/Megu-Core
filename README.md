
---

# Megu-Core API Framework

## About Megu-Core

**Megu-Core** adalah framework API kustom berbasis Laravel yang dirancang untuk produktivitas maksimal. Framework ini menggunakan pola *Dynamic CRUD* yang memungkinkan Anda membangun backend hanya dengan mendefinisikan tabel di database tanpa perlu menulis logika CRUD berulang kali.

Beberapa fitur unggulannya antara lain:
- **Dynamic CRUD Engine**: Satu controller untuk menangani semua model secara dinamis.
- **Automated Model Generator**: Membuat model lengkap dengan validasi dan lifecycle hooks secara otomatis.
- **JWT Stateless Authentication**: Keamanan API modern menggunakan JSON Web Token.
- **Dynamic Filtering**: Mendukung filter pencarian global dan filter range (misal: `price_min` & `price_max`) secara otomatis.
- **Auto-Swagger Documentation**: Dokumentasi API yang selalu sinkron dengan struktur Model Anda.
- **Dynamic Export**: Mengubah hasil query API menjadi file Excel secara instan.

---

## Getting Started (Installation)

Ikuti langkah-langkah di bawah ini untuk menjalankan project di lingkungan lokal:

### 1. Clone Repositori
```bash
git clone https://github.com/AlfianNF/Megu-Core
cd Megu-Core
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Setup Environment
Salin file `.env.example`, buat file `.env` baru, lalu generate app key.
```bash
cp .env.example .env
php artisan key:generate
```
*Sesuaikan konfigurasi database Anda (DB_DATABASE, DB_USERNAME, dll) di file `.env`.*

### 4. Database Migration
Jalankan migrasi dasar untuk menyiapkan tabel sistem dan user Super Admin.
```bash
php artisan migrate
```

---

## Authentication Setup (JWT)

Megu-Core menggunakan **php-open-source-saver/jwt-auth** untuk menangani otentikasi.

### 5. Install JWT Package
```bash
composer require php-open-source-saver/jwt-auth
```

### 6. Publish JWT Provider
```bash
php artisan vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider"
```

### 7. Generate JWT Secret Key
```bash
php artisan jwt:secret
```

---

## Development Workflow (Magic CRUD)

Setelah setup selesai, Anda bisa menambah fitur baru dengan alur kerja berikut:

### 8. Create Migration
Buat tabel baru sesuai kebutuhan Anda.
```bash
php artisan make:migration create_items_table
```

### 9. Execute Migration
```bash
php artisan migrate
```

### 10. Generate Model
Gunakan perintah otomatis untuk membuat model yang kompatibel dengan Megu-Core.
```bash
php artisan generate:model (nama_table)
```

---

## Documentation (Auto-Swagger)

Megu-Core menghilangkan beban penulisan dokumentasi API secara manual. Dengan satu perintah, framework akan membaca Model Anda dan menghasilkan file OpenAPI (Swagger).

### 11. Generate Swagger Documentation
Jalankan command ini setiap kali Anda menambah model baru atau mengubah konstanta di Model:
```bash
php artisan core:swagger
```

### 12. Accessing the UI
Setelah digenerate, akses dokumentasi interaktif melalui browser:
- **URL**: `http://your-domain.test/api/documentation`
- **Fitur**: Mendukung *Try it out*, Skema Request Body otomatis, dan Authorize JWT.

---

## Features (Reporting & Filtering)

### 13. Dynamic Export Excel
Megu-Core mendukung export data ke Excel secara dinamis untuk semua model. Cukup tambahkan suffix `/export` pada endpoint model Anda.
- **Contoh**: `GET /api/products/export`
- **Filter Support**: Data yang di-export akan mengikuti filter yang Anda pasang di URL (misal: `?price_min=1000`).

### 14. Advanced Range Filtering
Filter range otomatis tersedia untuk semua kolom numerik dan tanggal menggunakan suffix `_min` dan `_max`.
- **Contoh URL**: `/api/products?price_min=5000&price_max=20000`

---

## API Usage Reference

API Endpoint Anda sekarang aktif secara dinamis. Pastikan untuk menggunakan **Bearer Token** pada setiap request yang diproteksi.

### 15. Endpoint List
| Method | URL | Deskripsi |
| :--- | :--- | :--- |
| **POST** | `/api/login` | Login untuk mendapatkan Token |
| **GET** | `/api/{model}` | Get list data (Pagination, Search, & Filter) |
| **GET** | `/api/{model}/{id}` | Mendapatkan detail data berdasarkan ID |
| **POST** | `/api/{model}` | Menambah data baru (Sesuai `FIELD_ADD`) |
| **PUT** | `/api/{model}` | Memperbarui data (Sesuai `FIELD_EDIT`) |
| **DELETE** | `/api/{model}` | Menghapus data (Kirim ID di body) |
| **GET** | `/api/{model}/export` | Export data ke Excel (xlsx) |

---
