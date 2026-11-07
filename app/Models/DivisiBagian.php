<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DivisiBagian extends Model
{
    use HasFactory;

    protected $table = 'divisi_bagian';

    protected $fillable = [
        'nama',
        'kode',
        'deskripsi',
    ];

    /**
     * Relationship to Pegawai
     */
    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'divisi_bagian_id', 'id');
    }
}
