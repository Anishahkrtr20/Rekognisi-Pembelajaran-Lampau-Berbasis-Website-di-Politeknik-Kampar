<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cpl extends Model
{
    use HasFactory;

    use HasFactory;
    protected $table='cpl';

    protected $fillable=[
        'cpl',
        'kode_cpl',
        'prodi_id',
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function matkul()
    {
        return $this->hasMany(Matkul::class, 'cpl_id');
    }

    public function student_matkul()
    {
        return $this->hasMany(student_Matkul::class, 'cpl_id');
    }

    protected static function boot()
    {
        parent::boot();

        // Cascade delete related Matkul and SubMatkul when CPL is deleted
        static::deleting(function ($cpl) {
            // Delete related Matkul
            $cpl->matkul()->each(function ($matkul) {
                // Delete related SubMatkul first
                $matkul->submatkul()->delete();
                // Then delete the Matkul
                $matkul->delete();
            });
            // Delete related student_pilihBukti
            student_pilihBukti::where('cpl_id', $cpl->id)->delete();
        });
    }
}
