<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\DivisiBagian;
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

        // 2. Default standard working hours & schedule (Per Hari)
        Pengaturan::set('jam_masuk_standar', '07:30', 'Jam Masuk Standar');
        Pengaturan::set('jam_pulang_standar', '16:00', 'Jam Pulang Standar');

        foreach (Pengaturan::DAYS as $dayKey => $meta) {
            $label = $meta['label'];
            Pengaturan::set("{$dayKey}_masuk", $meta['masuk'], "Jam Masuk {$label}");
            Pengaturan::set("{$dayKey}_pulang", $meta['pulang'], "Jam Pulang {$label}");
            Pengaturan::set("{$dayKey}_flex", $meta['flex'], "Flex {$label}");
            Pengaturan::set("{$dayKey}_flex_masuk_mulai", $meta['masuk'], "Batas Awal Masuk Flex {$label}");
            Pengaturan::set("{$dayKey}_flex_masuk_akhir", '08:00', "Batas Akhir Masuk Flex {$label}");
        }

        // Backward compatibility
        Pengaturan::set('selasa_kamis_masuk', '07:30', 'Jam Masuk Selasa-Kamis');
        Pengaturan::set('selasa_kamis_pulang', '16:00', 'Jam Pulang Selasa-Kamis');
        Pengaturan::set('selasa_kamis_flex', '1', 'Flex Selasa-Kamis');

        // 3. Master Divisi / Bagian (Divisi_Bagian)
        $defaultDivisi = [
            ['nama' => 'Pelayanan Hukum', 'kode' => 'YANKUM', 'deskripsi' => 'Divisi Pelayanan Hukum'],
            ['nama' => 'P3H', 'kode' => 'P3H', 'deskripsi' => 'Divisi Peraturan Perundang-undangan dan Pembudayaan Hukum'],
            ['nama' => 'Tata Usaha dan Umum', 'kode' => 'TU_UMUM', 'deskripsi' => 'Bagian Tata Usaha dan Umum'],
        ];

        $divisiMap = [];
        foreach ($defaultDivisi as $div) {
            $record = DivisiBagian::firstOrCreate(['nama' => $div['nama']], $div);
            $divisiMap[$div['nama']] = $record->id;
        }

        // 4. Sample Pegawai from Kemenkumham Kalsel (Pelayanan Hukum, P3H, Tata Usaha dan Umum)
        $samplePegawai = [
            [
                'nip' => '198503152010121001',
                'nama' => 'Ahmad Fauzi, S.H.',
                'divisi' => 'Pelayanan Hukum',
            ],
            [
                'nip' => '199008222015032002',
                'nama' => 'Siti Rahmawati, S.Sos.',
                'divisi' => 'Tata Usaha dan Umum',
            ],
            [
                'nip' => '198811052012121003',
                'nama' => 'Budi Santoso, S.Kom.',
                'divisi' => 'Tata Usaha dan Umum',
            ],
            [
                'nip' => '199204182019012004',
                'nama' => 'Dewi Anggraini, S.H., M.H.',
                'divisi' => 'Pelayanan Hukum',
            ],
            [
                'nip' => '198706302011011005',
                'nama' => 'Muhammad Rizky, S.E.',
                'divisi' => 'P3H',
            ],
            [
                'nip' => '199309142020121006',
                'nama' => 'Nurul Hidayah, S.Tr.A.P.',
                'divisi' => 'Tata Usaha dan Umum',
            ],
            [
                'nip' => '198602102009031007',
                'nama' => 'Hendro Prasetyo, S.H.',
                'divisi' => 'P3H',
            ],
            [
                'nip' => '199105252014022008',
                'nama' => 'Kartika Sari, S.H.',
                'divisi' => 'Pelayanan Hukum',
            ],
            [
                'nip' => '198907122013011009',
                'nama' => 'Ferry Gunawan, S.H.',
                'divisi' => 'P3H',
            ],
            [
                'nip' => '199401152021031001',
                'nama' => 'Bambang Wijaya, S.H.',
                'divisi' => 'Pelayanan Hukum',
            ],
            [
                'nip' => '199505202022022002',
                'nama' => 'Rina Kusuma, S.E.',
                'divisi' => 'Tata Usaha dan Umum',
            ],
            [
                'nip' => '199612102023011003',
                'nama' => 'Dedi Saputra, S.Kom.',
                'divisi' => 'Tata Usaha dan Umum',
            ],
        ];

        foreach ($samplePegawai as $data) {
            $data['divisi_bagian_id'] = $divisiMap[$data['divisi']] ?? null;
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
