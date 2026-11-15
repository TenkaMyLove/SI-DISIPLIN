<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->time('jam_masuk')->nullable()->change();
            $table->time('jam_pulang')->nullable()->change();
            $table->enum('status', [
                'Tanpa Keterangan',
                'Terlambat',
                'Pulang Duluan',
                'Hanya Absen Masuk / Pulang',
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('absensi')
            ->where('status', 'Hanya Absen Masuk / Pulang')
            ->delete();

        Schema::table('absensi', function (Blueprint $table) {
            $table->time('jam_masuk')->nullable(false)->change();
            $table->time('jam_pulang')->nullable(false)->change();
            $table->enum('status', [
                'Tanpa Keterangan',
                'Terlambat',
                'Pulang Duluan',
            ])->change();
        });
    }
};
