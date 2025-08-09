<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class student_pilihBukti extends Model
{
    use HasFactory;

    protected $table = 'student_pilihbukti';

    protected $fillable = [
        'daftar_id',
        'cpl_id', // ID kode kuliah yang dipilih
        'matkul_id', // ID kode kuliah yang dipilih
        'bukti_id', // ID bukti yang dipilih
        'status',
        'editable',
    ];

    // Relasi ke model Daftar
    public function daftar()
    {
       return $this->belongsTo(Daftar::class, 'daftar_id');
    }

    // Relasi ke student_uploads
    public function matkul()
    {
        return $this->belongsTo(Matkul::class, 'matkul_id');
    }

    // Relasi ke student_uploads
    public function student_uploads()
    {
        return $this->belongsTo(student_uploads::class, 'bukti_id');
    }
}
