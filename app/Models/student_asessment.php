<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class student_asessment extends Model
{
    use HasFactory;

    protected $table = 'student_asessment';

    protected $fillable = [
        'daftar_id',     
        'jenis_rpl',                   
        'matkul_id',        
        'deskripsi',
        'pernyataan',
        'status',
        'editable',
    ];

    // Relasi ke model Daftar
    public function daftar()
    {
       return $this->belongsTo(Daftar::class, 'daftar_id');
    }

    public function matkul()
    {
        return $this->belongsTo(Matkul::class, 'matkul_id');
    }
}
