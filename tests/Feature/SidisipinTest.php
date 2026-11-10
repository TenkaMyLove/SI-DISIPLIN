<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\DivisiBagian;
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

        $this->admin = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        DivisiBagian::firstOrCreate(['nama' => 'Pelayanan Hukum'], ['nama' => 'Pelayanan Hukum', 'kode' => 'YANKUM']);
        DivisiBagian::firstOrCreate(['nama' => 'P3H'], ['nama' => 'P3H', 'kode' => 'P3H']);
        DivisiBagian::firstOrCreate(['nama' => 'Tata Usaha dan Umum'], ['nama' => 'Tata Usaha dan Umum', 'kode' => 'TU_UMUM']);
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
                'divisi' => 'Tata Usaha dan Umum',
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['nip']);
    }

    public function test_pegawai_divisi_must_be_one_of_the_three_categories(): void
    {
        $token = $this->admin->createToken('test_token')->plainTextToken;

        // Invalid category
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/pegawai', [
                'nip' => '199001012020011088',
                'nama' => 'Invalid Divisi',
                'divisi' => 'Keuangan',
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['divisi']);

        // Valid categories
        foreach (['Pelayanan Hukum', 'P3H', 'Tata Usaha dan Umum'] as $idx => $validDivisi) {
            $nip = '19900101202001109' . ($idx + 1);
            $res = $this->withHeader('Authorization', "Bearer {$token}")
                ->postJson('/api/pegawai', [
                    'nip' => $nip,
                    'nama' => "Pegawai {$validDivisi}",
                    'divisi' => $validDivisi,
                ]);
            $res->assertCreated();
        }
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
            'divisi' => 'Pelayanan Hukum',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/absensi', [
                'nip' => $pegawai->nip,
                'periode' => '2026-11-04',
                'jam_masuk' => '07:30',
                'jam_pulang' => '16:00',
                'status' => 'Tanpa Keterangan',
                'alasan' => 'Tidak masuk',
            ]);

        $response->assertCreated();
        $this->assertEquals(450, $response->json('data.durasi_menit'));
    }

    /**
     * Jadwal Kerja & Flex:
     * Senin: 07:30 - 16:00 (non flex)
     * Selasa - Kamis: 07:30 - 16:00 (flex) -> datang 07:36, pulang 16:06 memenuhi jam kerja (0 menit), pulang 16:00 defisit 6 menit
     * Jumat: 07:30 - 16:30 (non flex)
     */
    public function test_jadwal_kerja_dan_flex_logic(): void
    {
        $token = $this->admin->createToken('test_token')->plainTextToken;

        $pegawai = Pegawai::create([
            'nip' => '199001012020011003',
            'nama' => 'Dewi',
            'divisi' => 'P3H',
        ]);

        // 1. SENIN (2026-11-02, Non-Flex, Std: 07:30 - 16:00)
        // Datang 07:36, Pulang 16:06 -> Keterlambatan masuk 6 menit tetap dihitung
        $resSenin = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/absensi', [
                'nip' => $pegawai->nip,
                'periode' => '2026-11-02',
                'jam_masuk' => '07:36',
                'jam_pulang' => '16:06',
                'status' => 'Terlambat',
                'alasan' => 'Macet',
            ]);
        $resSenin->assertCreated();
        $this->assertEquals(6, $resSenin->json('data.durasi_menit'));

        // 2. SELASA (2026-11-03, Flex, Std: 07:30 - 16:00)
        // Skenario User: Datang 07:36, Pulang 16:06 -> Memenuhi jam kerja (0 menit)
        $resSelasaMemenuhi = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/absensi', [
                'nip' => $pegawai->nip,
                'periode' => '2026-11-03',
                'jam_masuk' => '07:36',
                'jam_pulang' => '16:06',
                'status' => 'Terlambat',
                'alasan' => 'Flex time dipenuhi',
            ]);
        $resSelasaMemenuhi->assertCreated();
        $this->assertEquals(0, $resSelasaMemenuhi->json('data.durasi_menit'));

        // Skenario User: Datang 07:36, Pulang 16:00 -> Defisit 6 menit
        $resSelasaDefisit = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/absensi', [
                'nip' => $pegawai->nip,
                'periode' => '2026-11-03',
                'jam_masuk' => '07:36',
                'jam_pulang' => '16:00',
                'status' => 'Terlambat',
                'alasan' => 'Pulang jam 16:00 defisit 6 menit',
            ]);
        $resSelasaDefisit->assertCreated();
        $this->assertEquals(6, $resSelasaDefisit->json('data.durasi_menit'));

        // 3. JUMAT (2026-11-06, Non-Flex, Std: 07:30 - 16:30)
        // Datang 07:30, Pulang 16:15 -> Pulang duluan 15 menit
        $resJumat = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/absensi', [
                'nip' => $pegawai->nip,
                'periode' => '2026-11-06',
                'jam_masuk' => '07:30',
                'jam_pulang' => '16:15',
                'status' => 'Pulang Duluan',
                'alasan' => 'Izin',
            ]);
        $resJumat->assertCreated();
        $this->assertEquals(15, $resJumat->json('data.durasi_menit'));
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
            'divisi' => 'Tata Usaha dan Umum',
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
        $pegawai1 = Pegawai::create(['nip' => '199001012020011005', 'nama' => 'Pegawai Bermasalah', 'divisi' => 'Tata Usaha dan Umum']);
        $pegawai2 = Pegawai::create(['nip' => '199001012020011006', 'nama' => 'Pegawai Rajin', 'divisi' => 'Tata Usaha dan Umum']);

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
        if (extension_loaded('gd')) {
            $pdfResponse = $this->get('/api/rekap/export-pdf?mode=bulan&bulan=2026-11');
            $pdfResponse->assertOk()
                ->assertHeader('content-type', 'application/pdf');
        }
    }

    /**
     * Test divisi_bagian table exists and associates with pegawai.
     */
    public function test_divisi_bagian_table_and_association(): void
    {
        $this->assertDatabaseHas('divisi_bagian', ['nama' => 'Pelayanan Hukum']);
        $this->assertDatabaseHas('divisi_bagian', ['nama' => 'P3H']);
        $this->assertDatabaseHas('divisi_bagian', ['nama' => 'Tata Usaha dan Umum']);

        $token = $this->admin->createToken('test_token')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/pegawai', [
                'nip' => '199001012020011099',
                'nama' => 'Pegawai Baru Divisi',
                'divisi' => 'Pelayanan Hukum',
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('pegawai', [
            'nip' => '199001012020011099',
            'divisi' => 'Pelayanan Hukum',
            'divisi_bagian_id' => 1,
        ]);
    }

    /**
     * Test admin can perform CRUD on divisi_bagian.
     */
    public function test_admin_divisi_crud_and_deletion_protection(): void
    {
        $token = $this->admin->createToken('test_token')->plainTextToken;

        // 1. List Divisi
        $resList = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/divisi');
        $resList->assertOk()
            ->assertJsonPath('success', true);

        // 2. Create New Divisi
        $resCreate = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/divisi', [
                'nama' => 'Divisi Keimigrasian Baru',
                'kode' => 'IMIGRASI',
                'deskripsi' => 'Unit Pelayanan Keimigrasian',
            ]);
        $resCreate->assertCreated()
            ->assertJsonPath('data.nama', 'Divisi Keimigrasian Baru');

        $divisiId = $resCreate->json('data.id');

        // 3. Update Divisi
        $resUpdate = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/divisi/{$divisiId}", [
                'nama' => 'Divisi Keimigrasian & Izin Tinggal',
                'kode' => 'IMI_IZIN',
                'deskripsi' => 'Deskripsi diperbarui',
            ]);
        $resUpdate->assertOk()
            ->assertJsonPath('data.nama', 'Divisi Keimigrasian & Izin Tinggal');

        // 4. Assign a pegawai to this divisi and try to delete (Must be rejected with 422)
        $pegawai = Pegawai::create([
            'nip' => '199201012020011088',
            'nama' => 'Staf Imigrasi',
            'divisi' => 'Divisi Keimigrasian & Izin Tinggal',
            'divisi_bagian_id' => $divisiId,
        ]);

        $resDeleteFail = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/divisi/{$divisiId}");
        $resDeleteFail->assertStatus(422)
            ->assertJsonPath('success', false);

        // 5. Remove pegawai from divisi and delete successfully
        $pegawai->delete();

        $resDeleteSuccess = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/divisi/{$divisiId}");
        $resDeleteSuccess->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('divisi_bagian', ['id' => $divisiId]);
    }

    /**
     * Test pengaturan jam kerja per hari (Senin, Selasa, Rabu, Kamis, Jumat).
     */
    public function test_pengaturan_per_hari_can_be_updated_and_retrieved(): void
    {
        $token = $this->admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/pengaturan', [
                'senin_masuk' => '07:30',
                'senin_pulang' => '16:00',
                'selasa_masuk' => '08:00',
                'selasa_pulang' => '16:30',
                'rabu_masuk' => '07:45',
                'rabu_pulang' => '16:15',
                'kamis_masuk' => '07:30',
                'kamis_pulang' => '16:00',
                'jumat_masuk' => '07:30',
                'jumat_pulang' => '17:00',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.jadwal.selasa.masuk', '08:00')
            ->assertJsonPath('data.jadwal.selasa.pulang', '16:30')
            ->assertJsonPath('data.jadwal.rabu.masuk', '07:45')
            ->assertJsonPath('data.jadwal.kamis.pulang', '16:00')
            ->assertJsonPath('data.jadwal.jumat.pulang', '17:00');

        $getRes = $this->getJson('/api/pengaturan');
        $getRes->assertOk()
            ->assertJsonPath('data.jadwal.selasa.masuk', '08:00')
            ->assertJsonPath('data.jadwal.rabu.masuk', '07:45')
            ->assertJsonPath('data.jadwal.jumat.pulang', '17:00');
    }
}
