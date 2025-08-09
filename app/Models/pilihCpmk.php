<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pilihCpmk extends Model
{
    use HasFactory;

    protected $table = 'pilihCpmk';

    protected $fillable = [
        'prodi_id', // ID prodi yang dipilih
        'cpl_id', // ID cpl kuliah yang dipilih
        'matkul_id', // ID kode kuliah yang dipilih
        'submatkul_id', //ID subMatkul yang dipilih
    ];

    public function matkul()
    {
        return $this->belongsTo(Matkul::class, 'matkul_id');
    }

    public function submatkul()
    {
        return $this->belongsTo(SubMatkul::class, 'submatkul_id');
    }
}
