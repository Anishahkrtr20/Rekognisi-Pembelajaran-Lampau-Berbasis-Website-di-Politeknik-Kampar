<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Daftar extends Model
{
    use HasFactory;

    protected $table = 'daftar';

    protected $fillable = [
        'user_id',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'kebangsaan',
        'no_hp',
        'alamat',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function student_matkul()
    {
        return $this->hasMany(student_Matkul::class, 'daftar_id');
    }

    public function student_pilihbukti()
    {
        return $this->hasMany(student_pilihBukti::class, 'daftar_id');
    }

    public function student_uploads()
    {
        return $this->hasMany(student_uploads::class, 'daftar_id');
    }

    public function studentBiodata()
    {
        return $this->hasMany(student_biodata::class, 'daftar_id');
    }

    public function student_asessment()
    {
        return $this->hasMany(student_asessment::class, 'daftar_id');
    }

    public function asesorAssesment()
    {
        return $this->hasMany(asesorAssesment::class, 'daftar_id');
    }

    public function asesor_vatm()
    {
        return $this->hasMany(asesor_vatm::class, 'daftar_id');
    }

    public function asesor_rekap()
    {
        return $this->hasMany(asesor_rekap::class, 'daftar_id');
    }

    public function student_profile()
    {
        return $this->hasOne(student_profile::class, 'daftar_id');
    }

    public function experiences()
    {
        return $this->hasMany(student_profile::class, 'daftar_id');
    }
}
