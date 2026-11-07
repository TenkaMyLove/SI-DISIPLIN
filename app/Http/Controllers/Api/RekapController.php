<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Pegawai;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RekapController extends Controller
{
    /**
     * Resolve date range and description from request filters.
     */
    private function resolveDateRange(Request $request): array
    {
        $mode = $request->input('mode', 'bulan'); // default to 'bulan' or 'minggu' or 'tanggal'
        $startDate = null;
        $endDate = null;
        $label = '';

        if ($mode === 'tanggal') {
            $tgl = $request->input('tanggal', now()->format('Y-m-d'));
            $c = Carbon::parse($tgl);
            $startDate = $c->format('Y-m-d');
            $endDate = $c->format('Y-m-d');
            $label = 'Tanggal ' . $c->translatedFormat('d F Y');
        } elseif ($mode === 'minggu') {
            // Monday to Friday only (PRD Bab 5: minggu dihitung dari Senin sampai Jumat saja)
            $refDate = $request->input('minggu', now()->format('Y-m-d'));
            $c = Carbon::parse($refDate);
            $start = $c->copy()->startOfWeek(Carbon::MONDAY);
            $end = $start->copy()->addDays(4); // Friday
            $startDate = $start->format('Y-m-d');
            $endDate = $end->format('Y-m-d');
            $label = 'Minggu (' . $start->format('d-m-Y') . ' s/d ' . $end->format('d-m-Y') . ')';
        } else {
            // Mode 'bulan'
            $bulanStr = $request->input('bulan', now()->format('Y-m')); // 'YYYY-MM'
            $c = Carbon::parse($bulanStr . '-01');
            $startDate = $c->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $c->copy()->endOfMonth()->format('Y-m-d');
            $label = 'Bulan ' . $c->translatedFormat('F Y');
        }

        return [$startDate, $endDate, $label, $mode];
    }

    /**
     * Fetch filtered recap dataset.
     */
    private function getRekapData(Request $request): array
    {
        [$startDate, $endDate, $periodeLabel, $mode] = $this->resolveDateRange($request);

        $nipFilter = $request->input('nip');
        $divisiFilter = $request->input('divisi');

        // Query employees who have records in this period (REK-10)
        $pegawaiQuery = Pegawai::query();

        if ($nipFilter) {
            $pegawaiQuery->where('nip', $nipFilter);
        }

        if ($divisiFilter) {
            $pegawaiQuery->where('divisi', $divisiFilter);
        }

        // Only employees who have attendance records in the period!
        $pegawaiQuery->whereHas('absensi', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('periode', [$startDate, $endDate]);
        });

        // Eager load only the absensi records in this date range
        $pegawaiList = $pegawaiQuery->with(['absensi' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('periode', [$startDate, $endDate])
              ->orderBy('periode', 'asc')
              ->orderBy('jam_masuk', 'asc');
        }])->orderBy('divisi', 'asc')->orderBy('nama', 'asc')->get();

        $rekapPegawai = [];
        $grandTotalMenit = 0;
        $totalCatatan = 0;

        foreach ($pegawaiList as $pegawai) {
            $totalMenitPegawai = $pegawai->absensi->sum('durasi_menit');
            $grandTotalMenit += $totalMenitPegawai;
            $totalCatatan += $pegawai->absensi->count();

            $rincian = $pegawai->absensi->map(function ($item) {
                return [
                    'id' => $item->id,
                    'periode' => $item->periode->format('Y-m-d'),
                    'periode_formatted' => $item->periode->format('d-m-Y'),
                    'jam_masuk' => substr($item->jam_masuk, 0, 5),
                    'jam_pulang' => substr($item->jam_pulang, 0, 5),
                    'status' => $item->status,
                    'alasan' => $item->alasan ?? '-',
                    'durasi_menit' => $item->durasi_menit,
                    'durasi_formatted' => Absensi::formatMenit($item->durasi_menit),
                ];
            });

            $rekapPegawai[] = [
                'nip' => (string) $pegawai->nip,
                'nama' => $pegawai->nama,
                'divisi' => $pegawai->divisi,
                'total_durasi_menit' => $totalMenitPegawai,
                'total_durasi_formatted' => Absensi::formatMenit($totalMenitPegawai),
                'jumlah_catatan' => $pegawai->absensi->count(),
                'rincian' => $rincian,
            ];
        }

        return [
            'filter' => [
                'mode' => $mode,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'periode_label' => $label = $periodeLabel,
                'nip' => $nipFilter,
                'divisi' => $divisiFilter,
            ],
            'ringkasan' => [
                'total_pegawai_bermasalah' => count($rekapPegawai),
                'total_catatan_absensi' => $totalCatatan,
                'grand_total_menit' => $grandTotalMenit,
                'grand_total_formatted' => Absensi::formatMenit($grandTotalMenit),
            ],
            'pegawai' => $rekapPegawai,
        ];
    }

    /**
     * Get public recap data (REK-1).
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->getRekapData($request);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Export recap to Excel (REK-11).
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $data = $this->getRekapData($request);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Absensi Bermasalah');

        // Brand & Header Styling (Solid colors, no gradient, Deep Navy #031833)
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'SIDISIPLIN - SISTEM INFORMASI DISIPLIN PEGAWAI');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('031833');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', 'KEMENTERIAN HUKUM KALIMANTAN SELATAN');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('374151');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:I3');
        $sheet->setCellValue('A3', 'Periode: ' . $data['filter']['periode_label'] . ($data['filter']['divisi'] ? ' | Divisi / Bagian: ' . $data['filter']['divisi'] : ''));
        $sheet->getStyle('A3')->getFont()->setSize(10)->getColor()->setRGB('4B5563');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 5;
        $headers = ['No', 'Tanggal', 'NIP', 'Nama Pegawai', 'Divisi / Bagian', 'Jam Masuk', 'Jam Pulang', 'Status', 'Alasan', 'Kekurangan'];
        // Let's output columns A to J
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

        $sheet->fromArray($headers, null, "A{$row}");

        // Table Header Styling (Solid #031833 background, white text)
        $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '031833'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $row++;
        $no = 1;

        foreach ($data['pegawai'] as $p) {
            foreach ($p['rincian'] as $r) {
                $sheet->setCellValue("A{$row}", $no++);
                $sheet->setCellValue("B{$row}", $r['periode_formatted']);
                $sheet->setCellValueExplicit("C{$row}", (string) $p['nip'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue("D{$row}", $p['nama']);
                $sheet->setCellValue("E{$row}", $p['divisi']);
                $sheet->setCellValue("F{$row}", $r['jam_masuk']);
                $sheet->setCellValue("G{$row}", $r['jam_pulang']);
                $sheet->setCellValue("H{$row}", $r['status']);
                $sheet->setCellValue("I{$row}", $r['alasan']);
                $sheet->setCellValue("J{$row}", $r['durasi_formatted']);

                // Border
                $sheet->getStyle("A{$row}:J{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $row++;
            }

            // Subtotal row per employee
            $sheet->mergeCells("A{$row}:I{$row}");
            $sheet->setCellValue("A{$row}", "Total Kekurangan: {$p['nama']} ({$p['nip']})");
            $sheet->setCellValue("J{$row}", $p['total_durasi_formatted']);
            $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F3F4F6'],
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $row++;
        }

        // Auto width
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Rekap_Absensi_SIDISIPLIN_' . date('Ymd_His') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export recap to PDF (REK-11).
     */
    public function exportPdf(Request $request)
    {
        $data = $this->getRekapData($request);

        $pdf = Pdf::loadView('exports.rekap-pdf', [
            'data' => $data,
        ])->setPaper('a4', 'landscape');

        $filename = 'Rekap_Absensi_SIDISIPLIN_' . date('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }
}
