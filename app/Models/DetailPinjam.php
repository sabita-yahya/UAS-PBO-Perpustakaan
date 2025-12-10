<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPinjam extends Model
{
    use HasFactory;
    
    protected $table = 'detail_pinjams_tabel';
    protected $fillable = ['pinjam_id', 'kode_buku', 'jumlah_buku', 'status'];
}
