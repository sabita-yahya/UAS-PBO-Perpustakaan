<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;

class mahasiswas extends Authenticatable
{
    use HasFactory;

    protected $table = 'mahasiswas';
    protected $fillable = [
        'nim', 'nama', 'tempat_lahir', 'tanggal_lahir', 'prodi_id', 'tahun_masuk', 'password', 'tipe_role'
    ];
    protected $hidden = ['password'];

    // Accessor untuk kompatibilitas dengan kode yang menggunakan 'role'
    public function getRoleAttribute()
    {
        // Mapping tipe_role dari database ke format yang digunakan aplikasi
        $roleMapping = [
            'admin_perpustakaan' => 'admin',
            'mahasiswa' => 'mhs'
        ];
        return $roleMapping[$this->tipe_role] ?? 'mhs';
    }

    // Accessor untuk kompatibilitas nama kolom tanggal_lahir -> tgl_lahir
    public function getTglLahirAttribute()
    {
        return $this->attributes['tanggal_lahir'] ?? null;
    }

    // Accessor untuk kompatibilitas nama kolom tahun_masuk -> th_masuk
    public function getThMasukAttribute()
    {
        return $this->attributes['tahun_masuk'] ?? null;
    }
}
