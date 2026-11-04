<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidisipinTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create single admin user
        $this->admin = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => 'admin123',
        ]);
    }

    /**
     * AUTH-1: Admin dapat masuk ke sistem melalui halaman login menggunakan kredensial akun Admin.
     */
    public function test_admin_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'token',
                'user' => ['id', 'name', 'username'],
            ]);
    }

    public function test_admin_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => 'admin',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    /**
     * AUTH-4: Seluruh menu pengelolaan hanya dapat diakses setelah login.
     */
    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $this->getJson('/api/pegawai')->assertUnauthorized();
        $this->postJson('/api/pegawai', [])->assertUnauthorized();
        $this->getJson('/api/absensi')->assertUnauthorized();
        $this->postJson('/api/absensi', [])->assertUnauthorized();
    }

    /**
     * AUTH-5: Admin dapat mengganti kata sandi akunnya setelah login.
     */
    public function test_admin_can_change_password(): void
    {
        $token = $this->admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/change-password', [
                'current_password' => 'admin123',
                'new_password' => 'newpassword123',
                'new_password_confirmation' => 'newpassword123',
            ]);

        $response->assertOk();

        // Verify old password doesn't work, new does
        $this->postJson('/api/auth/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ])->assertStatus(422);

        $this->postJson('/api/auth/login', [
            'username' => 'admin',
            'password' => 'newpassword123',
        ])->assertOk();
    }

    /**
     * PGW-1, PGW-2, PGW-3: NIP 18 digit, Nama, Divisi dari 9 pilihan.
     */
    public function test_admin_can_create_pegawai_with_18_digit_nip_and_divisi(): void
    {
        $token = $this->admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/pegawai', [
                'nip' => '198503152010121001',
                'nama' => 'Ahmad Fauzi, S.H.',
                'divisi' => 'Pelayanan Hukum',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('pegawai', [
            'nip' => '198503152010121001',
            'nama' => 'Ahmad Fauzi, S.H.',
            'divisi' => 'Pelayanan Hukum',
        ]);
    }

    public function test_pegawai_nip_must_be_18_digits(): void
    {
        $token = $this->admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/pegawai', [
                'nip' => '12345', // Not 18 digits
                'nama' => 'Testing',
                'divisi' => 'SDM',
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['nip']);
    }

    /**
     * PGW-6: Penghapusan ditolak jika pegawai masih memiliki catatan absensi.
     */
    public function test_cannot_delete_pegawai_with_attendance_records(): void
    {
        $token = $this->admin->createToken('test_token')->plainTextToken;

        $pegawai = Pegawai::create([
            'nip' => '199001012020011001',
            'nama' => 'Budi',
            'divisi' => 'Tata Usaha dan Umum',
        ]);

        Absensi::create([
            'nip' => $pegawai->nip,
            'periode' => '2026-11-04',
            'jam_masuk' => '08:30:00',
            'jam_pulang' => '16:00:00',
            'status' => 'Terlambat',
            'alasan' => 'Macet',
            'durasi_menit' => 30,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/pegawai/{$pegawai->nip}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('pegawai', ['nip' => $pegawai->nip]);
    }

    /**
     * ABS-5: Tanpa Keterangan otomatis 7,5 jam (450 menit).
     */
    public function test_tanpa_keterangan_auto_calculates_450_minutes_duration(): void
    {
        $token = $this->admin->createToken('test_token')->plainTextToken;

        $pegawai = Pegawai::create([
            'nip' => '199001012020011002',
            'nama' => 'Siti',
            'divisi' => 'Humas',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/absensi', [
                'nip' => $pegawai->nip,
                'periode' => '2026-11-04',
                'jam_masuk' => '08:00',
                'jam_pulang' => '16:00',
                'status' => 'Tanpa Keterangan',
                'alasan' => 'Tidak masuk',
            ]);

        $response->assertCreated();
        $this->assertEquals(450, $response->json('data.durasi_menit'));
    }

    /**
     * ABS-6: Terlambat dihitung dari selisih jam standar (08:00 - 16:00).
     */
    public function test_terlambat_calculates_duration_from_standard_hours(): void
    {
        $token = $this->admin->createToken('test_token')->plainTextToken;

        $pegawai = Pegawai::create([
            'nip' => '199001012020011003',
            'nama' => 'Dewi',
            'divisi' => 'Perencanaan',
        ]);

        // Masuk 08:35, Pulang 16:00 -> 35 menit
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/absensi', [
                'nip' => $pegawai->nip,
                'periode' => '2026-11-04',
                'jam_masuk' => '08:35',
                'jam_pulang' => '16:00',
                'status' => 'Terlambat',
                'alasan' => 'Macet',
            ]);

        $response->assertCreated();
        $this->assertEquals(35, $response->json('data.durasi_menit'));
    }

    /**
     * ABS-14: Jam Pulang harus lebih besar dari Jam Masuk.
     */
    public function test_jam_pulang_must_be_greater_than_jam_masuk(): void
    {
        $token = $this->admin->createToken('test_token')->plainTextToken;

        $pegawai = Pegawai::create([
            'nip' => '199001012020011004',
            'nama' => 'Rizky',
            'divisi' => 'Kekayaan Intelektual',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/absensi', [
                'nip' => $pegawai->nip,
                'periode' => '2026-11-04',
                'jam_masuk' => '16:00',
                'jam_pulang' => '08:00', // Earlier than masuk
                'status' => 'Pulang Duluan',
                'alasan' => 'Error',
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['jam_pulang']);
    }

    /**
     * REK-8 & REK-9: Format akumulasi dikonversi bertingkat tanpa satuan bernilai nol.
     * PRD Contoh:
     * 1.472 menit -> "1 hari 32 menit"
     * 1.647 menit -> "1 hari 3 jam 27 menit"
     */
    public function test_duration_formatting_matches_prd_examples(): void
    {
        $this->assertEquals('1 hari 32 menit', Absensi::formatMenit(1472));
        $this->assertEquals('1 hari 3 jam 27 menit', Absensi::formatMenit(1647));
        $this->assertEquals('2 jam', Absensi::formatMenit(120));
        $this->assertEquals('45 menit', Absensi::formatMenit(45));
        $this->assertEquals('0 menit', Absensi::formatMenit(0));
    }

    /**
     * REK-1: Rekap dapat dibuka publik tanpa login.
     * REK-10: Sistem hanya menampilkan pegawai yang memiliki catatan pada periode dan filter yang dipilih.
     */
    public function test_public_rekap_is_accessible_and_filters_correctly(): void
    {
        $pegawai1 = Pegawai::create(['nip' => '199001012020011005', 'nama' => 'Pegawai Bermasalah', 'divisi' => 'SDM']);
        $pegawai2 = Pegawai::create(['nip' => '199001012020011006', 'nama' => 'Pegawai Rajin', 'divisi' => 'SDM']);

        // Only pegawai1 has problem
        Absensi::create([
            'nip' => $pegawai1->nip,
            'periode' => '2026-11-04',
            'jam_masuk' => '08:45:00',
            'jam_pulang' => '16:00:00',
            'status' => 'Terlambat',
            'alasan' => 'Kesiangan',
            'durasi_menit' => 45,
        ]);

        $response = $this->getJson('/api/rekap?mode=tanggal&tanggal=2026-11-04');

        $response->assertOk();
        $pegawaiInRekap = collect($response->json('data.pegawai'));

        // REK-10: Only pegawai1 is present! pegawai2 is omitted.
        $this->assertCount(1, $pegawaiInRekap);
        $this->assertEquals('199001012020011005', $pegawaiInRekap->first()['nip']);
        $this->assertEquals('45 menit', $pegawaiInRekap->first()['total_durasi_formatted']);
    }

    /**
     * REK-11: Ekspor Excel & PDF endpoint availability.
     */
    public function test_export_endpoints_respond_with_files(): void
    {
        $pegawai = Pegawai::create(['nip' => '199001012020011007', 'nama' => 'Kartika', 'divisi' => 'P3H']);
        Absensi::create([
            'nip' => $pegawai->nip,
            'periode' => '2026-11-04',
            'jam_masuk' => '08:20:00',
            'jam_pulang' => '16:00:00',
            'status' => 'Terlambat',
            'alasan' => 'Hujan lebat',
            'durasi_menit' => 20,
        ]);

        // Excel Export
        $excelResponse = $this->get('/api/rekap/export-excel?mode=bulan&bulan=2026-11');
        $excelResponse->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // PDF Export
        $pdfResponse = $this->get('/api/rekap/export-pdf?mode=bulan&bulan=2026-11');
        $pdfResponse->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
