<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class student_profile extends Model
{
    //
    use HasFactory;

    protected $table = 'student_profile';

    protected $fillable = [
        'daftar_id',     
        'file',
    ];

    public function daftar()
    {
       return $this->belongsTo(Daftar::class, 'daftar_id');
    }
}
