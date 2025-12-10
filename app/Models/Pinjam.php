<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pinjam extends Model
{
    use HasFactory;
    
    protected $table = 'pinjams_tabel';
    protected $fillable = ['tanggal_pinjam', 'tanggal_kembali', 'nim', 'pegawai_id'];
}
