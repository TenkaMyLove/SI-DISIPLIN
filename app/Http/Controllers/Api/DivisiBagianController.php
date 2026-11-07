<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DivisiBagian;
use App\Models\Pegawai;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DivisiBagianController extends Controller
{
    /**
     * Display a listing of divisi / bagian with assigned pegawai count.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DivisiBagian::withCount('pegawai');

        if ($request->filled('q')) {
            $keyword = $request->input('q');
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                  ->orWhere('kode', 'like', "%{$keyword}%")
                  ->orWhere('deskripsi', 'like', "%{$keyword}%");
            });
        }

        $divisi = $query->orderBy('nama', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $divisi,
        ]);
    }

    /**
     * Store a newly created divisi / bagian.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:divisi_bagian,nama'],
            'kode' => ['nullable', 'string', 'max:50'],
            'deskripsi' => ['nullable', 'string', 'max:255'],
        ], [
            'nama.required' => 'Nama Divisi / Bagian wajib diisi.',
            'nama.unique' => 'Nama Divisi / Bagian sudah terdaftar.',
            'nama.max' => 'Nama Divisi / Bagian maksimal 100 karakter.',
        ]);

        $divisi = DivisiBagian::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Divisi / Bagian berhasil ditambahkan.',
            'data' => $divisi,
        ], 201);
    }

    /**
     * Display the specified divisi / bagian.
     */
    public function show(int $id): JsonResponse
    {
        $divisi = DivisiBagian::withCount('pegawai')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $divisi,
        ]);
    }

    /**
     * Update the specified divisi / bagian.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $divisi = DivisiBagian::findOrFail($id);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique('divisi_bagian', 'nama')->ignore($id)],
            'kode' => ['nullable', 'string', 'max:50'],
            'deskripsi' => ['nullable', 'string', 'max:255'],
        ], [
            'nama.required' => 'Nama Divisi / Bagian wajib diisi.',
            'nama.unique' => 'Nama Divisi / Bagian sudah terdaftar.',
            'nama.max' => 'Nama Divisi / Bagian maksimal 100 karakter.',
        ]);

        $oldName = $divisi->nama;
        $divisi->update($validated);

        // Keep denormalized string column in pegawai table in sync
        if ($oldName !== $divisi->nama) {
            Pegawai::where('divisi_bagian_id', $divisi->id)
                ->orWhere('divisi', $oldName)
                ->update(['divisi' => $divisi->nama]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data Divisi / Bagian berhasil diperbarui.',
            'data' => $divisi,
        ]);
    }

    /**
     * Remove the specified divisi / bagian.
     * Prevents deletion if employees are still assigned.
     */
    public function destroy(int $id): JsonResponse
    {
        $divisi = DivisiBagian::withCount('pegawai')->findOrFail($id);

        if ($divisi->pegawai_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Penghapusan ditolak. Divisi "' . $divisi->nama . '" masih memiliki ' . $divisi->pegawai_count . ' pegawai terdaftar. Pindahkan atau hapus data pegawai terlebih dahulu.',
            ], 422);
        }

        $divisi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Divisi / Bagian berhasil dihapus.',
        ]);
    }
}
