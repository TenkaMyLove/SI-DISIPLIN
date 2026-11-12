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
     * 1 hari kerja = 7,5 jam = 450 menit.
     */
    public static function formatMenit(int $totalMenit): string
    {
        if ($totalMenit <= 0) {
            return '0 menit';
        }

        $hari = intdiv($totalMenit, 450); // 7.5 jam * 60 menit = 450 menit (1 hari kerja)
        $sisaMenitSetelahHari = $totalMenit % 450;

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

    private static function timeToSeconds(string $time): int
    {
        $parts = explode(':', $time);
        $hours = (int) ($parts[0] ?? 0);
        $minutes = (int) ($parts[1] ?? 0);
        $seconds = (int) ($parts[2] ?? 0);

        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }

    /**
     * Calculate automatic duration based on working hours and flexitime rules.
     * Flex: Arrivals within the flex window shift the target departure time to fulfill daily working hours.
     * Arrivals past flex_masuk_akhir incur uncompensated lateness; Non-Flex directly adds arrival + early-leave deficits.
     */
    public static function hitungOtomatisDurasi(
        string $status,
        string $jamMasuk,
        string $jamPulang,
        ?string $tanggal = null,
        ?string $jamMasukStandar = null,
        ?string $jamPulangStandar = null,
        ?bool $forceFlex = null
    ): int {
        if ($status === self::STATUS_TANPA_KETERANGAN) {
            return 450; // 7.5 jam * 60 menit
        }

        // Backward compatibility: if 4th param is a time string (e.g. "08:00")
        if ($tanggal && str_contains($tanggal, ':') && ! str_contains($tanggal, '-')) {
            $jamMasukStandar = $tanggal;
            $tanggal = null;
        }

        $jadwal = $tanggal ? Pengaturan::getJadwalByTanggal($tanggal) : null;
        $stdMasuk = $jamMasukStandar ?? ($jadwal ? $jadwal['masuk'] : Pengaturan::get('jam_masuk_standar', '07:30'));
        $stdPulang = $jamPulangStandar ?? ($jadwal ? $jadwal['pulang'] : Pengaturan::get('jam_pulang_standar', '16:00'));
        $isFlex = $forceFlex !== null ? $forceFlex : ($jadwal ? $jadwal['is_flex'] : false);

        $masukSec = self::timeToSeconds($jamMasuk);
        $pulangSec = self::timeToSeconds($jamPulang);
        $stdMasukSec = self::timeToSeconds($stdMasuk);
        $stdPulangSec = self::timeToSeconds($stdPulang);

        if ($isFlex) {
            $stdDurationSec = max(0, $stdPulangSec - $stdMasukSec);
            $flexMulaiSec = $jadwal && isset($jadwal['flex_masuk_mulai'])
                ? self::timeToSeconds($jadwal['flex_masuk_mulai'])
                : $stdMasukSec;
            $flexAkhirSec = $jadwal && isset($jadwal['flex_masuk_akhir'])
                ? self::timeToSeconds($jadwal['flex_masuk_akhir'])
                : $stdPulangSec;

            // Keterlambatan melewati batas akhir flexi tidak bisa dikompensasi pada jam pulang
            $keterlambatanLuarFlexSec = max(0, $masukSec - $flexAkhirSec);
            $effectiveMasukSec = max($flexMulaiSec, min($masukSec, $flexAkhirSec));
            $targetPulangSec = $effectiveMasukSec + $stdDurationSec;
            $kekuranganPulangSec = max(0, $targetPulangSec - $pulangSec);

            return intval(round(($keterlambatanLuarFlexSec + $kekuranganPulangSec) / 60));
        }

        // Non-Flex: keterlambatan masuk + pulang lebih awal
        $selisihMasukMenit = max(0, intval(round(($masukSec - $stdMasukSec) / 60)));
        $selisihPulangMenit = max(0, intval(round(($stdPulangSec - $pulangSec) / 60)));

        return $selisihMasukMenit + $selisihPulangMenit;
    }
}
