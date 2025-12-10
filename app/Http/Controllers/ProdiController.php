<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdiController extends Controller
{
    // method index
    public function index()
    {
        $prodi = DB::table('prodis')->get();
        return view('prodi.index_prd', ['prd' => $prodi]);
    }
    
    // tambah method
    public function tambah()
    {
        return view('prodi.tambah_prd');
    }
    
    // simpan method
    public function simpan(Request $request)
    {
        $request->validate([
            'kode_prodi' => 'required|unique:prodis,kode_prodi',
            'nama_prodi' => 'required',
            'singkatan' => 'required'
        ]);
        
        DB::table('prodis')->insert([
            'kode_prodi' => $request->kode_prodi,
            'nama_prodi' => $request->nama_prodi,
            'singkatan' => $request->singkatan
        ]);
        
        return redirect('/prd/show')->with('success', 'Data prodi berhasil disimpan.');
    }

    // edit method
    public function edit($id)
    {
        $prodi = DB::table('prodis')->where('kode_prodi', $id)->first();
        return view('prodi.edit_prd', ['prd' => $prodi]);
    }
    
    // update method
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_prodi' => 'required',
            'singkatan' => 'required'
        ]);
        
        DB::table('prodis')->where('kode_prodi', $id)->update([
            'nama_prodi' => $request->nama_prodi,
            'singkatan' => $request->singkatan
        ]);
        
        return redirect('/prd/show')->with('success', 'Data prodi berhasil diperbarui!');
    }
    
    // hapus method
    public function hapus($id)
    {
        DB::table('prodis')->where('kode_prodi', $id)->delete();
        return redirect('/prd/show')->with('success', 'Data prodi berhasil dihapus.');
    }
}
