<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prodi extends Model
{
    use HasFactory;
    protected $table='prodi';

    protected $fillable=[
        'nama_prodi'
    ];

    public function cpl()
    {
        return $this->hasMany(Cpl::class, 'prodi_id');
    }

    public function student_matkul()
    {
        return $this->hasMany(student_Matkul::class, 'prodi_id');
    }

    protected static function boot()
    {
        parent::boot();

        // Cascade delete related CPL and associated Matkul, SubMatkul when Prodi is deleted
        static::deleting(function ($prodi) {
            // First delete related CPLs
            $prodi->cpl()->each(function ($cpl) {
                // Delete related Matkul and SubMatkul through Cpl
                $cpl->matkul()->each(function ($matkul) {
                    $matkul->submatkul()->delete(); // Delete related SubMatkul
                    $matkul->delete(); // Delete Matkul
                });
                $cpl->delete(); // Delete CPL
            });
        });
    }
}
