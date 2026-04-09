## About Megu-Core

**Megu-Core** adalah framework API kustom berbasis Laravel yang dirancang untuk produktivitas maksimal. Framework ini menggunakan pola *Dynamic CRUD* yang memungkinkan Anda membangun backend hanya dengan mendefinisikan tabel di database. 

Beberapa fitur unggulannya antara lain:
- **Dynamic CRUD Engine**: Satu controller untuk semua model.
- **Automated Model Generator**: Membuat model lengkap dengan validasi dan lifecycle hooks secara otomatis.
- **JWT Stateless Authentication**: Keamanan API modern menggunakan JSON Web Token.
- **Lifecycle Hooks**: Fungsi `before` dan `after` untuk kustomisasi logika bisnis pada setiap proses database.

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
Gunakan perintah otomatis untuk membuat model yang kompatibel dengan Lara-Core.
```bash
php artisan generate:model (nama_table)
```

---

## API Usage

API Endpoint Anda sekarang aktif secara dinamis. Pastikan untuk menggunakan **Bearer Token** pada setiap request yang diproteksi.

### 11. Endpoint Examples
| Method | URL | Deskripsi |
| :--- | :--- | :--- |
| **POST** | `/api/login` | Login untuk mendapatkan Token |
| **GET** | `/api/{name_model}` | Mendapatkan list data (Support pagination & search) |
| **POST** | `/api/{name_model}` | Menambah data baru |
| **PUT** | `/api/{name_model}` | Memperbarui data (Kirim ID di body) |
| **DELETE** | `/api/{name_model}` | Menghapus data (Kirim ID di body) |

---

