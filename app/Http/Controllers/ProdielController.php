<?php

namespace App\Http\Controllers;
use App\Models\prodi;
use Illuminate\Http\Request;

class ProdielController extends Controller
{
    // method index
    public function index()
    {
        $prodi = prodi::all();
        return view('prodiel.index_prd', ['prd' => $prodi]);
    }
    
    // tambah method
    public function tambah()
    {
        return view('prodiel.tambah_prd');
        
    }
    
    // simpan method
    public function simpan(Request $request)
    {
        try {
            $request->validate([
                'kode_prodi' => 'required|unique:prodis,kode_prodi',
                'nama_prodi' => 'required',
                'singkatan' => 'required'
            ], [
                'kode_prodi.required' => 'Kode Prodi wajib diisi',
                'kode_prodi.unique' => 'Kode Prodi sudah ada',
                'nama_prodi.required' => 'Nama Prodi wajib diisi',
                'singkatan.required' => 'Singkatan wajib diisi'
            ]);
            
            $prodi = new prodi;
            $prodi->kode_prodi = $request->kode_prodi;
            $prodi->nama_prodi = $request->nama_prodi;
            $prodi->singkatan = $request->singkatan;
            $prodi->save();
            
            return response()->json(['status' => true, 'message' => 'Data Prodi Berhasil Disimpan!']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $key => $messages) {
                $errors = array_merge($errors, $messages);
            }
            return response()->json([
                'status' => false, 
                'message' => implode(', ', $errors)
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false, 
                'message' => 'Data gagal disimpan: ' . $e->getMessage()
            ], 500);
        }
    }

    // edit method
    public function edit($id)
    {
        $prodi = prodi::find($id);
        if (!$prodi) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }
        return response()->json($prodi);
    }
    
    // update method
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'nama_prodi' => 'required',
                'singkatan' => 'required'
            ], [
                'nama_prodi.required' => 'Nama Prodi wajib diisi',
                'singkatan.required' => 'Singkatan wajib diisi'
            ]);
            
            $prodi = prodi::find($id);
            if (!$prodi) {
                return response()->json([
                    'status' => false, 
                    'message' => 'Data prodi tidak ditemukan'
                ], 404);
            }
            
            $prodi->nama_prodi = $request->nama_prodi;
            $prodi->singkatan = $request->singkatan;
            $prodi->save();
            
            return response()->json(['status' => true, 'message' => 'Data Prodi Berhasil Diperbarui!']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $key => $messages) {
                $errors = array_merge($errors, $messages);
            }
            return response()->json([
                'status' => false, 
                'message' => implode(', ', $errors)
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false, 
                'message' => 'Data gagal diperbarui: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // hapus method
    public function hapus($id)
    {
        try {
            $prodi = prodi::find($id);

            if (!$prodi) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data prodi tidak ditemukan'
                ], 404);
            }

            $prodi->delete();

            return response()->json([
                'status' => true,
                'message' => 'Data prodi berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data prodi gagal dihapus: ' . $e->getMessage()
            ], 500);
        }
    }
}
