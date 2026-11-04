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
        $jamMasuk = Pengaturan::get('jam_masuk_standar', '08:00');
        $jamPulang = Pengaturan::get('jam_pulang_standar', '16:00');

        return response()->json([
            'success' => true,
            'data' => [
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
        $request->validate([
            'jam_masuk_standar' => ['required', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'jam_pulang_standar' => ['required', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
        ], [
            'jam_masuk_standar.required' => 'Jam Masuk Standar wajib diisi.',
            'jam_pulang_standar.required' => 'Jam Pulang Standar wajib diisi.',
        ]);

        if (strtotime($request->jam_pulang_standar) <= strtotime($request->jam_masuk_standar)) {
            return response()->json([
                'success' => false,
                'message' => 'Jam Pulang Standar harus lebih besar dari Jam Masuk Standar.',
            ], 422);
        }

        Pengaturan::set('jam_masuk_standar', $request->jam_masuk_standar, 'Jam Masuk Standar');
        Pengaturan::set('jam_pulang_standar', $request->jam_pulang_standar, 'Jam Pulang Standar');

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan jam kerja standar berhasil disimpan.',
            'data' => [
                'jam_masuk_standar' => $request->jam_masuk_standar,
                'jam_pulang_standar' => $request->jam_pulang_standar,
            ],
        ]);
    }
}
