<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::create([
        'name' => 'Administrator',
        'email' => 'admin@rpl.co.id',
        'password' => Hash::make('user123'),
        'is_active' => 1,
        'status' => 1
        ]);

        //
        role::create([
            'nama_role' => 'Administrator',
        ]);

        role::create([
            'nama_role' => 'Asesor',
        ]);

        role::create([
            'nama_role' => 'Pendaftar',
        ]);
    }
}
