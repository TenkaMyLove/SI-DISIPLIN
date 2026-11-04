<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';

    public const STATUS_TANPA_KETERANGAN = 'Tanpa Keterangan';
    public const STATUS_TERLAMBAT = 'Terlambat';
    public const STATUS_PULANG_DULUAN = 'Pulang Duluan';

    public const STATUS_LIST = [
        self::STATUS_TANPA_KETERANGAN,
        self::STATUS_TERLAMBAT,
        self::STATUS_PULANG_DULUAN,
    ];

    protected $fillable = [
        'nip',
        'periode',
        'jam_masuk',
        'jam_pulang',
        'status',
        'alasan',
        'durasi_menit',
    ];

    protected $casts = [
        'nip' => 'string',
        'periode' => 'date:Y-m-d',
        'durasi_menit' => 'integer',
    ];

    protected $appends = [
        'durasi_formatted',
        'periode_formatted',
    ];

    /**
     * Relationship: Attendance belongs to an employee.
     */
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nip');
    }

    /**
     * Format minutes to human readable "X hari Y jam Z menit" without 0 units (REK-8 & REK-9).
     */
    public static function formatMenit(int $totalMenit): string
    {
        if ($totalMenit <= 0) {
            return '0 menit';
        }

        $hari = intdiv($totalMenit, 1440); // 24 * 60 = 1440 menit
        $sisaMenitSetelahHari = $totalMenit % 1440;

        $jam = intdiv($sisaMenitSetelahHari, 60);
        $menit = $sisaMenitSetelahHari % 60;

        $parts = [];
        if ($hari > 0) {
            $parts[] = "{$hari} hari";
        }
        if ($jam > 0) {
            $parts[] = "{$jam} jam";
        }
        if ($menit > 0) {
            $parts[] = "{$menit} menit";
        }

        return empty($parts) ? '0 menit' : implode(' ', $parts);
    }

    /**
     * Accessor for human-readable duration of this record.
     */
    public function getDurasiFormattedAttribute(): string
    {
        return self::formatMenit($this->durasi_menit ?? 0);
    }

    /**
     * Accessor for date in DD-MM-YYYY format (ABS-3).
     */
    public function getPeriodeFormattedAttribute(): string
    {
        return $this->periode ? $this->periode->format('d-m-Y') : '';
    }

    /**
     * Calculate automatic duration based on PRD requirements (ABS-5, ABS-6).
     */
    public static function hitungOtomatisDurasi(
        string $status,
        string $jamMasuk,
        string $jamPulang,
        string $jamMasukStandar = '08:00',
        string $jamPulangStandar = '16:00'
    ): int {
        if ($status === self::STATUS_TANPA_KETERANGAN) {
            return 450; // 7.5 jam * 60 menit
        }

        $masukSec = strtotime($jamMasuk);
        $pulangSec = strtotime($jamPulang);
        $stdMasukSec = strtotime($jamMasukStandar);
        $stdPulangSec = strtotime($jamPulangStandar);

        $selisihMasukMenit = max(0, intval(round(($masukSec - $stdMasukSec) / 60)));
        $selisihPulangMenit = max(0, intval(round(($stdPulangSec - $pulangSec) / 60)));

        return $selisihMasukMenit + $selisihPulangMenit;
    }
}
