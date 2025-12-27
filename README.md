# Kosify API (Backend)

Backend untuk aplikasi manajemen kos (SaaS) berbasis **Laravel 12**. Proyek ini dirancang dengan arsitektur API-First, mendukung performa tinggi dengan caching, indexing database, dan optimasi gambar otomatis.

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

## 📦 Cara Install & Menjalankan

1.  **Clone Repository**
    ```bash
    git clone <repository-url>
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
    Sesuaikan konfigurasi database:
    ```env
    DB_DATABASE=kosify
    DB_USERNAME=root
    DB_PASSWORD=
    
    APP_URL=http://localhost:8000
    FILESYSTEM_DISK=public
    ```

4.  **Generate Key & Storage Link**
    Wajib dijalankan agar gambar bisa diakses publik.
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

## 🔑 Akun Demo (Seeder)

*   **Super Admin:** `admin@kosify.com` / `password`
*   **Owner:** `kos1@gmail.com` s/d `kos20@gmail.com` / `kos12345`

## 📝 Dokumentasi API

File koleksi Postman tersedia (lihat file `Kosify_Full_API.json` jika ada).
Base URL: `http://localhost:8000/api`

## ⚠️ Status Pengembangan

Proyek ini masih dalam tahap pengembangan aktif (Work in Progress).
