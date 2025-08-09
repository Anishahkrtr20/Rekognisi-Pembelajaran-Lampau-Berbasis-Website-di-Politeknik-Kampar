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
        Schema::create('student_pilihbukti', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('daftar_id');
            $table->unsignedBigInteger('cpl_id');
            $table->unsignedBigInteger('matkul_id');
            $table->unsignedBigInteger('bukti_id');
            $table->integer('status'); // Biarkan jika status menggunakan integer
            $table->integer('editable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_pilihBukti');
    }
};
