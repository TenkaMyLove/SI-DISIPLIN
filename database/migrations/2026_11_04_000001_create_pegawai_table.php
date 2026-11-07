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
        Schema::create('pegawai', function (Blueprint $table) {
            $table->unsignedBigInteger('nip')->primary();
            $table->string('nama');
            $table->string('divisi');
            $table->unsignedBigInteger('divisi_bagian_id')->nullable();
            $table->timestamps();

            $table->foreign('divisi_bagian_id')
                ->references('id')
                ->on('divisi_bagian')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
