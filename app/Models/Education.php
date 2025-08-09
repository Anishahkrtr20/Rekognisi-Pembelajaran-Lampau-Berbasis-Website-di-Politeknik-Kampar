<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasFactory;

    protected $table = 'student_education';

    protected $fillable = [
        'daftar_id',
        'nama_sekolah',
        'tahun_lulus',
        'jurusan',
        'status'
    ];

    public function daftar()
    {
       return $this->belongsTo(Daftar::class, 'daftar_id');
    }
}
