<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Pinjam;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PinjamController extends Controller
{
    /**
     * Cek koneksi database
     */
    public function checkDbConnection()
    {
        try {
            DB::connection()->getPdo();
            return 'Koneksi database berhasil!';
        } catch (\Exception $e) {
            return 'Tidak dapat terhubung ke database. Error: ' . $e->getMessage();
        }
    }
    /**
     * INDEX - Tampilkan list peminjaman
     */
    public function index()
    {
        $pinjams = DB::table('pinjams_tabel')
            ->select(
                'id',
                'tanggal_pinjam',
                'tanggal_kembali',
                'nim',
                'pegawai_id',
                DB::raw('tanggal_pinjam as tgl_pinjam'),
                DB::raw('tanggal_kembali as tgl_kembali')
            )
            ->orderBy('tanggal_pinjam', 'desc')
            ->get();

        $nims = $pinjams->pluck('nim')->unique()->map(fn($n)=> (string)$n)->filter()->values()->all();

        $mahasiswaMap = [];
        $mahasiswaRoleMap = [];
        if ($nims) {
            $mahasiswas = DB::table('mahasiswas')
                ->select(
                    'nim',
                    'nama',
                    DB::raw("CASE WHEN tipe_role = 'admin_perpustakaan' THEN 'admin' WHEN tipe_role = 'mahasiswa' THEN 'mhs' ELSE 'mhs' END as role")
                )
                ->whereIn('nim',$nims)
                ->get();
            $mahasiswaMap = $mahasiswas->pluck('nama','nim')->toArray();
            $mahasiswaRoleMap = $mahasiswas->pluck('role','nim')->toArray();
        }

        $detilTotals = DB::table('detail_pinjams_tabel')
            ->select('pinjam_id',DB::raw('SUM(jumlah_buku) as total_buku'))
            ->groupBy('pinjam_id')
            ->pluck('total_buku','pinjam_id')
            ->toArray();

        // Ambil data detil untuk cek status telat
        $pinjamIds = $pinjams->pluck('id');
        $detilStatusMap = [];
        if ($pinjamIds->isNotEmpty()) {
            $detils = DB::table('detail_pinjams_tabel')
                ->whereIn('pinjam_id', $pinjamIds)
                ->where('status', 1) // Hanya yang masih dipinjam
                ->select('pinjam_id')
                ->get()
                ->groupBy('pinjam_id');
            
            foreach ($detils as $pinjamId => $detil) {
                $detilStatusMap[$pinjamId] = true; // Ada yang masih dipinjam
            }
        }

        $today = Carbon::today();
        $pinjam = $pinjams->map(function($p) use ($mahasiswaMap, $mahasiswaRoleMap, $detilTotals, $detilStatusMap, $today){
            $nim = (string)$p->nim;
            $p->mahasiswa_nama = $mahasiswaMap[$nim] ?? null;
            $p->mahasiswa_role = $mahasiswaRoleMap[$nim] ?? null;
            $p->total_buku = $detilTotals[$p->id] ?? 0;
            
            // Cek apakah telat
            $p->is_telat = false;
            if (isset($detilStatusMap[$p->id])) {
                // Ada buku yang masih dipinjam
                $tglKembali = Carbon::parse($p->tanggal_kembali);
                $p->is_telat = $tglKembali->lt($today);
            }
            
            return $p;
        });

        return view('pinjam.index_pj', ['pj'=>$pinjam]);
    }


    /**
     * AUTOCOMPLETE BUKU
     */
    public function autocompleteBuku(Request $request)
    {
        $search = $request->term ?? '';

        $cari = DB::table('bukus')
            ->select('judul','id','stok_buku')
            ->when($search, fn($q)=> $q->where('judul','like','%'.$search.'%'))
            ->orderBy('judul','asc')
            ->limit(5)->get();

        $response = $cari->map(fn($b)=>[
            'value'=>$b->id,
            'label'=>$b->judul,
            'stock'=>$b->stok_buku  // Ubah key menjadi 'stock' agar sesuai dengan JavaScript
        ]);

        return response()->json($response);
    }


    /**
     * AUTOCOMPLETE MAHASISWA
     */
    public function autocompleteMahasiswa(Request $request)
    {
        $search = $request->term ?? '';

        $cari = DB::table('mahasiswas')
            ->select('nama','nim')
            ->when($search, fn($q)=> $q->where('nama','like','%'.$search.'%'))
            ->orderBy('nama','asc')
            ->limit(5)->get();

        $response = $cari->map(fn($m)=>[
            "value"=>$m->nim,
            "label"=>$m->nama
        ]);

        return response()->json($response);
    }


    /**
     * Cek apakah mahasiswa memiliki buku yang terlambat dikembalikan
     * Method ini digunakan untuk validasi akses berdasarkan role
     * Hanya mahasiswa dengan role 'mhs' yang akan dicek tanggungan telatnya
     * 
     * @param string $nim NIM mahasiswa yang akan dicek
     * @return bool true jika ada buku telat, false jika tidak ada
     */

    private function cekTanggunganTelat($nim)
    {
        // Ambil tanggal hari ini untuk perbandingan
        $today = Carbon::now()->subDays(2);
        // dd($today);
        // today() mengembalikan objek Carbon dengan tanggal hari ini tanpa waktu
        // Query untuk mencari buku yang:
        // 1. Dipinjam oleh mahasiswa dengan NIM tertentu
        // 2. Masih berstatus dipinjam (status = 1)
        // 3. Tanggal kembali sudah lewat dari hari ini (telat)
        $tanggunganTelat = DB::table('pinjams_tabel')
            ->join('detail_pinjams_tabel', 'pinjams_tabel.id', '=', 'detail_pinjams_tabel.pinjam_id')
            ->where('pinjams_tabel.nim', $nim)                    // Filter berdasarkan NIM
            ->where('detail_pinjams_tabel.status', 1)              // Status 1 = masih dipinjam (belum dikembalikan)
            ->whereDate('pinjams_tabel.tanggal_kembali', '>', $today) // Tanggal kembali sudah lewat
            ->count(); // Hitung jumlah buku yang memenuhi kriteria
            
        // Kembalikan true jika ada buku telat (count > 0), false jika tidak ada
        return $tanggunganTelat > 0;
    }

    /**
     * SIMPAN DATA PINJAM
     * Method ini menangani proses penyimpanan data peminjaman buku
     * Terdapat beberapa tahap validasi sebelum data disimpan ke database
     */
    public function simpan(Request $request)
    {
        // ============================================
        // BAGIAN 1: VALIDASI NIM
        // ============================================
        $nim = $request->nim;
        
        // Validasi NIM tidak boleh kosong
        // Jika NIM kosong, kembalikan error dan redirect ke halaman pinjam
        if (!$nim) {
            return redirect('/pinjam')->with('error', 'NIM tidak boleh kosong.');
        }
        
        // ============================================
        // BAGIAN 2: VALIDASI ROLE DAN AKSES
        // ============================================
        // Cek apakah mahasiswa dengan NIM tersebut ada di database
        $mahasiswa = DB::table('mahasiswas')
            ->select(
                'nim',
                'nama',
                DB::raw("CASE WHEN tipe_role = 'admin_perpustakaan' THEN 'admin' WHEN tipe_role = 'mahasiswa' THEN 'mhs' ELSE 'mhs' END as role")
            )
            ->where('nim', $nim)
            ->first();
        
        // Jika mahasiswa tidak ditemukan, kembalikan error
        if (!$mahasiswa) {
            return redirect('/pinjam')->with('error', 'Mahasiswa dengan NIM ' . $nim . ' tidak ditemukan.');
        }
        
        // PENGECEKAN ROLE: Hanya mahasiswa dengan role 'mhs' yang dicek tanggungan telat
        // Admin tidak perlu dicek tanggungan telat karena memiliki akses khusus
        // Jika mahasiswa yang akan meminjam memiliki role 'mhs' (mahasiswa biasa)
        if ($mahasiswa->role === 'mhs') {
            // Cek apakah mahasiswa masih memiliki buku yang terlambat dikembalikan
            // Method cekTanggunganTelat() akan mengembalikan true jika ada buku telat
            if ($this->cekTanggunganTelat($nim)) {
                // Jika ada tanggungan telat, tolak peminjaman dan beri pesan error
                return redirect('/pinjam')->with('error', 'Mahasiswa dengan NIM ' . $nim . ' tidak dapat meminjam buku karena masih memiliki buku yang terlambat dikembalikan. Silakan kembalikan buku yang terlambat terlebih dahulu.');
            }
        }
        // Jika role adalah 'admin', skip pengecekan tanggungan telat (admin bisa pinjam meski ada tanggungan)
        
        // ============================================
        // BAGIAN 3: VALIDASI DATA BUKU
        // ============================================
        // Ambil data buku dari request (berbentuk array karena bisa pinjam banyak buku)
        $kode_buku = $request->input('kode_buku', []);
        $jumlah_pinjam = $request->input('jumlah_pinjam', []);
        
        // Validasi: Pastikan ada buku yang dipinjam
        // Jika array kode_buku atau jumlah_pinjam kosong, kembalikan error
        if (empty($kode_buku) || empty($jumlah_pinjam)) {
            return redirect('/pinjam')->with('error', 'Data buku tidak boleh kosong. Silakan tambahkan buku terlebih dahulu.');
        }
        
        // ============================================
        // BAGIAN 4: VALIDASI STOK BUKU
        // ============================================
        // Array untuk menyimpan semua error validasi stok
        $errors = [];
        
        // Loop untuk mengecek setiap buku yang akan dipinjam
        for($i=0; $i<count($kode_buku); $i++){
            // Ambil data buku dari database berdasarkan kode_buku
            $buku = DB::table('bukus')->where('id', $kode_buku[$i])->first();
            
            // Validasi: Cek apakah buku ada di database
            if (!$buku) {
                $errors[] = 'Buku dengan kode ' . $kode_buku[$i] . ' tidak ditemukan.'; // Menambahkan pesan error jika buku tidak ditemukan
                continue; // Skip ke buku berikutnya jika buku tidak ditemukan
            }
            
            // Ambil stok tersedia dari database buku
            // Stock adalah jumlah buku yang masih tersedia untuk dipinjam
            $stockTersedia = $buku->stok_buku;
            
            // Konversi jumlah peminjaman ke integer untuk perbandingan
            $jumlahRequest = (int)$jumlah_pinjam[$i];
            
            // VALIDASI 1: Cek apakah jumlah peminjaman lebih dari 0
            // Jumlah peminjaman harus positif (tidak boleh 0 atau negatif)
            if ($jumlahRequest <= 0) {
                $errors[] = 'Jumlah peminjaman untuk buku ' . ($buku->judul ?? $kode_buku[$i]) . ' harus lebih dari 0.';
            } 
            // VALIDASI 2: Cek apakah jumlah peminjaman melebihi stok tersedia
            // Ini adalah validasi utama untuk mencegah peminjaman melebihi stok
            elseif ($jumlahRequest > $stockTersedia) { // Menambahkan pesan error jika jumlah peminjaman melebihi stok tersedia
                $errors[] = 'Jumlah peminjaman untuk buku "' . ($buku->judul ?? $kode_buku[$i]) . '" (' . $jumlahRequest . ') melebihi stok yang tersedia (' . $stockTersedia . ').';
            }
        }
        
        // Jika ada error validasi stok, kembalikan semua error dan batalkan proses
        if (!empty($errors)) {
            return redirect('/pinjam')->with('error', implode(' ', $errors));
        }
        
        // ============================================
        // BAGIAN 5: PENYIMPANAN DATA KE DATABASE
        // ============================================
        // Mulai transaksi database
        // beginTransaction() adalah fungsi untuk memulai transaksi database
        // Transaksi database adalah mekanisme untuk memastikan bahwa semua operasi database 
        // berjalan secara konsisten dan atomik (ACID: Atomicity, Consistency, Isolation, Durability)
        // Artinya: jika ada salah satu operasi database yang gagal, 
        // maka semua operasi database akan dibatalkan (rollback)
        DB::beginTransaction();
        
        // try-catch: Blok kode untuk menangani error
        // try: blok kode yang akan dijalankan jika transaksi database berhasil
        // catch: blok kode yang akan dijalankan jika terjadi error/exception
        try {
            // ============================================
            // SUB BAGIAN 5.1: SIMPAN DATA PEMINJAMAN UTAMA
            // ============================================
            // Simpan data peminjaman ke tabel 'pinjams_tabel'
            // Tabel ini menyimpan informasi utama peminjaman (NIM, tanggal, pegawai)
            $pinjam = new Pinjam;
            $pinjam->nim          = $request->nim;           // NIM mahasiswa yang meminjam
            $pinjam->tanggal_pinjam   = $request->tgl_pinjam;   // Tanggal peminjaman
            $pinjam->tanggal_kembali  = $request->tgl_kembali;  // Tanggal pengembalian yang dijadwalkan
            $pinjam->pegawai_id   = $request->pegawai_id;    // ID pegawai yang menangani peminjaman
            $pinjam->save(); // Simpan ke database
            
        // ============================================
        // SUB BAGIAN 5.2: SIMPAN DATA DETIL PEMINJAMAN
        // ============================================
        // Ambil kembali data buku dari request untuk disimpan ke detil
        $kode_buku  = $request->input('kode_buku',[]);      // Array kode buku yang dipinjam
        $jumlah_pinjam = $request->input('jumlah_pinjam',[]); // Array jumlah buku yang dipinjam
        $status     = $request->input('status',[]);           // Array status peminjaman (1 = masih dipinjam)            // Loop untuk menyimpan setiap buku yang dipinjam ke tabel detil_pinjams
            for($i=0; $i<count($kode_buku); $i++){
                // Simpan detil peminjaman ke tabel 'detail_pinjams_tabel'
                // Tabel ini menyimpan informasi detail setiap buku yang dipinjam
                DB::table('detail_pinjams_tabel')->insert([
                    'pinjam_id'=> $pinjam->id,        // ID dari data peminjaman utama yang baru disimpan
                    'kode_buku'=> $kode_buku[$i],     // Kode buku yang dipinjam
                    'jumlah_buku'=>  $jumlah_pinjam[$i], // Jumlah buku yang dipinjam
                    'status'=>    $status[$i]         // Status: 1 = masih dipinjam, 0 = sudah dikembalikan
                ]);

                // ============================================
                // SUB BAGIAN 5.3: UPDATE STOK BUKU
                // ============================================
                // Kurangi stok buku di tabel 'bukus'
                // decrement() mengurangi nilai kolom 'stok_buku' sebanyak jumlah_pinjam[$i]
                // Ini penting untuk menjaga konsistensi data stok
                DB::table('bukus')
                    ->where('id',$kode_buku[$i])
                    ->decrement('stok_buku',$jumlah_pinjam[$i]);
            }
            
            // ============================================
            // SUB BAGIAN 5.4: COMMIT TRANSAKSI
            // ============================================
            // Commit transaksi jika semua operasi berhasil
            // commit() akan menyimpan semua perubahan ke database secara permanen
            // Setelah commit, data tidak bisa di-rollback lagi
            DB::commit();
            
        } catch (\Exception $e) {
            // ============================================
            // BAGIAN 6: PENANGANAN ERROR
            // ============================================
            // Rollback transaksi jika terjadi error
            // rollback() akan membatalkan semua perubahan yang dilakukan dalam transaksi
            // Data akan kembali ke keadaan sebelum beginTransaction()
            DB::rollback();
            
            // Kembalikan error message ke user
            return redirect('/pinjam')->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }

        // ============================================
        // BAGIAN 7: SUKSES
        // ============================================
        // Jika semua proses berhasil, kembalikan pesan sukses
        return redirect('/pinjam')->with('success','Data peminjaman berhasil disimpan.');
    }


    /**
     * Menampilkan daftar peminjaman yang belum dikembalikan
     */
    public function daftarPengembalian()
    {
        $pinjams = collect(); // Kosongkan karena akan diisi via AJAX

        return view('kembali.index_k', compact('pinjams'));
    }

    /**
     * Get data peminjaman berdasarkan NIM (untuk AJAX)
     */
    public function getPeminjamanByNim(Request $request)
    {
        try {
            $nim = $request->nim;

            if (!$nim) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIM tidak boleh kosong'
                ], 400);
            }

            $pinjams = DB::table('pinjams_tabel')
                ->join('detail_pinjams_tabel', 'pinjams_tabel.id', '=', 'detail_pinjams_tabel.pinjam_id')
                ->join('bukus', 'detail_pinjams_tabel.kode_buku', '=', 'bukus.id')
                ->join('mahasiswas', 'pinjams_tabel.nim', '=', 'mahasiswas.nim')
                ->select(
                    'pinjams_tabel.nim',
                    'pinjams_tabel.tanggal_pinjam as tgl_pinjam',
                    'pinjams_tabel.tanggal_kembali as tgl_kembali',
                    'bukus.judul as judul_buku',
                    'mahasiswas.nama as nama_peminjam',
                    'detail_pinjams_tabel.id as detil_id',
                    'detail_pinjams_tabel.jumlah_buku as jml_buku'
                )
                ->where('pinjams_tabel.nim', $nim)
                ->where('detail_pinjams_tabel.status', 1) // Hanya tampilkan yang belum dikembalikan
                ->get();

            return response()->json([
                'success' => true,
                'data' => $pinjams
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Memproses pengembalian buku
     */
    public function prosesPengembalian(Request $request)
    {
        // Validasi request
        $request->validate([
            'detil_id' => 'required|exists:detail_pinjams_tabel,id',
        ]);

        // Ambil detil_id dari request POST
        $detil_id = $request->detil_id;

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            // Dapatkan kode buku dan jumlah yang dipinjam sebelum update
            $detil = DB::table('detail_pinjams_tabel')
                ->where('id', $detil_id)
                ->first();

            if (!$detil) {
                throw new \Exception('Data detil peminjaman tidak ditemukan');
            }

            // Update status peminjaman menjadi 0 (sudah dikembalikan)
            DB::table('detail_pinjams_tabel')
                ->where('id', $detil_id)
                ->update([
                    'status' => 0 // Sudah dikembalikan
                ]);

            // Update stok buku (tambah kembali stok yang dipinjam)
            DB::table('bukus')
                ->where('id', $detil->kode_buku)
                ->increment('stok_buku', $detil->jumlah_buku);

            // Commit transaksi
            DB::commit();

            return redirect()->route('kembali')
                ->with('success', 'Buku berhasil dikembalikan. Stok buku telah diperbarui.');
                
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi error
            DB::rollback();
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Menampilkan riwayat peminjaman untuk mahasiswa yang sedang login
     * Method ini menampilkan riwayat peminjaman berdasarkan role:
     * - Mahasiswa biasa (mhs): hanya melihat riwayat sendiri
     * - Admin: bisa melihat semua riwayat (jika diimplementasikan)
     */
    public function riwayat()
    {
        // ============================================
        // BAGIAN 1: VALIDASI AKSES (AUTHENTICATION)
        // ============================================
        // Ambil data user yang sedang login menggunakan guard 'mahasiswas'
        // Guard adalah mekanisme autentikasi untuk menentukan user yang login
        $user = Auth::guard('mahasiswas')->user();

        // Validasi: Cek apakah user sudah login
        // Jika user belum login, redirect ke halaman login dengan pesan error
        if (!$user) {
            return redirect()->route('viewlogin')->with('error', 'Silakan login terlebih dahulu.');
        }

        $pinjams = DB::table('pinjams_tabel')
            ->select(
                'id',
                'tanggal_pinjam',
                'tanggal_kembali',
                'nim',
                'pegawai_id',
                DB::raw('tanggal_pinjam as tgl_pinjam'),
                DB::raw('tanggal_kembali as tgl_kembali')
            )
            ->where('nim', $user->nim)
            ->orderBy('tanggal_pinjam', 'desc')
            ->get();

        $detilMap = [];
        if ($pinjams->isNotEmpty()) {
            $pinjamIds = $pinjams->pluck('id');
            $detilMap = DB::table('detail_pinjams_tabel')
                ->join('bukus', 'detail_pinjams_tabel.kode_buku', '=', 'bukus.id')
                ->select(
                    'detail_pinjams_tabel.pinjam_id',
                    'detail_pinjams_tabel.kode_buku',
                    'detail_pinjams_tabel.jumlah_buku',
                    'detail_pinjams_tabel.status',
                    'bukus.judul as judul_buku',
                    DB::raw('detail_pinjams_tabel.jumlah_buku as jml_buku')
                )
                ->whereIn('detail_pinjams_tabel.pinjam_id', $pinjamIds)
                ->get()
                ->groupBy('pinjam_id');
        }

        $today = Carbon::now();
        // Map data peminjaman untuk menampilkan riwayat peminjaman
        $riwayat = $pinjams->map(function ($pinjam) use ($detilMap, $today) {
            $pinjam->detil = $detilMap[$pinjam->id] ?? collect();
            // Cek apakah tanggal kembali sudah lewat
            $pinjam->is_telat = Carbon::parse($pinjam->tanggal_kembali)->lt($today) && 
                                $pinjam->detil->where('status', 1)->isNotEmpty();
            return $pinjam;
        });

        return view('pinjam.riwayat_pj', [
            'riwayat' => $riwayat,
            'mahasiswa' => $user,
        ]);
    }
    
    /**
     * Helper method untuk mendapatkan jumlah buku telat mahasiswa
     */
    public static function getBukuTelat($nim)
    {
        $today = Carbon::today();
        
        $bukuTelat = DB::table('pinjams_tabel')
            ->join('detail_pinjams_tabel', 'pinjams_tabel.id', '=', 'detail_pinjams_tabel.pinjam_id')
            ->join('bukus', 'detail_pinjams_tabel.kode_buku', '=', 'bukus.id')
            ->select(
                'pinjams_tabel.id',
                'pinjams_tabel.tanggal_kembali as tgl_kembali',
                'bukus.judul as nama_buku',
                'detail_pinjams_tabel.jumlah_buku as jml_buku'
            )
            ->where('pinjams_tabel.nim', $nim)
            ->where('detail_pinjams_tabel.status', 1) // Status 1 = masih dipinjam
            ->whereDate('pinjams_tabel.tanggal_kembali', '<', $today)
            ->get();
            
        return $bukuTelat;
    }
    
    /**
     * Laporan Pengembalian untuk Admin
     */
    public function laporanPengembalian()
    {
        // Ambil semua data peminjaman dengan detail
        $laporan = DB::table('pinjams_tabel')
            ->join('mahasiswas', 'pinjams_tabel.nim', '=', 'mahasiswas.nim')
            ->join('detail_pinjams_tabel', 'pinjams_tabel.id', '=', 'detail_pinjams_tabel.pinjam_id')
            ->join('bukus', 'detail_pinjams_tabel.kode_buku', '=', 'bukus.id')
            ->select(
                'pinjams_tabel.id as pinjam_id',
                'pinjams_tabel.nim',
                'mahasiswas.nama as nama_mahasiswa',
                DB::raw("CASE WHEN mahasiswas.tipe_role = 'admin_perpustakaan' THEN 'admin' WHEN mahasiswas.tipe_role = 'mahasiswa' THEN 'mhs' ELSE 'mhs' END as role"),
                'pinjams_tabel.tanggal_pinjam as tgl_pinjam',
                'pinjams_tabel.tanggal_kembali as tgl_kembali',
                'bukus.judul as nama_buku',
                'bukus.id as kode_buku',
                'detail_pinjams_tabel.jumlah_buku as jml_buku',
                'detail_pinjams_tabel.status',
                'detail_pinjams_tabel.id as detil_id'
            )
            ->orderBy('pinjams_tabel.tanggal_pinjam', 'desc')
            ->get();

        $today = Carbon::today();
        
        // Tambahkan informasi status telat
        $laporan = $laporan->map(function($item) use ($today) {
            $tglKembali = Carbon::parse($item->tgl_kembali);
            $item->is_telat = $tglKembali->lt($today) && $item->status == 1;
            $item->status_text = $item->status == 1 ? 'Belum Dikembalikan' : 'Sudah Dikembalikan';
            return $item;
        });

        // Group by pinjam_id untuk statistik
        $statistik = [
            'total_peminjaman' => $laporan->pluck('pinjam_id')->unique()->count(),
            'total_belum_kembali' => $laporan->where('status', 1)->count(),
            'total_telat' => $laporan->where('is_telat', true)->count(),
            'total_sudah_kembali' => $laporan->where('status', 0)->count(),
        ];

        return view('pinjam.laporan_pengembalian', [
            'laporan' => $laporan,
            'statistik' => $statistik
        ]);
    }
}
