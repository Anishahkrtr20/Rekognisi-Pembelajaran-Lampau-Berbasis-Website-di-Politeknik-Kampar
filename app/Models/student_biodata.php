<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class student_biodata extends Model
{
    use HasFactory;
    protected $table = 'student_biodata';

    protected $fillable = [
        'daftar_id',     
        'status_nikah',
        'tahun_ajaran',                   
        'nama_instansi',        
        'jabatan',        
        'alamat_instansi',
        'divisi',        
        'status_pegawai',
        'lama_bekerja',        
        'status',
        'editable',
    ];

    public function daftar()
    {
       return $this->belongsTo(Daftar::class, 'daftar_id');
    }

    public function asesorAssesment()
    {
        return $this->hasMany(asesorAssesment::class, 'daftar_id', 'daftar_id');
    }

    public function Experiences()
    {
        return $this->hasMany(Experiences::class, 'daftar_id', 'daftar_id');
    }

    public function user()
    {
        return $this->hasOneThrough(User::class, Daftar::class, 'id', 'id', 'daftar_id', 'user_id');
    }
}
