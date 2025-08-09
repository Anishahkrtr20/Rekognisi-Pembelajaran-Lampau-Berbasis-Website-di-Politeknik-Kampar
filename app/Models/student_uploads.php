<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class student_uploads extends Model
{
    use HasFactory;

    protected $table = 'student_uploads';

    protected $fillable = [
        'daftar_id',     
        'kode_bukti',                   
        'nama_bukti',        
        'jenis',        
        'file',
        'keterangan',           
        'status',
        'editable',
    ];

    public function daftar()
    {
       return $this->belongsTo(Daftar::class, 'daftar_id');
    }

    public function user()
    {
        return $this->hasOneThrough(User::class, Daftar::class, 'id', 'id', 'daftar_id', 'user_id');
    }

    // Relasi ke SubMatkul, jika mengarah ke kolom `matkul_id` di tabel `submatkul`
    public function submatkul()
    {
        return $this->belongsTo(SubMatkul::class, 'submatkul_id');
    }

    // Relasi ke student_pilihBukti
    public function student_pilihBukti()
    {
        return $this->hasMany(student_pilihBukti::class, 'bukti_id');
    }

    public function student_asessment()
    {
        return $this->hasMany(student_asessment::class, 'daftar_id', 'daftar_id');
    }
}
