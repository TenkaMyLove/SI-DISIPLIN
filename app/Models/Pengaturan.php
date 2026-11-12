<?php

namespace App\Models;

use Carbon\Carbon;
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

    public const DAYS = [
        'senin' => [
            'label' => 'Senin',
            'masuk' => '07:30',
            'pulang' => '16:00',
            'flex' => '0',
            'fallback_masuk_key' => null,
            'fallback_pulang_key' => null,
            'fallback_flex_key' => null,
        ],
        'selasa' => [
            'label' => 'Selasa',
            'masuk' => '07:30',
            'pulang' => '16:00',
            'flex' => '1',
            'fallback_masuk_key' => 'selasa_kamis_masuk',
            'fallback_pulang_key' => 'selasa_kamis_pulang',
            'fallback_flex_key' => 'selasa_kamis_flex',
        ],
        'rabu' => [
            'label' => 'Rabu',
            'masuk' => '07:30',
            'pulang' => '16:00',
            'flex' => '1',
            'fallback_masuk_key' => 'selasa_kamis_masuk',
            'fallback_pulang_key' => 'selasa_kamis_pulang',
            'fallback_flex_key' => 'selasa_kamis_flex',
        ],
        'kamis' => [
            'label' => 'Kamis',
            'masuk' => '07:30',
            'pulang' => '16:00',
            'flex' => '1',
            'fallback_masuk_key' => 'selasa_kamis_masuk',
            'fallback_pulang_key' => 'selasa_kamis_pulang',
            'fallback_flex_key' => 'selasa_kamis_flex',
        ],
        'jumat' => [
            'label' => 'Jumat',
            'masuk' => '07:30',
            'pulang' => '16:30',
            'flex' => '0',
            'fallback_masuk_key' => null,
            'fallback_pulang_key' => null,
            'fallback_flex_key' => null,
        ],
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

    public static function timeToMinutes(string $time): int
    {
        $parts = explode(':', $time);
        $hours = (int) ($parts[0] ?? 0);
        $minutes = (int) ($parts[1] ?? 0);

        return ($hours * 60) + $minutes;
    }

    public static function minutesToTime(int $totalMinutes): string
    {
        $clamped = max(0, min(1439, $totalMinutes));
        $hours = intdiv($clamped, 60);
        $minutes = $clamped % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Build schedule data for a single weekday using preloaded settings map when provided.
     *
     * @param  array<string, ?string>|null  $settingsMap
     */
    public static function buildDaySchedule(string $dayKey, ?array $settingsMap = null): array
    {
        $meta = self::DAYS[$dayKey];
        $map = $settingsMap ?? static::pluck('value', 'key')->all();

        $getVal = function (string $key, ?string $default = null) use ($map): ?string {
            return array_key_exists($key, $map) && $map[$key] !== null ? $map[$key] : $default;
        };

        $fallbackMasuk = $meta['fallback_masuk_key']
            ? $getVal($meta['fallback_masuk_key'], $meta['masuk'])
            : $meta['masuk'];
        $fallbackPulang = $meta['fallback_pulang_key']
            ? $getVal($meta['fallback_pulang_key'], $meta['pulang'])
            : $meta['pulang'];
        $fallbackFlex = $meta['fallback_flex_key']
            ? $getVal($meta['fallback_flex_key'], $meta['flex'])
            : $meta['flex'];

        $masuk = $getVal("{$dayKey}_masuk", $fallbackMasuk);
        $pulang = $getVal("{$dayKey}_pulang", $fallbackPulang);
        $isFlex = (bool) intval($getVal("{$dayKey}_flex", $fallbackFlex));

        $masukMin = self::timeToMinutes($masuk);
        $pulangMin = self::timeToMinutes($pulang);
        $stdDurationMin = max(0, $pulangMin - $masukMin);

        $defaultFlexAkhir = self::minutesToTime(min(max($masukMin, $pulangMin - 1), $masukMin + 30));
        $flexMasukMulai = $getVal("{$dayKey}_flex_masuk_mulai", $masuk);
        $flexMasukAkhir = $getVal("{$dayKey}_flex_masuk_akhir", $defaultFlexAkhir);

        $flexMulaiMin = self::timeToMinutes($flexMasukMulai);
        $flexAkhirMin = self::timeToMinutes($flexMasukAkhir);

        return [
            'hari' => $meta['label'],
            'masuk' => $masuk,
            'pulang' => $pulang,
            'is_flex' => $isFlex,
            'flex_masuk_mulai' => $flexMasukMulai,
            'flex_masuk_akhir' => $flexMasukAkhir,
            'flex_pulang_mulai' => self::minutesToTime($flexMulaiMin + $stdDurationMin),
            'flex_pulang_akhir' => self::minutesToTime($flexAkhirMin + $stdDurationMin),
            'flex_toleransi_menit' => max(0, $flexAkhirMin - $masukMin),
        ];
    }

    /**
     * Get complete operational working hours schedule.
     */
    public static function getJadwal(): array
    {
        $settingsMap = static::pluck('value', 'key')->all();

        $jadwal = [];
        foreach (array_keys(self::DAYS) as $dayKey) {
            $jadwal[$dayKey] = self::buildDaySchedule($dayKey, $settingsMap);
        }

        // Kept for backward compatibility
        $jadwal['selasa_kamis'] = [
            ...$jadwal['selasa'],
            'hari' => 'Selasa - Kamis',
        ];

        return $jadwal;
    }

    /**
     * Get applicable schedule for a given date.
     */
    public static function getJadwalByTanggal(string|\DateTimeInterface $date): array
    {
        $carbon = is_string($date) ? Carbon::parse($date) : Carbon::instance($date);

        $dayMap = [
            Carbon::MONDAY => 'senin',
            Carbon::TUESDAY => 'selasa',
            Carbon::WEDNESDAY => 'rabu',
            Carbon::THURSDAY => 'kamis',
            Carbon::FRIDAY => 'jumat',
        ];

        $dayKey = $dayMap[$carbon->dayOfWeek] ?? null;

        if ($dayKey !== null) {
            return self::buildDaySchedule($dayKey);
        }

        $masuk = static::get('jam_masuk_standar', '07:30');
        $pulang = static::get('jam_pulang_standar', '16:00');

        return [
            'hari' => 'Akhir Pekan',
            'masuk' => $masuk,
            'pulang' => $pulang,
            'is_flex' => false,
            'flex_masuk_mulai' => $masuk,
            'flex_masuk_akhir' => $masuk,
            'flex_pulang_mulai' => $pulang,
            'flex_pulang_akhir' => $pulang,
            'flex_toleransi_menit' => 0,
        ];
    }

    /**
     * Resolve submitted schedule input for a single weekday with backward-compatible fallbacks.
     *
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $existing
     */
    public static function resolveDayInput(string $dayKey, array $input, array $existing): array
    {
        $meta = self::DAYS[$dayKey];

        $fallbackMasuk = $meta['fallback_masuk_key']
            ? ($input[$meta['fallback_masuk_key']] ?? $existing['masuk'])
            : ($dayKey === 'senin' ? ($input['jam_masuk_standar'] ?? $existing['masuk']) : $existing['masuk']);

        $fallbackPulang = $meta['fallback_pulang_key']
            ? ($input[$meta['fallback_pulang_key']] ?? $existing['pulang'])
            : ($dayKey === 'senin' ? ($input['jam_pulang_standar'] ?? $existing['pulang']) : $existing['pulang']);

        $masuk = ! empty($input["{$dayKey}_masuk"]) ? $input["{$dayKey}_masuk"] : $fallbackMasuk;
        $pulang = ! empty($input["{$dayKey}_pulang"]) ? $input["{$dayKey}_pulang"] : $fallbackPulang;

        $isFlex = array_key_exists("{$dayKey}_flex", $input)
            ? filter_var($input["{$dayKey}_flex"], FILTER_VALIDATE_BOOLEAN)
            : (bool) $existing['is_flex'];

        $masukMin = self::timeToMinutes($masuk);
        $pulangMin = self::timeToMinutes($pulang);
        $defaultFlexAkhir = self::minutesToTime(min(max($masukMin, $pulangMin - 1), $masukMin + 30));

        $flexMulai = ! empty($input["{$dayKey}_flex_masuk_mulai"])
            ? $input["{$dayKey}_flex_masuk_mulai"]
            : (self::timeToMinutes($existing['flex_masuk_mulai']) <= $masukMin ? $existing['flex_masuk_mulai'] : $masuk);

        $flexAkhir = ! empty($input["{$dayKey}_flex_masuk_akhir"])
            ? $input["{$dayKey}_flex_masuk_akhir"]
            : (self::timeToMinutes($existing['flex_masuk_akhir']) >= $masukMin
                && self::timeToMinutes($existing['flex_masuk_akhir']) < $pulangMin
                ? $existing['flex_masuk_akhir']
                : $defaultFlexAkhir);

        return [
            'label' => $meta['label'],
            'masuk' => $masuk,
            'pulang' => $pulang,
            'is_flex' => $isFlex,
            'flex_masuk_mulai' => $flexMulai,
            'flex_masuk_akhir' => $flexAkhir,
        ];
    }
}
