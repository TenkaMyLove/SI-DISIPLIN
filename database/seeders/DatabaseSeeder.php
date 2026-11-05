<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Single Admin Account
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator SIDISIPLIN',
                'password' => Hash::make('admin123'),
            ]
        );

        // 2. Default standard working hours
        Pengaturan::set('jam_masuk_standar', '08:00', 'Jam Masuk Standar');
        Pengaturan::set('jam_pulang_standar', '16:00', 'Jam Pulang Standar');

        // 3. Sample Pegawai from Kemenkumham Kalsel
        $samplePegawai = [
            [
                'nip' => '198503152010121001',
                'nama' => 'Ahmad Fauzi, S.H.',
                'divisi' => 'Pelayanan Hukum',
            ],
            [
                'nip' => '199008222015032002',
                'nama' => 'Siti Rahmawati, S.Sos.',
                'divisi' => 'Humas',
            ],
            [
                'nip' => '198811052012121003',
                'nama' => 'Budi Santoso, S.Kom.',
                'divisi' => 'Tata Usaha dan Umum',
            ],
            [
                'nip' => '199204182019012004',
                'nama' => 'Dewi Anggraini, S.H., M.H.',
                'divisi' => 'Kekayaan Intelektual',
            ],
            [
                'nip' => '198706302011011005',
                'nama' => 'Muhammad Rizky, S.E.',
                'divisi' => 'Perencanaan',
            ],
            [
                'nip' => '199309142020121006',
                'nama' => 'Nurul Hidayah, S.Tr.A.P.',
                'divisi' => 'SDM',
            ],
            [
                'nip' => '198602102009031007',
                'nama' => 'Hendro Prasetyo, S.H.',
                'divisi' => 'P3H',
            ],
            [
                'nip' => '199105252014022008',
                'nama' => 'Kartika Sari, S.H.',
                'divisi' => 'Administrasi Hukum Umum',
            ],
            [
                'nip' => '198907122013011009',
                'nama' => 'Ferry Gunawan, S.H.',
                'divisi' => 'Analis Hukum',
            ],
            [
                'nip' => '199401152021031001',
                'nama' => 'Bambang Wijaya, S.H.',
                'divisi' => 'Pelayanan Hukum',
            ],
            [
                'nip' => '199505202022022002',
                'nama' => 'Rina Kusuma, S.E.',
                'divisi' => 'Perencanaan',
            ],
            [
                'nip' => '199612102023011003',
                'nama' => 'Dedi Saputra, S.Kom.',
                'divisi' => 'Tata Usaha dan Umum',
            ],
        ];

        foreach ($samplePegawai as $data) {
            Pegawai::firstOrCreate(['nip' => $data['nip']], $data);
        }

        // 4. Sample problematic attendance records
        $sampleAbsensi = [
            [
                'nip' => '198503152010121001',
                'periode' => '2026-11-02',
                'jam_masuk' => '08:35:00',
                'jam_pulang' => '16:00:00',
                'status' => Absensi::STATUS_TERLAMBAT,
                'alasan' => 'Macet di Jalan A. Yani KM 6',
                'durasi_menit' => 35,
            ],
            [
                'nip' => '198503152010121001',
                'periode' => '2026-11-03',
                'jam_masuk' => '08:00:00',
                'jam_pulang' => '15:15:00',
                'status' => Absensi::STATUS_PULANG_DULUAN,
                'alasan' => 'Ada keperluan keluarga mendesak',
                'durasi_menit' => 45,
            ],
            [
                'nip' => '199008222015032002',
                'periode' => '2026-11-03',
                'jam_masuk' => '08:00:00',
                'jam_pulang' => '16:00:00',
                'status' => Absensi::STATUS_TANPA_KETERANGAN,
                'alasan' => 'Tidak hadir tanpa pemberitahuan',
                'durasi_menit' => 450,
            ],
            [
                'nip' => '198811052012121003',
                'periode' => '2026-11-04',
                'jam_masuk' => '08:50:00',
                'jam_pulang' => '15:30:00',
                'status' => Absensi::STATUS_TERLAMBAT,
                'alasan' => 'Ban bocor dan izin pulang lebih awal',
                'durasi_menit' => 80,
            ],
        ];

        foreach ($sampleAbsensi as $data) {
            Absensi::create($data);
        }
    }
}
