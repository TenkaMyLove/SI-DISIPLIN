<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsensiRequest;
use App\Http\Requests\UpdateAbsensiRequest;
use App\Models\Absensi;
use App\Models\Pengaturan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    /**
     * Display a listing of absensi records for admin.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Absensi::with('pegawai');

        // Filter by NIP / Pegawai
        if ($request->filled('nip')) {
            $query->where('nip', $request->input('nip'));
        }

        // Search by keyword (NIP or Nama pegawai)
        if ($request->filled('q')) {
            $keyword = $request->input('q');
            $query->where(function ($q) use ($keyword) {
                $q->where('nip', 'like', "%{$keyword}%")
                  ->orWhereHas('pegawai', function ($sub) use ($keyword) {
                      $sub->where('nama', 'like', "%{$keyword}%");
                  });
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by Date / Date range
        if ($request->filled('tanggal')) {
            $query->whereDate('periode', $request->input('tanggal'));
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('periode', [$request->input('start_date'), $request->input('end_date')]);
        }

        // Sorting
        $query->orderBy('periode', 'desc')->orderBy('created_at', 'desc');

        $perPage = max(5, min(100, (int) $request->input('per_page', 15)));
        $absensi = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $absensi->items(),
            'meta' => [
                'current_page' => $absensi->currentPage(),
                'last_page' => $absensi->lastPage(),
                'per_page' => $absensi->perPage(),
                'total' => $absensi->total(),
            ],
        ]);
    }

    /**
     * Helper endpoint to compute automatic duration.
     */
    public function hitungDurasi(Request $request): JsonResponse
    {
        $status = $request->input('status', Absensi::STATUS_TERLAMBAT);
        $jamMasuk = $request->input('jam_masuk', '08:00');
        $jamPulang = $request->input('jam_pulang', '16:00');

        $stdMasuk = Pengaturan::get('jam_masuk_standar', '08:00');
        $stdPulang = Pengaturan::get('jam_pulang_standar', '16:00');

        $durasiMenit = Absensi::hitungOtomatisDurasi($status, $jamMasuk, $jamPulang, $stdMasuk, $stdPulang);

        return response()->json([
            'success' => true,
            'durasi_menit' => $durasiMenit,
            'durasi_formatted' => Absensi::formatMenit($durasiMenit),
        ]);
    }

    /**
     * Store a newly created absensi record.
     */
    public function store(StoreAbsensiRequest $request): JsonResponse
    {
        $data = $request->validated();

        // If manual duration is not provided or null, calculate automatically
        if (! isset($data['durasi_menit']) || $data['durasi_menit'] === null) {
            $stdMasuk = Pengaturan::get('jam_masuk_standar', '08:00');
            $stdPulang = Pengaturan::get('jam_pulang_standar', '16:00');
            $data['durasi_menit'] = Absensi::hitungOtomatisDurasi(
                $data['status'],
                $data['jam_masuk'],
                $data['jam_pulang'],
                $stdMasuk,
                $stdPulang
            );
        }

        $absensi = Absensi::create($data);
        $absensi->load('pegawai');

        return response()->json([
            'success' => true,
            'message' => 'Catatan absensi bermasalah berhasil ditambahkan.',
            'data' => $absensi,
        ], 201);
    }

    /**
     * Display the specified absensi record.
     */
    public function show(int $id): JsonResponse
    {
        $absensi = Absensi::with('pegawai')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $absensi,
        ]);
    }

    /**
     * Update the specified absensi record.
     */
    public function update(UpdateAbsensiRequest $request, int $id): JsonResponse
    {
        $absensi = Absensi::findOrFail($id);
        $data = $request->validated();

        // If duration is not explicitly provided, recalculate
        if (! isset($data['durasi_menit']) || $data['durasi_menit'] === null) {
            $stdMasuk = Pengaturan::get('jam_masuk_standar', '08:00');
            $stdPulang = Pengaturan::get('jam_pulang_standar', '16:00');
            $data['durasi_menit'] = Absensi::hitungOtomatisDurasi(
                $data['status'],
                $data['jam_masuk'],
                $data['jam_pulang'],
                $stdMasuk,
                $stdPulang
            );
        }

        $absensi->update($data);
        $absensi->load('pegawai');

        return response()->json([
            'success' => true,
            'message' => 'Catatan absensi bermasalah berhasil diperbarui.',
            'data' => $absensi,
        ]);
    }

    /**
     * Remove the specified absensi record.
     */
    public function destroy(int $id): JsonResponse
    {
        $absensi = Absensi::findOrFail($id);
        $absensi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Catatan absensi bermasalah berhasil dihapus.',
        ]);
    }
}
