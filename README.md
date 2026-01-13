# Kosify API (Backend)

Backend untuk aplikasi manajemen kos (SaaS) berbasis **Laravel 12**. Proyek ini dirancang dengan arsitektur API-First yang melayani data untuk Frontend Vue.js.

## 🔗 Repository Terkait
Proyek ini adalah bagian Backend (API). Untuk antarmuka pengguna (Frontend), silakan kunjungi:
👉 **[Kosify Web (Frontend)](https://github.com/VicoTriansyahNasril/kosify-web)**

---

## 🚀 Fitur Utama & Teknologi

*   **Framework:** Laravel 12
*   **Database:** MySQL (Menggunakan **ULID** sebagai Primary Key)
*   **Authentication:** Laravel Sanctum (SPA Authentication)
*   **Performance:**
    *   **Database Indexing:** Optimasi query pencarian (Fulltext & B-Tree).
    *   **Caching:** Implementasi Cache pada endpoint publik (Redis/File driver).
    *   **Eager Loading:** Mencegah N+1 Query problem.
*   **Media Handling:**
    *   **Intervention Image:** Auto-resize & konversi otomatis ke format **WEBP** untuk performa ringan.
*   **Architecture:** Service Pattern & Skinny Controllers.

## 🛠️ Prasyarat

*   PHP >= 8.2
*   Composer
*   MySQL / MariaDB
*   Redis (Opsional, default menggunakan `file` cache)

## 📦 Cara Install & Menjalankan

Ikuti langkah ini untuk menjalankan server API agar bisa diakses oleh Frontend.

1.  **Clone Repository**
    ```bash
    git clone https://github.com/VicoTriansyahNasril/kosify-api.git
    cd kosify-api
    ```

2.  **Install Dependencies**
    ```bash
    composer install
    ```

3.  **Konfigurasi Environment**
    Salin file `.env.example` menjadi `.env`:
    ```bash
    cp .env.example .env
    ```
    Sesuaikan konfigurasi database dan URL Frontend (untuk CORS/Sanctum):
    ```env
    DB_DATABASE=kosify
    DB_USERNAME=root
    DB_PASSWORD=

    # URL Backend (Diri sendiri)
    APP_URL=http://localhost:8000
    
    # URL Frontend (Untuk izin akses CORS & Cookies)
    APP_FRONTEND_URL=http://localhost:5173
    SANCTUM_STATEFUL_DOMAINS=localhost:5173
    
    FILESYSTEM_DISK=public
    ```

4.  **Generate Key & Storage Link**
    **PENTING:** Wajib dijalankan agar gambar bisa diakses publik oleh frontend.
    ```bash
    php artisan key:generate
    php artisan storage:link
    ```

5.  **Migrasi & Seeding Data**
    Ini akan membuat struktur tabel dan mengisi data dummy (20 Owner, ~1000 Kamar, ~1500 Transaksi) untuk tes performa.
    ```bash
    php artisan migrate:fresh --seed
    ```

6.  **Jalankan Server**
    ```bash
    php artisan serve
    ```
    Server akan berjalan di `http://localhost:8000`.

## 🔑 Akun Demo (Seeder)

Gunakan akun ini untuk login di Frontend:

*   **Super Admin:** `admin@kosify.com` / `password`
*   **Owner (Pemilik Kos):** `kos1@gmail.com` s/d `kos20@gmail.com` / `kos12345`

## 📝 Dokumentasi API

File koleksi Postman tersedia di dalam repo ini (lihat file `Kosify_Full_API.json` jika ada) untuk pengujian endpoint secara manual.

## ⚠️ Catatan Integrasi

*   Pastikan server backend ini **SELALU BERJALAN** (`php artisan serve`) sebelum Anda menjalankan frontend.
*   Jika frontend mengalami error gambar (404), pastikan Anda sudah menjalankan `php artisan storage:link`.
