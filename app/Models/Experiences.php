<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experiences extends Model
{
    use HasFactory;

    protected $table = 'student_experience';

    protected $fillable = [
        'daftar_id',
        'jenis_pengalaman',
        'kegiatan',
        'tahun',
        'penyelenggara',
        'jangka_waktu',
        'jabatan',
        'det_kegiatan',
        'file_sertifikat',
        'status',
        'editable',
    ];

    public function daftar()
    {
       return $this->belongsTo(Daftar::class, 'daftar_id');
    }

    public function student_matkul()
    {
        return $this->hasMany(student_Matkul::class, 'daftar_id', 'daftar_id');
    }

    public function student_pilihBukti()
    {
        return $this->hasMany(student_pilihBukti::class, 'daftar_id', 'daftar_id');
    }
}
