<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubMatkul extends Model
{
    use HasFactory;
    
    protected $table = 'submatkul';

    protected $fillable = ([
        'sub_matkul',
        'matkul_id',
        'prodi_id',
    ]);

    public function matkul()
    {
        return $this->belongsTo(Matkul::class, 'matkul_id');
    }

    public function pilihCpmk()
    {
        return $this->hasMany(pilihCpmk::class, 'submatkul_id');
    }

    // Relasi ke student_uploads
    public function student_uploads()
    {
        return $this->hasMany(student_uploads::class, 'bukti_id');
    }
}
