<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'pegawai';
    protected $primaryKey = 'nip';
    public $incrementing = false;
    protected $keyType = 'string';

    public const DIVISI_LIST = [
        'Pelayanan Hukum',
        'P3H',
        'Tata Usaha dan Umum',
        'Kekayaan Intelektual',
        'Administrasi Hukum Umum',
        'Humas',
        'SDM',
        'Perencanaan',
        'Analis Hukum',
    ];

    protected $fillable = [
        'nip',
        'nama',
        'divisi',
    ];

    protected $casts = [
        'nip' => 'string', // Keep as string in JSON to preserve 18-digit precision in JS
    ];

    /**
     * Relationship: An employee has many attendance records.
     */
    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'nip', 'nip');
    }
}
