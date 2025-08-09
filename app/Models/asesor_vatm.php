<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class asesor_vatm extends Model
{
    use HasFactory;

    protected $table = 'asesor_vatm';

    // Kolom-kolom yang dapat diisi secara massal
    protected $fillable = [
        'asesor_id',
        'daftar_id',
        'matkul_id',
        'hasil',
        'editable',
    ];

    public function daftar()
    {
        return $this->belongsTo(Daftar::class, 'daftar_id');
    }

    // Relasi ke student_uploads
    public function student_pilihBukti()
    {
        return $this->belongsTo(student_pilihBukti::class, 'bukti_id');
    }

    // Relasi ke asesor_rekap
    public function asesor_rekap()
    {
        return $this->hasMany(asesor_rekap::class, 'daftar_id');
    }
}
