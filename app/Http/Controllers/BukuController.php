<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BukuController extends Controller
{
    // method index
    public function index()
    {
        $buku = DB::table('bukus')
            ->select(
                'id',
                'judul',
                'pengarang',
                'penerbit',
                'tahun_terbit',
                'stok_buku',
                DB::raw('id as kode_buku'),
                DB::raw('judul as nama_buku'),
                DB::raw('tahun_terbit as th_terbit'),
                DB::raw('stok_buku as stock')
            )
            ->get();
        return view('buku.index_bk', ['bk' => $buku]);
    }
    
    // tambah method
    public function tambah()
    {
        return view('buku.tambah_bk');
    }
    
    // simpan method
    public function simpan(Request $request)
    {
        $request->validate([
            'nama_buku' => 'required',
            'pengarang' => 'required',
            'penerbit' => 'required',
            'th_terbit' => 'required|digits:4',
            'stock' => 'required'
        ]);
        
        DB::table('bukus')->insert([
            'judul' => $request->nama_buku,
            'pengarang' => $request->pengarang,
            'penerbit' => $request->penerbit,
            'tahun_terbit' => $request->th_terbit,
            'stok_buku' => $request->stock
        ]);
        
        return redirect('/bk/show')->with('success', 'Data buku berhasil disimpan.');
    }

    // edit method
    public function edit($id)
    {
        $buku = DB::table('bukus')
            ->select(
                'id',
                'judul',
                'pengarang',
                'penerbit',
                'tahun_terbit',
                'stok_buku',
                DB::raw('id as kode_buku'),
                DB::raw('judul as nama_buku'),
                DB::raw('tahun_terbit as th_terbit'),
                DB::raw('stok_buku as stock')
            )
            ->where('id', $id)
            ->first();
        return view('buku.edit_bk', ['bk' => $buku]);
    }
    
    // update method
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_buku' => 'required',
            'pengarang' => 'required',
            'penerbit' => 'required',
            'th_terbit' => 'required|digits:4',
            'stock' => 'required'
        ]);
        
        DB::table('bukus')->where('id', $id)->update([
            'judul' => $request->nama_buku,
            'pengarang' => $request->pengarang,
            'penerbit' => $request->penerbit,
            'tahun_terbit' => $request->th_terbit,
            'stok_buku' => $request->stock
        ]);
        
        return redirect('/bk/show')->with('success', 'Data buku berhasil diperbarui!');
    }
    
    // hapus method
    public function hapus($id)
    {
        DB::table('bukus')->where('id', $id)->delete();
        return redirect('/bk/show')->with('success', 'Data buku berhasil dihapus.');
    }
}
