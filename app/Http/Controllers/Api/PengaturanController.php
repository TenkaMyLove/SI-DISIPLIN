<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    /**
     * Get system settings.
     */
    public function index(): JsonResponse
    {
        $jadwal = Pengaturan::getJadwal();
        $jamMasuk = $jadwal['senin']['masuk'];
        $jamPulang = $jadwal['senin']['pulang'];

        return response()->json([
            'success' => true,
            'data' => [
                'jadwal' => $jadwal,
                'jam_masuk_standar' => $jamMasuk,
                'jam_pulang_standar' => $jamPulang,
            ],
        ]);
    }

    /**
     * Update system settings.
     */
    public function update(Request $request): JsonResponse
    {
        $timeRegex = '/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/';

        $request->validate([
            'senin_masuk' => ['nullable', 'regex:' . $timeRegex],
            'senin_pulang' => ['nullable', 'regex:' . $timeRegex],
            'selasa_masuk' => ['nullable', 'regex:' . $timeRegex],
            'selasa_pulang' => ['nullable', 'regex:' . $timeRegex],
            'rabu_masuk' => ['nullable', 'regex:' . $timeRegex],
            'rabu_pulang' => ['nullable', 'regex:' . $timeRegex],
            'kamis_masuk' => ['nullable', 'regex:' . $timeRegex],
            'kamis_pulang' => ['nullable', 'regex:' . $timeRegex],
            'jumat_masuk' => ['nullable', 'regex:' . $timeRegex],
            'jumat_pulang' => ['nullable', 'regex:' . $timeRegex],
            'selasa_kamis_masuk' => ['nullable', 'regex:' . $timeRegex],
            'selasa_kamis_pulang' => ['nullable', 'regex:' . $timeRegex],
            'jam_masuk_standar' => ['nullable', 'regex:' . $timeRegex],
            'jam_pulang_standar' => ['nullable', 'regex:' . $timeRegex],
        ]);

        $seninMasuk = $request->input('senin_masuk', $request->input('jam_masuk_standar', '07:30'));
        $seninPulang = $request->input('senin_pulang', $request->input('jam_pulang_standar', '16:00'));

        $selasaFallbackMasuk = $request->input('selasa_kamis_masuk', '07:30');
        $selasaFallbackPulang = $request->input('selasa_kamis_pulang', '16:00');

        $selasaMasuk = $request->input('selasa_masuk', $selasaFallbackMasuk);
        $selasaPulang = $request->input('selasa_pulang', $selasaFallbackPulang);

        $rabuMasuk = $request->input('rabu_masuk', $selasaFallbackMasuk);
        $rabuPulang = $request->input('rabu_pulang', $selasaFallbackPulang);

        $kamisMasuk = $request->input('kamis_masuk', $selasaFallbackMasuk);
        $kamisPulang = $request->input('kamis_pulang', $selasaFallbackPulang);

        $jumatMasuk = $request->input('jumat_masuk', '07:30');
        $jumatPulang = $request->input('jumat_pulang', '16:30');

        $days = [
            'Senin' => [$seninMasuk, $seninPulang],
            'Selasa' => [$selasaMasuk, $selasaPulang],
            'Rabu' => [$rabuMasuk, $rabuPulang],
            'Kamis' => [$kamisMasuk, $kamisPulang],
            'Jumat' => [$jumatMasuk, $jumatPulang],
        ];

        foreach ($days as $dayName => [$masuk, $pulang]) {
            if (strtotime($pulang) <= strtotime($masuk)) {
                return response()->json([
                    'success' => false,
                    'message' => "Jam Pulang {$dayName} harus lebih besar dari Jam Masuk.",
                ], 422);
            }
        }

        // Save detailed schedule per day
        Pengaturan::set('senin_masuk', $seninMasuk, 'Jam Masuk Senin');
        Pengaturan::set('senin_pulang', $seninPulang, 'Jam Pulang Senin');
        Pengaturan::set('senin_flex', '0', 'Flex Senin');

        Pengaturan::set('selasa_masuk', $selasaMasuk, 'Jam Masuk Selasa');
        Pengaturan::set('selasa_pulang', $selasaPulang, 'Jam Pulang Selasa');
        Pengaturan::set('selasa_flex', '1', 'Flex Selasa');

        Pengaturan::set('rabu_masuk', $rabuMasuk, 'Jam Masuk Rabu');
        Pengaturan::set('rabu_pulang', $rabuPulang, 'Jam Pulang Rabu');
        Pengaturan::set('rabu_flex', '1', 'Flex Rabu');

        Pengaturan::set('kamis_masuk', $kamisMasuk, 'Jam Masuk Kamis');
        Pengaturan::set('kamis_pulang', $kamisPulang, 'Jam Pulang Kamis');
        Pengaturan::set('kamis_flex', '1', 'Flex Kamis');

        Pengaturan::set('jumat_masuk', $jumatMasuk, 'Jam Masuk Jumat');
        Pengaturan::set('jumat_pulang', $jumatPulang, 'Jam Pulang Jumat');
        Pengaturan::set('jumat_flex', '0', 'Flex Jumat');

        // Backward compatibility keys
        Pengaturan::set('selasa_kamis_masuk', $selasaMasuk, 'Jam Masuk Selasa-Kamis');
        Pengaturan::set('selasa_kamis_pulang', $selasaPulang, 'Jam Pulang Selasa-Kamis');
        Pengaturan::set('selasa_kamis_flex', '1', 'Flex Selasa-Kamis');

        Pengaturan::set('jam_masuk_standar', $seninMasuk, 'Jam Masuk Standar');
        Pengaturan::set('jam_pulang_standar', $seninPulang, 'Jam Pulang Standar');

        $jadwal = Pengaturan::getJadwal();

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan jam kerja berhasil disimpan.',
            'data' => [
                'jadwal' => $jadwal,
                'jam_masuk_standar' => $seninMasuk,
                'jam_pulang_standar' => $seninPulang,
            ],
        ]);
    }
}
