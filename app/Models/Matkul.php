<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matkul extends Model
{
    use HasFactory;
    protected $table = 'matkul';

    protected $fillable = [
        'kode_matkul',
        'nama_matkul',
        'sks',
        'cpl_id',
        'prodi_id'
    ];

    public function cpl()
    {
        return $this->belongsTo(Cpl::class, 'cpl_id');
    }

    public function pilihCpmk()
    {
        return $this->hasMany(pilihCpmk::class, 'matkul_id');
    }
    
    // Relationship with SubMatkul model
    public function submatkul()
    {
        return $this->hasMany(SubMatkul::class, 'matkul_id');
    }

    public function student_Matkul()
    {
        return $this->hasMany(student_Matkul::class, 'matkul_id');
    }

    // Relasi ke student_pilihBukti
    public function student_pilihBukti()
    {
        return $this->hasMany(student_pilihBukti::class, 'matkul_id');
    }

    public function student_asessment()
    {
        return $this->hasMany(student_asessment::class, 'matkul_id');
    }

    protected static function boot()
    {
        parent::boot();

        // Cascade delete related SubMatkul when Matkul is deleted
        static::deleting(function ($matkul) {
            // Delete related SubMatkul
            $matkul->submatkul()->delete(); // Delete SubMatkul first

            // Delete related student_pilihBukti
            student_pilihBukti::where('matkul_id',$matkul->id)->delete();
        });
    }
}
