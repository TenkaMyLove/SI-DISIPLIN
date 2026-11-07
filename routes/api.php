<?php

use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DivisiBagianController;
use App\Http\Controllers\Api\PegawaiController;
use App\Http\Controllers\Api\PengaturanController;
use App\Http\Controllers\Api\RekapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (Read-Only / Auth)
|--------------------------------------------------------------------------
*/
Route::post('/auth/login', [AuthController::class, 'login']);

// Public Attendance Recap & Export (PRD REK-1, REK-11, REK-12)
Route::get('/rekap', [RekapController::class, 'index']);
Route::get('/rekap/export-excel', [RekapController::class, 'exportExcel']);
Route::get('/rekap/export-pdf', [RekapController::class, 'exportPdf']);

// Autocomplete and options for search filter
Route::get('/pegawai/options', [PegawaiController::class, 'options']);
Route::get('/pengaturan', [PengaturanController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Authenticated Admin Routes (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Auth Management
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

    // Pegawai Management (CRUD)
    Route::apiResource('pegawai', PegawaiController::class);

    // Divisi / Bagian Management (CRUD)
    Route::apiResource('divisi', DivisiBagianController::class);

    // Absensi Bermasalah Management (CRUD)
    Route::post('/absensi/hitung-durasi', [AbsensiController::class, 'hitungDurasi']);
    Route::apiResource('absensi', AbsensiController::class);

    // Pengaturan Jam Standar
    Route::put('/pengaturan', [PengaturanController::class, 'update']);
});
