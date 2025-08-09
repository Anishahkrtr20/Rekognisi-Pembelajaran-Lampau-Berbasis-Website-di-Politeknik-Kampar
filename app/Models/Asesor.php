<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asesor extends Model
{
    use HasFactory;

    // Pastikan nama tabel benar (hanya diperlukan jika nama tabel tidak sesuai konvensi plural)
    protected $table = 'asesor';

    // Kolom-kolom yang dapat diisi secara massal
    protected $fillable = [
        'user_id',
        'nama_asesor',
        'jk',
        'prodi_id',
        'alamat',
        'no_telepon',
    ];

    // Relasi belongsTo ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');  // Foreign key ke tabel user
    }

    // Jika prodi memiliki relasi, tambahkan relasi ke model Prodi
    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');  // Foreign key ke tabel prodi
    }

    public function asesorAssesment()
    {
        return $this->hasMany(asesorAssesment::class, 'asesor_id');
    }
}
