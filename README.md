# file-management-system

Sistem manajemen dokumen berbasis web yang dirancang untuk mengorganisasi folder, berkas (*files*), serta manajemen hak akses berdasarkan departemen dan *role* pengguna (Administrator & Viewer).

## Fitur Utama
- **Autentikasi Aman:** Menggunakan **Laravel Sanctum** untuk pengelolaan token API yang aman.
- **Manajemen Department:** 
  - *Administrator* dapat menambah, mengubah, dan menghapus data departemen.
  - Penanganan integritas data menggunakan `firstOrCreate` pada seeder.
- **Manajemen Folder & File:** 
  - Unggah, unduh, perbarui informasi, dan hapus berkas.
  - Navigasi antarmuka yang ramah pengguna dengan tombol kembali (*Back Button*) ke Dashboard.
- **Manajemen Hak Akses (Authorization):** 
  - Pembatasan fitur berdasarkan *role* (Administrator dan Viewer) untuk memastikan keamanan data sistem.

## Tech Stack
- **Backend:** Laravel (PHP), PostgreSQL / MySQL, Laravel Sanctum
- **Frontend:** Vue.js 3 (Composition API, `<script setup>`), Tailwind CSS, Vue Router, Pinia
- **Version Control:** Git & GitHub

## Konfigurasi Environment
Sebelum menjalankan aplikasi, pastikan Anda telah mengatur file `.env` pada bagian backend:
- Konfigurasi koneksi database (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
- Konfigurasi penyimpanan publik (`FILESYSTEM_DISK=public`).

## Instalasi & Menjalankan Proyek

Proyek ini terbagi menjadi dua bagian: **Backend (Laravel)** dan **Frontend (Vue.js)**. Anda memerlukan **dua terminal (tab) terpisah** untuk menjalankan keduanya secara bersamaan.

### 1. Jalankan Backend (Terminal 1)
Masuk ke direktori backend, lalu jalankan perintah berikut:

```
# Install dependencies PHP
composer install

# Salin file konfigurasi lingkungan
cp .env.example .env

# Generate application key
php artisan key:generate

# Jalankan migrasi database dan seeder
php artisan migrate --seed

# Hubungkan storage publik
php artisan storage:link

# Jalankan server lokal Laravel
php artisan serve
```

### 2. Jalankan Frontend (Terminal 2)
Buka tab terminal baru, masuk ke direktori frontend, lalu jalankan perintah berikut:

```
# Install dependencies Node.js
npm install

# Jalankan development server Vue.js
npm run dev
```

## API Endpoints Reference

Base URL Backend: `http://localhost:8000/api`
Semua rute yang dilindungi (selain login) memerlukan header otentikasi Bearer Token dari Laravel Sanctum (`Authorization: Bearer <token>`).

### 1. Autentikasi
* `POST /login` - Masuk ke sistem dan mendapatkan token API.
* `POST /logout` - Menghapus token aktif (Membutuhkan autentikasi).
* `GET /user` - Mengambil data profil pengguna yang sedang login beserta relasi departemennya.

### 2. Manajemen Department (Administrator Only)
* `GET /departments` - Mengambil daftar seluruh departemen.
* `POST /departments` - Menambah departemen baru.
* `PUT /departments/{id}` - Memperbarui data departemen.
* `DELETE /departments/{id}` - Menghapus departemen.

### 3. Manajemen Folder
* `GET /folders` - Mengambil daftar seluruh folder.
* `POST /folders` - Membuat folder baru.
* `GET /folders/{id}` - Menampilkan detail folder beserta file di dalamnya.
* `DELETE /folders/{id}` - Menghapus folder.

### 4. Manajemen File
* `GET /files` - Mengambil daftar seluruh file (mendukung parameter pencarian dan filter).
* `POST /files` - Mengunggah file baru (*multipart/form-data*).
* `PUT /files/{id}` - Memperbarui informasi/judul file.
* `GET /files/{id}/download` - Mengunduh file fisik dari server.
* `DELETE /files/{id}` - Menghapus file dari database dan penyimpanan.

### 5. Dashboard
* `GET /dashboard` - Mengambil data ringkasan statistik (total folder, file, departemen) dan daftar file terbaru.

## Akun Login (Default Credentials)

### Administrator
- **Administrator:** Memiliki akses penuh ke manajemen departemen, folder, dan file.
- **Email:** admin@filemanagement.com
- **Password:** password123

### Viewer
- **Viewer:** Memiliki akses terbatas untuk melihat data tanpa hak modifikasi/penghapusan penuh.
- **Email:** viewer@example.com
- **Password:** password123
