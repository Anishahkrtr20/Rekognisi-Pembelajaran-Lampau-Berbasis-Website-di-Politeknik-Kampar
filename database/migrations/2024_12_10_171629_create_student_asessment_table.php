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
        Schema::create('student_asessment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('daftar_id');
            $table->string('jenis_rpl');
            $table->unsignedBigInteger('matkul_id');
            $table->text('deskripsi');
            $table->string('pernyataan');
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
        Schema::dropIfExists('student_asessment');
    }
};
