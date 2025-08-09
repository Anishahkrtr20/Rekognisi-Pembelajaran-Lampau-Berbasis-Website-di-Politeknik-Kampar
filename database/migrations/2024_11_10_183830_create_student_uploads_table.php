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
        Schema::create('student_uploads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('daftar_id');
            $table->string('kode_bukti');
            $table->string('nama_bukti');
            $table->string('jenis');
            $table->string('file');
            $table->text('keterangan');
            $table->integer('status');
            $table->integer('editable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_uploads');
    }
};
