<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MIDDLEWARE UNTUK AKSES BERDASARKAN ROLE
 * 
 * Middleware ini mengatur akses ke route berdasarkan role user (admin atau mhs)
 * Middleware ini akan:
 * 1. Mengecek apakah user sudah login
 * 2. Mengecek apakah role user sesuai dengan role yang diizinkan
 * 
 * Role yang tersedia:
 * - 'admin': Administrator dengan akses penuh
 * - 'mhs': Mahasiswa biasa dengan akses terbatas
 */
class MahasiswaMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * Method ini dipanggil setiap kali ada request yang menggunakan middleware ini
     * 
     * @param  \Illuminate\Http\Request  $request Request yang masuk
     * @param  \Closure  $next Closure untuk melanjutkan request ke controller
     * @param  string  ...$role Role yang diizinkan (bisa lebih dari satu: 'admin', 'mhs')
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$role): Response
    {
        // ============================================
        // BAGIAN 1: CEK APAKAH USER SUDAH LOGIN
        // ============================================
        // Auth::guard('mahasiswas') menggunakan guard 'mahasiswas' untuk autentikasi
        // Guard 'mahasiswas' dikonfigurasi di config/auth.php
        // check() mengembalikan true jika user sudah login, false jika belum
        if (!Auth::guard('mahasiswas')->check()) {
            // Jika user belum login, redirect ke halaman login dengan pesan error
            return redirect()->route('viewlogin')
                ->with('error', 'Anda harus login terlebih dahulu.');
        }
        
        // ============================================
        // BAGIAN 2: AMBIL DATA USER YANG SEDANG LOGIN
        // ============================================
        // Ambil data user yang sedang login dari guard 'mahasiswas'
        // Data user ini berisi informasi dari tabel 'mahasiswas' termasuk kolom 'role'
        $user = Auth::guard('mahasiswas')->user();
        
        // ============================================
        // BAGIAN 3: VALIDASI ROLE USER
        // ============================================
        // Cek apakah role user (admin atau mhs) ada dalam array role yang diizinkan
        // in_array() mengecek apakah nilai $user->role ada dalam array $role
        // $role adalah parameter yang diterima dari route (contoh: AuthMahasiswa:admin,mhs)
        // 
        // Contoh penggunaan:
        // - Route::middleware('AuthMahasiswa:admin') -> hanya admin yang bisa akses
        // - Route::middleware('AuthMahasiswa:mhs,admin') -> admin dan mhs bisa akses
        // - Route::middleware('AuthMahasiswa:mhs') -> hanya mhs yang bisa akses
        if (!in_array($user->role, $role)) {
            // Jika role user tidak sesuai dengan role yang diizinkan
            // Abort dengan HTTP status code 403 (Forbidden)
            // User akan melihat halaman error 403
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');   
        }
        
        // ============================================
        // BAGIAN 4: LANJUTKAN REQUEST
        // ============================================
        // Jika semua validasi berhasil (user sudah login dan role sesuai)
        // Lanjutkan request ke controller dengan memanggil $next($request)
        return $next($request);
    }
}
