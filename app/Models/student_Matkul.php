<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class student_Matkul extends Model
{
    use HasFactory;

    protected $table = 'student_matkul';

    protected $fillable = [
        'daftar_id',
        'prodi_id',
        'cpl_id',
        'matkul_id', // ID mata kuliah yang dipilih
        'status',
        'editable',
    ];

    // Relasi ke model Daftar
    public function daftar()
    {
       return $this->belongsTo(Daftar::class, 'daftar_id');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function cpl() {
        return $this->belongsTo(Cpl::class, 'cpl_id');
    }

    public function matkul()
    {
        return $this->belongsTo(Matkul::class, 'matkul_id');
    }

    public function asesorAssesment()
    {
        return $this->belongsTo(asesorAssesment::class, 'daftar_id', 'daftar_id');
    }

    public function pilihCpmk()
    {
        return $this->belongsTo(pilihCpmk::class, 'cpl_id', 'cpl_id');
    }

    public function student_pilihBukti()
    {
        return $this->hasMany(student_pilihBukti::class, 'daftar_id', 'daftar_id');
    }
}
