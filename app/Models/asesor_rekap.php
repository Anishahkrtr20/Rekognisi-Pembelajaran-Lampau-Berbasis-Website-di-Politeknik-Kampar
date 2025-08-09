<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class asesor_rekap extends Model
{
    use HasFactory;
    protected $table = 'asesor_rekap';

    protected $fillable = [
        'asesor_id',
        'daftar_id',
        'matkul_id',
        'hasil_vatm',
        'hasil_rekap',
        'status_lulus',
        'editable',
        'status_kirim',
        'komen',
    ];

    public function daftar()
    {
        return $this->belongsTo(Daftar::class, 'daftar_id');
    }

    public function asesor()
    {
        return $this->belongsTo(Asesor::class, 'asesor_id');
    }

    public function asesor_vatm()
    {
        return $this->belongsTo(asesor_vatm::class, 'daftar_id');
    }
}
