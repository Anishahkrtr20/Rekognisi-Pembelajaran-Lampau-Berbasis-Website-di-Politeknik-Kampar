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
        Schema::create('asesor_rekap', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asesor_id');
            $table->unsignedBigInteger('daftar_id');
            $table->unsignedBigInteger('matkul_id');
            $table->unsignedBigInteger('hasil_vatm');
            $table->string('hasil_rekap');
            $table->string('status_lulus');
            $table->integer('editable');
            $table->integer('status_kirim');
            $table->text('komen')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asesor_rekap');
    }
};
