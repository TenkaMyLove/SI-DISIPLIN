# SI-DISIPLIN (Sistem Informasi Disiplin Pegawai)

**SI-DISIPLIN** adalah aplikasi web untuk pencatatan dan monitoring disiplin kehadiran pegawai di lingkungan Kantor Wilayah Kementerian Hukum Kalimantan Selatan (Kemenkumham Kalsel).

Aplikasi ini dirancang untuk memudahkan Bagian Kepegawaian dan Tata Usaha dalam merekap dan mengidentifikasi pelanggaran kehadiran (Terlambat, Pulang Cepat, dan Tanpa Keterangan) dengan perhitungan durasi otomatis sesuai jam kerja standar operasional.

---

## 🛠️ Tech Stack

- **Backend**: Laravel 11.x (PHP 8.2+)
- **Frontend**: Vue 3 (Composition API, `<script setup>`) + Vue Router 4
- **Styling**: Tailwind CSS + Lucide Icons
- **Build Tool**: Vite
- **Database**: MySQL / MariaDB (SQLite untuk automated testing)
- **Authentication**: Laravel Sanctum (Single Admin Account Session)
- **Exporting**: Fast-Excel (XLSX & CSV)

---

## 🚀 Fitur Utama

1. **Autentikasi Administrator Tunggal**
   - Single admin account tanpa registrasi mandiri.
   - Fitur ganti kata sandi admin.
2. **Dashboard Pemantauan Disiplin**
   - Kartu statistik total pegawai, pelanggaran hari ini, dan total menit defisit jam kerja.
   - Tabel 5 pelanggaran terbaru dengan badge status yang jelas.
3. **Manajemen Data Pegawai (CRUD)**
   - NIP validasi ketat 18 digit angka unik.
   - Nama lengkap beserta gelar.
   - Divisi / Subbag di lingkungan Kanwil.
   - Proteksi integritas data: pegawai dengan riwayat absensi tidak dapat dihapus sembarangan.
4. **Pencatatan Presensi & Pelanggaran**
   - Pencatatan jenis status: `Terlambat`, `Pulang Duluan`, dan `Tanpa Keterangan`.
   - Perhitungan otomatis durasi menit:
     - Terlambat: selisih `jam_masuk` terhadap `jam_masuk_standar` (default 08:00).
     - Pulang Duluan: selisih `jam_pulang_standar` (default 16:00) terhadap `jam_pulang`.
     - Tanpa Keterangan: otomatis dihitung penuh 450 menit (7.5 jam kerja efektif).
   - Validasi integritas waktu: `jam_pulang` tidak boleh lebih awal dari `jam_masuk`.
5. **Rekapitulasi Disiplin Pegawai (Publik & Admin)**
   - Rekapitulasi per pegawai: NIP, nama, divisi, jumlah terlambat, jumlah pulang duluan, jumlah tanpa keterangan, dan total menit defisit (diformat dalam `... Jam ... Menit`).
   - Filter rentang tanggal awal dan akhir (`start_date` & `end_date`).
   - Pencarian berdasarkan nama / NIP / divisi.
   - Modal detail riwayat pelanggaran setiap pegawai.
   - Export laporan ke format **Excel (.xlsx)** dan **CSV**.
6. **Pengaturan Jam Kerja Standar**
   - Penyesuaian `jam_masuk_standar` dan `jam_pulang_standar` yang tersimpan di database.

---

## 📋 Panduan Instalasi Lokal

### 1. Clone Repository
```bash
git clone https://github.com/TenkaMyLove/SI-DISIPLIN.git
cd SI-DISIPLIN
```

### 2. Install Dependensi
```bash
composer install
npm install
```

### 3. Konfigurasi Environment
Salin file `.env.example` menjadi `.env` dan atur koneksi database:
```bash
cp .env.example .env
php artisan key:generate
```
Pastikan pengaturan database di `.env` sudah sesuai:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sidisiplin
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrasi & Seeder Database
```bash
php artisan migrate --seed
```
*Seeder akan membuat akun admin default, pengaturan jam operasional standar, dan contoh data pegawai serta absensi.*

**Kredensial Admin Default:**
- **Username**: `admin`
- **Password**: `admin123`

### 5. Build Asset & Jalankan Aplikasi
```bash
npm run build
php artisan serve
```
Buka browser di `http://127.0.0.1:8000`.

---

## 🧪 Menjalankan Pengujian (Automated Tests)
Semua unit & feature test berjalan pada database in-memory SQLite:
```bash
php artisan test
```

---

## ⚖️ Lisensi & Hak Cipta
Dikembangkan untuk Kantor Wilayah Kementerian Hukum Kalimantan Selatan.
Hak Cipta Dilindungi Undang-Undang.
