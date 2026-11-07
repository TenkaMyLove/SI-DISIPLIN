<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    ];

    protected $fillable = [
        'nip',
        'nama',
        'divisi',
        'divisi_bagian_id',
    ];

    protected $casts = [
        'nip' => 'string', // Keep as string in JSON to preserve 18-digit precision in JS
    ];

    /**
     * Relationship: Pegawai belongs to a DivisiBagian.
     */
    public function divisiBagian(): BelongsTo
    {
        return $this->belongsTo(DivisiBagian::class, 'divisi_bagian_id', 'id');
    }

    /**
     * Relationship: An employee has many attendance records.
     */
    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'nip', 'nip');
    }
}
