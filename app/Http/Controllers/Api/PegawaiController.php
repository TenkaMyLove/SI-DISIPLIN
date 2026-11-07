<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePegawaiRequest;
use App\Http\Requests\UpdatePegawaiRequest;
use App\Models\DivisiBagian;
use App\Models\Pegawai;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    /**
     * Display a listing of pegawai with search and filter.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Pegawai::query()->withCount('absensi');

        // Search by NIP or Nama (PGW-7)
        if ($request->filled('q')) {
            $keyword = $request->input('q');
            $query->where(function ($q) use ($keyword) {
                $q->where('nip', 'like', "%{$keyword}%")
                  ->orWhere('nama', 'like', "%{$keyword}%");
            });
        }

        // Filter by Divisi
        if ($request->filled('divisi')) {
            $query->where('divisi', $request->input('divisi'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'nama');
        $sortOrder = $request->input('sort_order', 'asc');
        if (in_array($sortBy, ['nip', 'nama', 'divisi', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('nama', 'asc');
        }

        // Pagination or all
        if ($request->boolean('all')) {
            $pegawai = $query->get();
            return response()->json([
                'success' => true,
                'data' => $pegawai,
            ]);
        }

        $perPage = max(5, min(100, (int) $request->input('per_page', 15)));
        $pegawai = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $pegawai->items(),
            'meta' => [
                'current_page' => $pegawai->currentPage(),
                'last_page' => $pegawai->lastPage(),
                'per_page' => $pegawai->perPage(),
                'total' => $pegawai->total(),
            ],
        ]);
    }

    /**
     * Get lightweight list for autocomplete / dropdown options (ABS-2, REK-3).
     */
    public function options(Request $request): JsonResponse
    {
        $query = Pegawai::query()->select(['nip', 'nama', 'divisi']);

        if ($request->filled('q')) {
            $keyword = $request->input('q');
            $query->where(function ($q) use ($keyword) {
                $q->where('nip', 'like', "%{$keyword}%")
                  ->orWhere('nama', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('divisi')) {
            $query->where('divisi', $request->input('divisi'));
        }

        $list = $query->orderBy('nama', 'asc')->get();
        $dbDivisi = DivisiBagian::pluck('nama')->toArray();

        return response()->json([
            'success' => true,
            'data' => $list,
            'divisi_list' => !empty($dbDivisi) ? $dbDivisi : Pegawai::DIVISI_LIST,
        ]);
    }

    /**
     * Store a newly created pegawai.
     */
    public function store(StorePegawaiRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['divisi_bagian_id']) && !empty($data['divisi'])) {
            $data['divisi_bagian_id'] = DivisiBagian::where('nama', $data['divisi'])->value('id');
        }

        $pegawai = Pegawai::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Pegawai berhasil ditambahkan.',
            'data' => $pegawai,
        ], 201);
    }

    /**
     * Display the specified pegawai.
     */
    public function show(string $nip): JsonResponse
    {
        $pegawai = Pegawai::withCount('absensi')->where('nip', $nip)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $pegawai,
        ]);
    }

    /**
     * Update the specified pegawai.
     */
    public function update(UpdatePegawaiRequest $request, string $nip): JsonResponse
    {
        $pegawai = Pegawai::where('nip', $nip)->firstOrFail();
        $data = $request->validated();
        if (empty($data['divisi_bagian_id']) && !empty($data['divisi'])) {
            $data['divisi_bagian_id'] = DivisiBagian::where('nama', $data['divisi'])->value('id');
        }

        $pegawai->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Data pegawai berhasil diperbarui.',
            'data' => $pegawai,
        ]);
    }

    /**
     * Delete pegawai (PGW-6: reject if attendance records exist).
     */
    public function destroy(string $nip): JsonResponse
    {
        $pegawai = Pegawai::withCount('absensi')->where('nip', $nip)->firstOrFail();

        if ($pegawai->absensi_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Penghapusan ditolak. Pegawai masih memiliki ' . $pegawai->absensi_count . ' catatan absensi bermasalah. Hapus catatan absensi terlebih dahulu.',
            ], 422);
        }

        $pegawai->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pegawai berhasil dihapus.',
        ]);
    }
}
