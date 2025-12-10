<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CobaController extends Controller
{
    /**
     * Method index - Menampilkan data mahasiswa
     * 
     * Method ini mengatur akses berdasarkan role:
     * - Admin: bisa melihat semua data mahasiswa
     * - Mahasiswa (mhs): hanya bisa melihat data sendiri
     * 
     * Route ini menggunakan middleware 'AuthMahasiswa:mhs,admin'
     * yang berarti admin dan mahasiswa bisa akses, tapi dengan data yang berbeda
     */
    public function index()
    {
        // ============================================
        // BAGIAN 1: AMBIL DATA USER YANG SEDANG LOGIN
        // ============================================
        // $cekRole berisi data user yang sedang login dari tabel 'mahasiswas'
        // Data ini termasuk kolom 'role' yang menentukan akses user
        // Auth::guard('mahasiswas') menggunakan guard khusus untuk autentikasi mahasiswa
        $cekRole = Auth::guard('mahasiswas')->user();

        // ============================================
        // BAGIAN 2: PEMBAGIAN AKSES BERDASARKAN ROLE
        // ============================================
        
        // CEK ROLE ADMIN
        // Jika role user adalah 'admin', tampilkan semua data mahasiswa
        // Admin memiliki akses penuh untuk melihat semua data
        if ($cekRole && $cekRole->role == 'admin') {
            // Query untuk mengambil semua data mahasiswa beserta data prodi
            // join dengan tabel 'prodis' untuk mendapatkan nama prodi
            $mahasiswa = DB::table('mahasiswas')
                ->join('prodis', 'mahasiswas.prodi_id', '=', 'prodis.kode_prodi')
                ->select(
                    'mahasiswas.*',
                    'prodis.nama_prodi',
                    DB::raw('mahasiswas.tanggal_lahir as tgl_lahir'),
                    DB::raw('mahasiswas.tahun_masuk as th_masuk'),
                    DB::raw("CASE WHEN mahasiswas.tipe_role = 'admin_perpustakaan' THEN 'admin' WHEN mahasiswas.tipe_role = 'mahasiswa' THEN 'mhs' ELSE 'mhs' END as role")
                )
                ->get(); // Ambil semua data tanpa filter
            
            // Kirim data ke view dengan parameter:
            // - 'mhs': data mahasiswa (semua mahasiswa untuk admin)
            // - 'cekRole': data user yang sedang login (untuk menampilkan info di view)
            return view('mahasiswa.index_mhs', ['mhs' => $mahasiswa, 'cekRole' => $cekRole]);

        } 
        // CEK ROLE MAHASISWA (mhs)
        // Jika role user adalah 'mhs', hanya tampilkan data mahasiswa yang login
        // Mahasiswa hanya bisa melihat data dirinya sendiri (berdasarkan NIM)
        else {
            // Ambil NIM dari user yang sedang login
            // NIM digunakan untuk filter data, sehingga mahasiswa hanya melihat data sendiri
            $ceknim = $cekRole ? $cekRole->nim : null;
            
            // Query untuk mengambil data mahasiswa yang login saja
            // Filter berdasarkan NIM yang sedang login
            $mahasiswa = DB::table('mahasiswas')
                ->join('prodis', 'mahasiswas.prodi_id', '=', 'prodis.kode_prodi')
                ->select(
                    'mahasiswas.*',
                    'prodis.nama_prodi',
                    DB::raw('mahasiswas.tanggal_lahir as tgl_lahir'),
                    DB::raw('mahasiswas.tahun_masuk as th_masuk'),
                    DB::raw("CASE WHEN mahasiswas.tipe_role = 'admin_perpustakaan' THEN 'admin' WHEN mahasiswas.tipe_role = 'mahasiswa' THEN 'mhs' ELSE 'mhs' END as role")
                )
                ->where('nim', '=', $ceknim) // Filter: hanya data dengan NIM yang sama dengan user login
                ->get(); // Ambil data yang sudah difilter
            
            // Kirim data ke view dengan parameter:
            // - 'mhs': data mahasiswa (hanya data sendiri untuk mahasiswa)
            // - 'cekRole': data user yang sedang login
            return view('mahasiswa.index_mhs', ['mhs' => $mahasiswa, 'cekRole' => $cekRole]);
        }
    }
    //         return redirect('/mhs/show');
    //     } else {
    //         return redirect('/pinjam');
    //     }
    //     $mahasiswa = DB::table('mahasiswas')
    //         ->leftJoin('prodis', 'mahasiswas.prodi_id', '=', 'prodis.kode_prodi')
    //         ->select('mahasiswas.*', 'prodis.nama_prodi')
    //         ->get();

    //     return view('mahasiswa.index_mhs', ['mhs' => $mahasiswa]);
    // }

    // tambah method 
    public function tambah()
    {
        $prodi = DB::table('prodis')->orderBy('nama_prodi')->get();
        return view('mahasiswa.tambah_mhs', ['prodi' => $prodi]);
    }
    // simpan method
    public function simpan(Request $request)
    {
        $request->validate([
            'nim' => 'required|unique:mahasiswas,nim',
            'nama' => 'required',
            'tempat_lahir' => 'required',
            'tgl_lahir' => 'required|date',
            'prodi_id' => 'required',
            'th_masuk' => 'required|digits:4',
            'password' => 'required|min:4',
            'role' => 'required|in:mhs,admin',
        ]);
        
        // Mapping role dari form ke tipe_role database
        $tipe_role = $request->role == 'admin' ? 'admin_perpustakaan' : 'mahasiswa';
        
        DB::table('mahasiswas')->insert([
            'nim' => $request->nim,
            'nama' => $request->nama,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tgl_lahir,
            'prodi_id' => $request->prodi_id,
            'tahun_masuk' => $request->th_masuk,
            'password' => bcrypt($request->password),
            'tipe_role' => $tipe_role,
        ]);
        return redirect('/mhs/show');
    }

// edit method
    public function edit($id)
    {
        $mahasiswa = DB::table('mahasiswas')
            ->select(
                'nim',
                'nama',
                'tempat_lahir',
                'tanggal_lahir as tgl_lahir',
                'prodi_id',
                'tahun_masuk as th_masuk',
                'password',
                DB::raw("CASE WHEN tipe_role = 'admin_perpustakaan' THEN 'admin' WHEN tipe_role = 'mahasiswa' THEN 'mhs' ELSE 'mhs' END as role")
            )
            ->where('nim', $id)
            ->first();
        $prodi = DB::table('prodis')->orderBy('nama_prodi')->get();
        return view('mahasiswa.edit_mhs', ['mhs' => $mahasiswa, 'prodi' => $prodi]);
    }
// update method
    public function update(Request $request, $id)
    {
        // Mapping role dari form ke tipe_role database
        $tipe_role = $request->role == 'admin' ? 'admin_perpustakaan' : 'mahasiswa';
        
        DB::table('mahasiswas')->where('nim', $id)->update([
            'nama' => $request->nama,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tgl_lahir,
            'prodi_id' => $request->prodi_id,
            'tahun_masuk' => $request->th_masuk,
            'password' => bcrypt($request->password),
            'tipe_role' => $tipe_role,
        ]);
        // return redirect()->back()->with('success', 'Data berhasil diperbarui!');
        return redirect('/mhs/show')->with('success', 'Data mahasiswa berhasil diperbarui!');
    }
// hapus method
    public function hapus($id)
    {
        DB::table('mahasiswas')->where('nim', $id)->delete();
        return redirect('/mhs/show')->with('success', 'Data mahasiswa berhasil dihapus.');
    }

// login

// untuk validasi login harus diisi
    public function login(Request $request)
    {
        $request->validate([
            'nim' => 'required',
            'password' => 'required',
        ]);
// auth untuk mahasiswa 
        if (Auth::guard('mahasiswas')->attempt([
            'nim' => $request ->nim,
            'password' => $request -> password,
        ])){
            return redirect()->intended('/mhs/show');
        }
        return back()->with('error', 'Nim atau Password salah');
    }

    // view login
    public function viewlogin()
    {
        return view('login.index_login');
    }
    public function logout(Request $request)
    {
        Auth::guard('mahasiswas')->logout();
        return redirect('/login');
    }
}