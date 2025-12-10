<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CobaController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\ProdielController;
use App\Http\Controllers\PinjamController;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

// ============================================
// ROUTE LOGIN (TANPA MIDDLEWARE)
// ============================================
// Route login TIDAK boleh menggunakan middleware AuthMahasiswa
// karena user belum login saat mengakses halaman login
// Jika menggunakan middleware, akan terjadi redirect loop
Route::get('/login', [CobaController::class, 'viewlogin'])->name('viewlogin');
Route::post('/login/simpan', [CobaController::class, 'login'])->name('login');
Route::get('/logout', [CobaController::class, 'logout'])->name('logout');

// ============================================
// ROUTE GROUP: AKSES UNTUK ADMIN DAN MAHASISWA
// ============================================
// Middleware 'AuthMahasiswa:mhs,admin' artinya:
// - User harus sudah login (dicek oleh middleware)
// - Role user harus 'mhs' ATAU 'admin' (keduanya boleh akses)
// 
// Route di dalam group ini bisa diakses oleh:
// ✓ Admin (role = 'admin')
// ✓ Mahasiswa (role = 'mhs')
// ✗ User yang belum login akan di-redirect ke halaman login
// ✗ User dengan role lain akan mendapat error 403
Route::middleware('AuthMhs:mhs,admin')->group(function () {

    // ============================================
    // ROUTE AKSES BERSAMA (ADMIN & MAHASISWA)
    // ============================================
    // Route di bawah ini bisa diakses oleh admin dan mahasiswa
    // Admin dan mahasiswa memiliki akses yang sama untuk route ini
    
    // Route untuk melihat data mahasiswa
    // Admin: bisa lihat semua data mahasiswa
    // Mahasiswa: hanya bisa lihat data sendiri (dikontrol di controller)
    Route::get('/mhs/show', [\App\Http\Controllers\CobaController::class, 'index']);
    
    // Route untuk melihat daftar buku
    // Baik admin maupun mahasiswa bisa melihat daftar buku
    Route::get('/bk/show', [\App\Http\Controllers\BukuController::class, 'index'])->name('bk.index');
    
    // Route untuk melihat riwayat peminjaman
    // Admin: bisa lihat semua riwayat (jika diimplementasikan)
    // Mahasiswa: hanya bisa lihat riwayat sendiri
    Route::get('/pinjam/riwayat', [\App\Http\Controllers\PinjamController::class, 'riwayat'])->name('pinjam.riwayat');

    // ============================================
    // ROUTE GROUP: AKSES KHUSUS ADMIN SAJA
    // ============================================
    // Middleware 'AuthMahasiswa:admin' artinya:
    // - User harus sudah login
    // - Role user HARUS 'admin' (hanya admin yang boleh akses)
    // 
    // Route di dalam group ini HANYA bisa diakses oleh:
    // ✓ Admin (role = 'admin')
    // ✗ Mahasiswa (role = 'mhs') akan mendapat error 403
    // ✗ User yang belum login akan di-redirect ke halaman login
    Route::middleware(['AuthMhs:admin'])->group(function () {
        
        // ============================================
        // ROUTE CRUD MAHASISWA (HANYA ADMIN)
        // ============================================
        // Route untuk mengelola data mahasiswa
        // Hanya admin yang bisa menambah, edit, dan hapus data mahasiswa
        Route::get('/mhs/baru', [\App\Http\Controllers\CobaController::class, 'tambah']);      // Form tambah mahasiswa
        Route::post('/mhs/simpan', [\App\Http\Controllers\CobaController::class, 'simpan']);     // Simpan data mahasiswa baru
        Route::get('/mhs/edit/{id}', [\App\Http\Controllers\CobaController::class, 'edit']);    // Form edit mahasiswa
        Route::post('/mhs/update/{id}', [\App\Http\Controllers\CobaController::class, 'update']); // Update data mahasiswa
        Route::get('/mhs/hapus/{id}', [\App\Http\Controllers\CobaController::class, 'hapus']);  // Hapus data mahasiswa

        // ============================================
        // ROUTE CRUD BUKU (HANYA ADMIN)
        // ============================================
        // Route untuk mengelola data buku
        // Hanya admin yang bisa menambah, edit, dan hapus data buku
        Route::get('/bk/baru', [\App\Http\Controllers\BukuController::class, 'tambah']);      // Form tambah buku
        Route::post('/bk/simpan', [\App\Http\Controllers\BukuController::class, 'simpan']);  // Simpan data buku baru
        Route::get('/bk/edit/{id}', [\App\Http\Controllers\BukuController::class, 'edit']); // Form edit buku
        Route::post('/bk/update/{id}', [\App\Http\Controllers\BukuController::class, 'update']); // Update data buku
        Route::get('/bk/hapus/{id}', [\App\Http\Controllers\BukuController::class, 'hapus']); // Hapus data buku

        // ============================================
        // ROUTE CRUD PRODI (HANYA ADMIN)
        // ============================================
        // Route untuk mengelola data program studi
        // Hanya admin yang bisa menambah, edit, dan hapus data prodi
        Route::get('/prd/show', [\App\Http\Controllers\ProdiController::class, 'index']);      // Daftar prodi
        Route::get('/prd/baru', [\App\Http\Controllers\ProdiController::class, 'tambah']);      // Form tambah prodi
        Route::post('/prd/simpan', [\App\Http\Controllers\ProdiController::class, 'simpan']);  // Simpan data prodi baru
        Route::get('/prd/edit/{id}', [\App\Http\Controllers\ProdiController::class, 'edit']);  // Form edit prodi
        Route::post('/prd/update/{id}', [\App\Http\Controllers\ProdiController::class, 'update']); // Update data prodi
        Route::get('/prd/hapus/{id}', [\App\Http\Controllers\ProdiController::class, 'hapus']); // Hapus data prodi

        // ============================================
        // ROUTE CRUD PRODIEL (HANYA ADMIN)
        // ============================================
        // Route alternatif untuk mengelola data program studi
        Route::get('/prodi', [\App\Http\Controllers\ProdielController::class, 'index']);      // Daftar prodi
        Route::get('/prodi/baru', [\App\Http\Controllers\ProdielController::class, 'tambah']); // Form tambah prodi
        Route::post('/prodi/simpan', [\App\Http\Controllers\ProdielController::class, 'simpan']); // Simpan data prodi baru
        Route::get('/prodi/edit/{id}', [\App\Http\Controllers\ProdielController::class, 'edit']); // Form edit prodi
        Route::post('/prodi/update/{id}', [\App\Http\Controllers\ProdielController::class, 'update']); // Update data prodi
        Route::delete('/prodi/hapus/{id}', [\App\Http\Controllers\ProdielController::class, 'hapus']); // Hapus data prodi

        // ============================================
        // ROUTE PEMINJAMAN BUKU (HANYA ADMIN)
        // ============================================
        // Route untuk mengelola peminjaman buku
        // Hanya admin yang bisa melakukan peminjaman untuk mahasiswa
        // Admin bertindak sebagai petugas perpustakaan
        Route::get('/pinjam', [\App\Http\Controllers\PinjamController::class, 'index']); // Daftar peminjaman
        Route::get('/autocomplete-mahasiswa', [\App\Http\Controllers\PinjamController::class, 'autocompleteMahasiswa'])->name('autocomplete.mahasiswa'); // Autocomplete untuk mencari mahasiswa
        Route::get('/autocomplete-buku', [\App\Http\Controllers\PinjamController::class, 'autocompleteBuku'])->name('autocomplete.buku'); // Autocomplete untuk mencari buku
        Route::post('/pinjam/simpan', [\App\Http\Controllers\PinjamController::class, 'simpan']); // Simpan data peminjaman

        // ============================================
        // ROUTE PENGEMBALIAN BUKU (HANYA ADMIN)
        // ============================================
        // Route untuk mengelola pengembalian buku
        // Hanya admin yang bisa memproses pengembalian buku
        Route::get('/kembali', [\App\Http\Controllers\PinjamController::class, 'daftarPengembalian'])->name('kembali'); // Daftar pengembalian
        Route::get('/kembali/get-by-nim', [\App\Http\Controllers\PinjamController::class, 'getPeminjamanByNim'])->name('kembali.getByNim'); // Get data peminjaman berdasarkan NIM (AJAX)
        Route::post('/kembali/proses', [\App\Http\Controllers\PinjamController::class, 'prosesPengembalian'])->name('kembali.proses'); // Proses pengembalian buku
        
        // ============================================
        // ROUTE LAPORAN (HANYA ADMIN)
        // ============================================
        // Route untuk melihat laporan pengembalian
        // Hanya admin yang bisa melihat laporan
        Route::get('/laporan/pengembalian', [\App\Http\Controllers\PinjamController::class, 'laporanPengembalian'])->name('laporan.pengembalian');
    });
});
