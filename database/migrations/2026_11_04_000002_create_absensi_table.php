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
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nip');
            $table->date('periode');
            $table->time('jam_masuk');
            $table->time('jam_pulang');
            $table->enum('status', [
                'Tanpa Keterangan',
                'Terlambat',
                'Pulang Duluan',
            ]);
            $table->text('alasan')->nullable();
            $table->integer('durasi_menit');
            $table->timestamps();

            // Relationships & Indexes
            $table->foreign('nip')
                ->references('nip')
                ->on('pegawai')
                ->onDelete('restrict');

            $table->index('periode');
            $table->index(['nip', 'periode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
