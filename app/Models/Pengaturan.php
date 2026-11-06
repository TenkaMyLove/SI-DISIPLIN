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
        return [
            'senin' => [
                'hari' => 'Senin',
                'masuk' => static::get('senin_masuk', '07:30'),
                'pulang' => static::get('senin_pulang', '16:00'),
                'is_flex' => (bool) intval(static::get('senin_flex', '0')),
            ],
            'selasa_kamis' => [
                'hari' => 'Selasa - Kamis',
                'masuk' => static::get('selasa_kamis_masuk', '07:30'),
                'pulang' => static::get('selasa_kamis_pulang', '16:00'),
                'is_flex' => (bool) intval(static::get('selasa_kamis_flex', '1')),
            ],
            'jumat' => [
                'hari' => 'Jumat',
                'masuk' => static::get('jumat_masuk', '07:30'),
                'pulang' => static::get('jumat_pulang', '16:30'),
                'is_flex' => (bool) intval(static::get('jumat_flex', '0')),
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

        if ($dayOfWeek === \Carbon\Carbon::MONDAY) {
            return [
                'hari' => 'Senin',
                'masuk' => static::get('senin_masuk', '07:30'),
                'pulang' => static::get('senin_pulang', '16:00'),
                'is_flex' => (bool) intval(static::get('senin_flex', '0')),
            ];
        } elseif ($dayOfWeek === \Carbon\Carbon::FRIDAY) {
            return [
                'hari' => 'Jumat',
                'masuk' => static::get('jumat_masuk', '07:30'),
                'pulang' => static::get('jumat_pulang', '16:30'),
                'is_flex' => (bool) intval(static::get('jumat_flex', '0')),
            ];
        } else {
            // Selasa, Rabu, Kamis (dan fallback Sabtu-Minggu bila ada)
            $isFlexDay = ($dayOfWeek >= 2 && $dayOfWeek <= 4);
            return [
                'hari' => $isFlexDay ? 'Selasa - Kamis' : 'Akhir Pekan',
                'masuk' => static::get('selasa_kamis_masuk', '07:30'),
                'pulang' => static::get('selasa_kamis_pulang', '16:00'),
                'is_flex' => $isFlexDay ? (bool) intval(static::get('selasa_kamis_flex', '1')) : false,
            ];
        }
    }
}
