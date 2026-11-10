<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    use HasFactory;

    protected $table = 'pengaturan';

    protected $fillable = [
        'key',
        'value',
        'deskripsi',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, ?string $value, ?string $deskripsi = null): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'deskripsi' => $deskripsi]
        );
    }

    /**
     * Get complete operational working hours schedule.
     */
    public static function getJadwal(): array
    {
        $selasaFallbackMasuk = static::get('selasa_kamis_masuk', '07:30');
        $selasaFallbackPulang = static::get('selasa_kamis_pulang', '16:00');
        $selasaFallbackFlex = static::get('selasa_kamis_flex', '1');

        return [
            'senin' => [
                'hari' => 'Senin',
                'masuk' => static::get('senin_masuk', '07:30'),
                'pulang' => static::get('senin_pulang', '16:00'),
                'is_flex' => (bool) intval(static::get('senin_flex', '0')),
            ],
            'selasa' => [
                'hari' => 'Selasa',
                'masuk' => static::get('selasa_masuk', $selasaFallbackMasuk),
                'pulang' => static::get('selasa_pulang', $selasaFallbackPulang),
                'is_flex' => (bool) intval(static::get('selasa_flex', $selasaFallbackFlex)),
            ],
            'rabu' => [
                'hari' => 'Rabu',
                'masuk' => static::get('rabu_masuk', $selasaFallbackMasuk),
                'pulang' => static::get('rabu_pulang', $selasaFallbackPulang),
                'is_flex' => (bool) intval(static::get('rabu_flex', $selasaFallbackFlex)),
            ],
            'kamis' => [
                'hari' => 'Kamis',
                'masuk' => static::get('kamis_masuk', $selasaFallbackMasuk),
                'pulang' => static::get('kamis_pulang', $selasaFallbackPulang),
                'is_flex' => (bool) intval(static::get('kamis_flex', $selasaFallbackFlex)),
            ],
            'jumat' => [
                'hari' => 'Jumat',
                'masuk' => static::get('jumat_masuk', '07:30'),
                'pulang' => static::get('jumat_pulang', '16:30'),
                'is_flex' => (bool) intval(static::get('jumat_flex', '0')),
            ],
            // Kept for backward compatibility
            'selasa_kamis' => [
                'hari' => 'Selasa - Kamis',
                'masuk' => static::get('selasa_masuk', $selasaFallbackMasuk),
                'pulang' => static::get('selasa_pulang', $selasaFallbackPulang),
                'is_flex' => (bool) intval(static::get('selasa_flex', $selasaFallbackFlex)),
            ],
        ];
    }

    /**
     * Get applicable schedule for a given date.
     */
    public static function getJadwalByTanggal(string|\DateTimeInterface $date): array
    {
        $carbon = is_string($date) ? \Carbon\Carbon::parse($date) : \Carbon\Carbon::instance($date);
        $dayOfWeek = $carbon->dayOfWeek; // 0 = Minggu, 1 = Senin, ..., 5 = Jumat, 6 = Sabtu

        $selasaFallbackMasuk = static::get('selasa_kamis_masuk', '07:30');
        $selasaFallbackPulang = static::get('selasa_kamis_pulang', '16:00');
        $selasaFallbackFlex = static::get('selasa_kamis_flex', '1');

        switch ($dayOfWeek) {
            case \Carbon\Carbon::MONDAY:
                return [
                    'hari' => 'Senin',
                    'masuk' => static::get('senin_masuk', '07:30'),
                    'pulang' => static::get('senin_pulang', '16:00'),
                    'is_flex' => (bool) intval(static::get('senin_flex', '0')),
                ];
            case \Carbon\Carbon::TUESDAY:
                return [
                    'hari' => 'Selasa',
                    'masuk' => static::get('selasa_masuk', $selasaFallbackMasuk),
                    'pulang' => static::get('selasa_pulang', $selasaFallbackPulang),
                    'is_flex' => (bool) intval(static::get('selasa_flex', $selasaFallbackFlex)),
                ];
            case \Carbon\Carbon::WEDNESDAY:
                return [
                    'hari' => 'Rabu',
                    'masuk' => static::get('rabu_masuk', $selasaFallbackMasuk),
                    'pulang' => static::get('rabu_pulang', $selasaFallbackPulang),
                    'is_flex' => (bool) intval(static::get('rabu_flex', $selasaFallbackFlex)),
                ];
            case \Carbon\Carbon::THURSDAY:
                return [
                    'hari' => 'Kamis',
                    'masuk' => static::get('kamis_masuk', $selasaFallbackMasuk),
                    'pulang' => static::get('kamis_pulang', $selasaFallbackPulang),
                    'is_flex' => (bool) intval(static::get('kamis_flex', $selasaFallbackFlex)),
                ];
            case \Carbon\Carbon::FRIDAY:
                return [
                    'hari' => 'Jumat',
                    'masuk' => static::get('jumat_masuk', '07:30'),
                    'pulang' => static::get('jumat_pulang', '16:30'),
                    'is_flex' => (bool) intval(static::get('jumat_flex', '0')),
                ];
            default:
                return [
                    'hari' => 'Akhir Pekan',
                    'masuk' => static::get('jam_masuk_standar', '07:30'),
                    'pulang' => static::get('jam_pulang_standar', '16:00'),
                    'is_flex' => false,
                ];
        }
    }
}
