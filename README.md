# 📚 Sistem Informasi Perpustakaan - UAS PBO

Aplikasi manajemen perpustakaan berbasis web yang dibangun menggunakan Laravel. Sistem ini dirancang untuk mengelola peminjaman dan pengembalian buku di perpustakaan kampus dengan fitur role-based access control (Admin & Mahasiswa).

## 🎯 Fitur Utama

### 👨‍💼 Fitur Admin
- **Manajemen Mahasiswa**: CRUD data mahasiswa (Tambah, Lihat, Edit, Hapus)
- **Manajemen Buku**: CRUD data buku dengan pencatatan stok
- **Manajemen Program Studi**: CRUD data program studi
- **Peminjaman Buku**: 
  - Input peminjaman dengan autocomplete mahasiswa & buku
  - Validasi stok buku otomatis
  - Pencatatan tanggal pinjam dan tenggat kembali
- **Pengembalian Buku**: 
  - Pencarian peminjaman berdasarkan NIM
  - Proses pengembalian dengan update stok otomatis
  - Deteksi keterlambatan pengembalian
- **Laporan Pengembalian**: Statistik dan riwayat pengembalian lengkap

### 👨‍🎓 Fitur Mahasiswa
- **Lihat Katalog Buku**: Melihat daftar buku yang tersedia
- **Riwayat Peminjaman**: Melihat riwayat peminjaman pribadi
- **Status Keterlambatan**: Notifikasi buku yang terlambat dikembalikan
- **Validasi Tanggungan**: Sistem mencegah peminjaman jika masih ada buku telat

## 🛠️ Teknologi yang Digunakan

- **Framework**: Laravel 10.x
- **Database**: MySQL
- **Frontend**: Bootstrap 5, jQuery, jQuery UI (Autocomplete)
- **Backend**: PHP 8.x
- **Authentication**: Custom Guard untuk mahasiswa
- **Date Management**: Carbon

## 📋 Persyaratan Sistem

- PHP >= 8.1
- Composer
- MySQL/MariaDB
- Node.js & NPM (untuk asset compilation)
- Web Server (Apache/Nginx)

## 🚀 Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/sabita-yahya/UAS-PBO-Perpustakaan.git
cd UAS-PBO-Perpustakaan
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Konfigurasi Environment
```bash
# Copy file .env.example menjadi .env
copy .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Konfigurasi Database
Edit file `.env` dan sesuaikan dengan konfigurasi database Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=perpustakaan
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Migrasi Database
```bash
# Jalankan migrasi
php artisan migrate

# (Optional) Jalankan seeder jika ada
php artisan db:seed
```

### 6. Compile Assets
```bash
npm run dev
# Atau untuk production
npm run build
```

### 7. Jalankan Aplikasi
```bash
php artisan serve
```

Akses aplikasi di: `http://localhost:8000`

## 📊 Struktur Database

### Tabel Utama
- **mahasiswas**: Data mahasiswa (NIM, nama, prodi, role)
- **bukus**: Data buku (judul, pengarang, penerbit, tahun, stok)
- **prodis**: Data program studi
- **pinjams_tabel**: Data peminjaman (NIM, tanggal pinjam/kembali)
- **detail_pinjams_tabel**: Detail buku yang dipinjam (kode buku, jumlah, status)

## 🔐 Autentikasi & Role

### Role yang Tersedia:
1. **Admin** (`role = 'admin'`)
   - Akses penuh ke semua fitur
   - Mengelola data mahasiswa, buku, dan prodi
   - Memproses peminjaman dan pengembalian

2. **Mahasiswa** (`role = 'mhs'`)
   - Melihat katalog buku
   - Melihat riwayat peminjaman pribadi
   - Terbatas dari fitur CRUD

### Middleware
- `AuthMhs:admin` - Hanya untuk admin
- `AuthMhs:mhs,admin` - Untuk mahasiswa dan admin

## 📁 Struktur Project

```
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── BukuController.php        # CRUD Buku
│   │   │   ├── CobaController.php        # Auth & CRUD Mahasiswa
│   │   │   ├── PinjamController.php      # Peminjaman & Pengembalian
│   │   │   ├── ProdiController.php       # CRUD Prodi
│   │   └── Middleware/
│   ├── Models/
│   │   ├── Buku.php
│   │   ├── mahasiswas.php
│   │   ├── Pinjam.php
│   │   ├── DetailPinjam.php
│   │   └── Prodi.php
├── database/
│   └── migrations/
│       ├── create_mahasiswas_table.php
│       ├── create_prodis_table.php
│       ├── create_bukus_table.php
│       └── create_pinjams_table.php
├── resources/
│   └── views/
│       ├── buku/          # Views CRUD Buku
│       ├── mahasiswa/     # Views CRUD Mahasiswa
│       ├── pinjam/        # Views Peminjaman & Riwayat
│       ├── kembali/       # Views Pengembalian
│       └── login/         # Views Login
└── routes/
    └── web.php            # Routing aplikasi
```

## 🎨 Fitur Khusus

### 1. Autocomplete Search
- Pencarian mahasiswa berdasarkan nama (AJAX)
- Pencarian buku berdasarkan judul (AJAX)
- Menampilkan stok buku real-time

### 2. Validasi Peminjaman
- Cek stok buku sebelum peminjaman
- Validasi tanggungan keterlambatan
- Mahasiswa dengan buku telat tidak bisa pinjam lagi
- Admin tidak terkena validasi tanggungan

### 3. Deteksi Keterlambatan
- Otomatis mendeteksi buku yang lewat jatuh tempo
- Menampilkan status "TELAT" dengan warna merah
- Statistik buku telat per mahasiswa

### 4. Transaction Management
- Menggunakan database transaction untuk keamanan data
- Rollback otomatis jika terjadi error
- Update stok buku secara atomik

## 📝 Cara Penggunaan

### Login
1. Akses halaman login
2. Masukkan NIM dan password
3. Sistem akan redirect sesuai role:
   - Admin → Dashboard Admin
   - Mahasiswa → Katalog Buku

### Peminjaman Buku (Admin)
1. Pilih menu "Peminjaman"
2. Cari mahasiswa dengan autocomplete
3. Tambah buku yang akan dipinjam
4. Sistem akan validasi:
   - Stok buku tersedia
   - Mahasiswa tidak punya tanggungan telat
5. Klik "Simpan"

### Pengembalian Buku (Admin)
1. Pilih menu "Pengembalian"
2. Masukkan NIM mahasiswa
3. Sistem akan menampilkan buku yang dipinjam
4. Pilih buku yang dikembalikan
5. Stok buku otomatis bertambah

### Riwayat Peminjaman (Mahasiswa)
1. Login sebagai mahasiswa
2. Pilih menu "Riwayat Peminjaman"
3. Lihat semua buku yang pernah dipinjam
4. Status buku telat akan ditampilkan

## 🐛 Troubleshooting

### Error "Class not found"
```bash
composer dump-autoload
```

### Error Migrasi Database
```bash
php artisan migrate:fresh
```

### Cache Clear
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## 👥 Kontributor

- **Sabita Yahya** - Developer

## 📄 License

Project ini dibuat untuk keperluan UAS Pemrograman Berbasis Objek.

## 🙏 Acknowledgments

- Laravel Framework
- Bootstrap Team
- jQuery & jQuery UI
- Carbon Date Library

---

**Dibuat dengan ❤️ untuk UAS Pemrograman Berbasis Objek - Semester 3 AKN**
