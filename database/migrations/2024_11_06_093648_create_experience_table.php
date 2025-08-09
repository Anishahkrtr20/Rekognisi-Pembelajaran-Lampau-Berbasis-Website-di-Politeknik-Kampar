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
        Schema::create('student_experience', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('daftar_id');
            $table->string('jenis_pengalaman');
            $table->string('kegiatan');
            $table->date('tahun');
            $table->string('penyelenggara');
            $table->string('jangka_waktu');
            $table->string('jabatan');
            $table->text('det_kegiatan');
            $table->string('file_sertifikat');
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
        Schema::dropIfExists('experience');
    }
};
