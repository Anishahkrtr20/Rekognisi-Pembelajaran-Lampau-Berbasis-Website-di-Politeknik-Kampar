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
        Schema::create('asesor_vatm', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asesor_id');
            $table->unsignedBigInteger('daftar_id');
            $table->unsignedBigInteger('matkul_id');
            $table->string('hasil');
            $table->integer('editable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asesor_vatm');
    }
};
