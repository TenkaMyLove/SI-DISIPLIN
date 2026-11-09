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
        if (!Schema::hasColumn('pegawai', 'divisi_bagian_id')) {
            Schema::table('pegawai', function (Blueprint $table) {
                $table->unsignedBigInteger('divisi_bagian_id')->nullable()->after('divisi');
                $table->foreign('divisi_bagian_id')
                    ->references('id')
                    ->on('divisi_bagian')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pegawai', 'divisi_bagian_id')) {
            Schema::table('pegawai', function (Blueprint $table) {
                $table->dropForeign(['divisi_bagian_id']);
                $table->dropColumn('divisi_bagian_id');
            });
        }
    }
};
