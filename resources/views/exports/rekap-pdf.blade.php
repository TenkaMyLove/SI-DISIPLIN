<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi Bermasalah - SIDISIPLIN</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 1.5cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #1F2937;
            background-color: #FFFFFF;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #031833;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header h1 {
            font-size: 16px;
            color: #031833;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h2 {
            font-size: 13px;
            color: #374151;
            margin: 0 0 6px 0;
            font-weight: normal;
        }
        .header p {
            font-size: 11px;
            color: #4B5563;
            margin: 0;
        }
        .info-bar {
            margin-bottom: 12px;
            font-size: 11px;
            color: #374151;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        th {
            background-color: #031833;
            color: #FFFFFF;
            font-weight: bold;
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #031833;
            font-size: 10px;
            text-transform: uppercase;
        }
        td {
            padding: 6px;
            border: 1px solid #D1D5DB;
            font-size: 10px;
            vertical-align: top;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .subtotal-row {
            background-color: #F3F4F6;
            font-weight: bold;
        }
        .grandtotal-row {
            background-color: #E5E7EB;
            font-weight: bold;
            color: #031833;
            font-size: 11px;
        }
        .footer {
            margin-top: 24px;
            width: 100%;
        }
        .footer-table {
            width: 100%;
            border: none;
        }
        .footer-table td {
            border: none;
            padding: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%; border: none; margin-bottom: 0;">
            <tr>
                <td style="width: 60px; border: none; vertical-align: middle; padding: 0;">
                    <img src="{{ public_path('images/logo-pengayoman.png') }}" style="height: 52px;" alt="Logo Pengayoman" />
                </td>
                <td style="border: none; vertical-align: middle; padding-left: 14px; text-align: left;">
                    <h1>SIDISIPLIN &mdash; Sistem Informasi Disiplin Pegawai</h1>
                    <h2>Kementerian Hukum Kalimantan Selatan</h2>
                    <p>Laporan Rekapitulasi Kehadiran Bermasalah &bull; {{ $data['filter']['periode_label'] }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="info-bar">
        <strong>Filter Aktif:</strong> {{ $data['filter']['periode_label'] }}
        @if(!empty($data['filter']['divisi']))
            | <strong>Divisi / Bagian:</strong> {{ $data['filter']['divisi'] }}
        @endif
        | <strong>Total Pegawai:</strong> {{ $data['ringkasan']['total_pegawai_bermasalah'] }} orang
        | <strong>Total Catatan:</strong> {{ $data['ringkasan']['total_catatan_absensi'] }} kali
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 25px;">No</th>
                <th class="text-center" style="width: 65px;">Tanggal</th>
                <th style="width: 120px;">NIP</th>
                <th style="width: 130px;">Nama Pegawai</th>
                <th style="width: 110px;">Divisi / Bagian</th>
                <th class="text-center" style="width: 50px;">Masuk</th>
                <th class="text-center" style="width: 50px;">Pulang</th>
                <th style="width: 90px;">Status</th>
                <th>Alasan</th>
                <th class="text-right" style="width: 85px;">Kekurangan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($data['pegawai'] as $p)
                @foreach($p['rincian'] as $r)
                    <tr>
                        <td class="text-center">{{ $no++ }}</td>
                        <td class="text-center">{{ $r['periode_formatted'] }}</td>
                        <td>{{ $p['nip'] }}</td>
                        <td><strong>{{ $p['nama'] }}</strong></td>
                        <td>{{ $p['divisi'] }}</td>
                        <td class="text-center">{{ $r['jam_masuk'] }}</td>
                        <td class="text-center">{{ $r['jam_pulang'] }}</td>
                        <td>{{ $r['status'] }}</td>
                        <td>{{ $r['alasan'] }}</td>
                        <td class="text-right">{{ $r['durasi_formatted'] }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td colspan="9" class="text-right">Total Kekurangan Jam Kerja &mdash; {{ $p['nama'] }}:</td>
                    <td class="text-right">{{ $p['total_durasi_formatted'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 24px;">Tidak ada catatan absensi bermasalah pada periode dan filter yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer-table">
        <tr>
            <td style="width: 70%;">
                <small style="color: #6B7280;">Dicetak secara otomatis melalui Sistem Informasi Disiplin Pegawai (SIDISIPLIN) pada {{ now()->translatedFormat('d F Y H:i') }} WITA.</small>
            </td>
            <td style="width: 30%; text-align: center;">
                <p>Banjarmasin, {{ now()->translatedFormat('d F Y') }}</p>
                <p style="margin-top: 40px; font-weight: bold; text-decoration: underline;">Administrator Disiplin</p>
                <p style="margin: 0; color: #4B5563;">Kementerian Hukum Kalimantan Selatan</p>
            </td>
        </tr>
    </table>
</body>
</html>
