<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class asesorAssesment extends Model
{
    use HasFactory;
    protected $table='asesor_assesment';

    protected $fillable=[
        'daftar_id',
        'prodi_id',
        'asesor_id',
        'status',
    ];

    public function daftar()
    {
        return $this->belongsTo(Daftar::class, 'daftar_id');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function asesor()
    {
        return $this->belongsTo(Asesor::class, 'asesor_id');
    }

    public function asesor_vatm()
    {
        return $this->hasOne(asesor_vatm::class, 'daftar_id', 'daftar_id');
    }

    public function asesor_rekap()
    {
        return $this->hasMany(asesor_rekap::class, 'daftar_id', 'daftar_id');
    }

    public function student_biodata()
    {
        return $this->belongsTo(student_biodata::class, 'daftar_id', 'daftar_id');
    }

    public function student_matkul()
    {
        return $this->hasMany(student_Matkul::class, 'daftar_id', 'daftar_id');
    }
}
