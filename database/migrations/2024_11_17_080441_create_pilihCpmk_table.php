<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pilihCpmk', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prodi_id');
            $table->unsignedBigInteger('cpl_id');
            $table->unsignedBigInteger('matkul_id');
            $table->unsignedBigInteger('submatkul_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pilihCpmk');
    }
};
