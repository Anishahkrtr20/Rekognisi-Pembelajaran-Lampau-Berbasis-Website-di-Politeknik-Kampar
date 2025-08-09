<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class phoneContact extends Model
{
    use HasFactory;
    protected $table='phone_contact';

    protected $fillable=[
        'phone_number'
    ];
}
