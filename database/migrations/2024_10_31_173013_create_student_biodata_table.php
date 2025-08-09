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
        Schema::create('student_biodata', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('daftar_id');
            $table->string('status_nikah'); // Ubah menjadi string (atau enum jika ada nilai tetap)
            $table->string('tahun_ajaran');
            $table->string('nama_instansi')->nullable();
            $table->string('jabatan')->nullable();
            $table->text('alamat_instansi')->nullable();
            $table->string('divisi')->nullable();
            $table->string('status_pegawai')->nullable(); // Ubah menjadi string jika ada nilai tetap
            $table->string('lama_bekerja')->nullable();
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
        Schema::dropIfExists('student_biodata');
    }
};
