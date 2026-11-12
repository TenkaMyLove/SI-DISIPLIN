<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePengaturanRequest;
use App\Models\Pengaturan;
use Illuminate\Http\JsonResponse;

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
    public function update(UpdatePengaturanRequest $request): JsonResponse
    {
        $currentJadwal = Pengaturan::getJadwal();
        $input = $request->all();

        foreach (array_keys(Pengaturan::DAYS) as $dayKey) {
            $resolved = Pengaturan::resolveDayInput($dayKey, $input, $currentJadwal[$dayKey]);
            $label = $resolved['label'];

            Pengaturan::set("{$dayKey}_masuk", $resolved['masuk'], "Jam Masuk {$label}");
            Pengaturan::set("{$dayKey}_pulang", $resolved['pulang'], "Jam Pulang {$label}");
            Pengaturan::set("{$dayKey}_flex", $resolved['is_flex'] ? '1' : '0', "Flex {$label}");
            Pengaturan::set("{$dayKey}_flex_masuk_mulai", $resolved['flex_masuk_mulai'], "Batas Awal Masuk Flex {$label}");
            Pengaturan::set("{$dayKey}_flex_masuk_akhir", $resolved['flex_masuk_akhir'], "Batas Akhir Masuk Flex {$label}");
        }

        $jadwal = Pengaturan::getJadwal();

        // Backward compatibility keys
        Pengaturan::set('selasa_kamis_masuk', $jadwal['selasa']['masuk'], 'Jam Masuk Selasa-Kamis');
        Pengaturan::set('selasa_kamis_pulang', $jadwal['selasa']['pulang'], 'Jam Pulang Selasa-Kamis');
        Pengaturan::set('selasa_kamis_flex', $jadwal['selasa']['is_flex'] ? '1' : '0', 'Flex Selasa-Kamis');

        Pengaturan::set('jam_masuk_standar', $jadwal['senin']['masuk'], 'Jam Masuk Standar');
        Pengaturan::set('jam_pulang_standar', $jadwal['senin']['pulang'], 'Jam Pulang Standar');

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan jam kerja berhasil disimpan.',
            'data' => [
                'jadwal' => $jadwal,
                'jam_masuk_standar' => $jadwal['senin']['masuk'],
                'jam_pulang_standar' => $jadwal['senin']['pulang'],
            ],
        ]);
    }
}
