<template>
  <div class="min-h-screen flex flex-col bg-neutral-surface pt-20 pb-16 print:pt-0 print:pb-0 overflow-x-hidden">
    <Navbar />

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-col min-w-0 max-w-full">
      <!-- Header: Sama seperti Header Admin -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
        <div>
          <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">
            Rekapitulasi Kehadiran Bermasalah
          </h1>
          <p class="text-xs sm:text-sm text-neutral-muted mt-0.5">
            Tinjauan rekapitulasi data absensi bermasalah pegawai dan total akumulasi kekurangan jam kerja.
          </p>
        </div>

        <div class="flex items-center space-x-2 shrink-0 no-print">
          <button
            @click="exportExcel"
            class="inline-flex items-center space-x-1.5 px-3 py-1.5 border border-neutral-border text-xs font-semibold text-navy bg-white hover:bg-neutral-50 transition-colors shadow-2xs"
          >
            <FileSpreadsheet class="w-4 h-4 text-emerald-700" />
            <span>Ekspor Excel</span>
          </button>
          <button
            @click="exportPdf"
            class="inline-flex items-center space-x-1.5 px-3 py-1.5 border border-neutral-border text-xs font-semibold text-navy bg-white hover:bg-neutral-50 transition-colors shadow-2xs"
          >
            <FileDown class="w-4 h-4 text-red-700" />
            <span>Ekspor PDF</span>
          </button>
          <button
            @click="printRekap"
            class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-navy text-white text-xs font-semibold hover:bg-navy-800 transition-colors shadow-2xs"
          >
            <Printer class="w-4 h-4" />
            <span>Cetak</span>
          </button>
        </div>
      </div>

      <!-- Filter Panel: Compact (Sama persis dengan Panel Admin) -->
      <div class="bg-white border border-neutral-border p-3 sm:px-4 sm:py-2.5 grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3 no-print shadow-2xs">
        <div>
          <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1">
            Mode Periode
          </label>
          <div class="grid grid-cols-3 border border-neutral-border p-0.5 bg-neutral-50 gap-1 mb-1.5">
            <button
              type="button"
              @click="setMode('tanggal')"
              class="py-1 text-xs text-center font-medium"
              :class="filters.mode === 'tanggal' ? 'bg-navy text-white font-semibold' : 'text-neutral-700 hover:bg-neutral-200'"
            >
              Harian
            </button>
            <button
              type="button"
              @click="setMode('minggu')"
              class="py-1 text-xs text-center font-medium"
              :class="filters.mode === 'minggu' ? 'bg-navy text-white font-semibold' : 'text-neutral-700 hover:bg-neutral-200'"
            >
              Mingguan
            </button>
            <button
              type="button"
              @click="setMode('bulan')"
              class="py-1 text-xs text-center font-medium"
              :class="filters.mode === 'bulan' ? 'bg-navy text-white font-semibold' : 'text-neutral-700 hover:bg-neutral-200'"
            >
              Bulanan
            </button>
          </div>
          <input
            v-if="filters.mode === 'tanggal'"
            v-model="filters.tanggal"
            type="date"
            class="w-full text-xs border border-neutral-border px-3 py-1.5 bg-white focus:outline-none focus:border-navy"
            @change="fetchRekap"
          />
          <div v-else-if="filters.mode === 'minggu'">
            <input
              v-model="filters.minggu"
              type="date"
              class="w-full text-xs border border-neutral-border px-3 py-1.5 bg-white focus:outline-none focus:border-navy"
              @change="fetchRekap"
            />
            <p class="text-[11px] text-neutral-500 mt-1">
              Rentang: <strong class="text-navy">{{ rekapData?.filter?.periode_label || 'Senin s/d Jumat' }}</strong>
            </p>
          </div>
          <input
            v-else
            v-model="filters.bulan"
            type="month"
            class="w-full text-xs border border-neutral-border px-3 py-1.5 bg-white focus:outline-none focus:border-navy"
            @change="fetchRekap"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1">
            Pegawai
          </label>
          <div class="mt-7">
            <CustomDropdown
              v-model="filters.nip"
              :options="[
                { value: '', label: 'Tampilkan Semua Pegawai' },
                ...pegawaiList.map(p => ({ value: p.nip, label: `${p.nama} (${p.nip})` }))
              ]"
              placeholder="Tampilkan Semua Pegawai"
              @change="fetchRekap"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1">
            Divisi / Bagian
          </label>
          <div class="mt-7">
            <CustomDropdown
              v-model="filters.divisi"
              :options="[
                { value: '', label: 'Semua Divisi / Bagian' },
                ...divisiOptions.map(d => ({ value: d, label: d }))
              ]"
              placeholder="Semua Divisi / Bagian"
              @change="fetchRekap"
            />
          </div>
        </div>
      </div>

      <!-- Stat Cards: Sama persis seperti Panel Admin (Pegawai Terkena Rekap & Total Pelanggaran) -->
      <div v-if="!loading && rekapData?.ringkasan" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3 no-print">
        <div class="bg-white border border-neutral-border p-3 shadow-2xs">
          <p class="text-[11px] font-semibold text-neutral-muted uppercase tracking-wider">Pegawai Terkena Rekap</p>
          <p class="text-xl font-bold text-navy mt-0.5">{{ rekapData.ringkasan.total_pegawai_bermasalah || 0 }} orang</p>
        </div>
        <div class="bg-white border border-neutral-border p-3 shadow-2xs">
          <p class="text-[11px] font-semibold text-neutral-muted uppercase tracking-wider">Total Pelanggaran</p>
          <p class="text-xl font-bold text-navy mt-0.5">{{ rekapData.ringkasan.total_catatan_absensi || 0 }} kali</p>
        </div>
      </div>

      <!-- Print-Only Official Report Header -->
      <div class="hidden print-only mb-6 text-center border-b-2 border-navy pb-4">
        <h1 class="text-xl font-bold uppercase tracking-wider text-navy">SIDISIPLIN - SISTEM INFORMASI DISIPLIN PEGAWAI</h1>
        <h2 class="text-sm font-semibold text-neutral-800">KEMENTERIAN HUKUM KALIMANTAN SELATAN</h2>
        <p class="text-xs text-neutral-600 mt-1">Laporan Rekapitulasi Kehadiran Bermasalah &bull; {{ rekapData?.filter?.periode_label }}</p>
      </div>

      <!-- Loading State: SKELETON LOADER -->
      <div v-if="loading" class="space-y-4">
        <SkeletonLoader type="table" :rows="8" :cols="10" />
      </div>

      <!-- Data Loaded Content -->
      <div v-else class="space-y-3 min-w-0 max-w-full">
        <!-- Empty State -->
        <div
          v-if="!rekapData?.pegawai || rekapData.pegawai.length === 0"
          class="bg-white border border-neutral-border p-12 text-center"
        >
          <div class="inline-flex p-3 bg-neutral-100 text-neutral-500 mb-3">
            <CheckCircle2 class="w-8 h-8 text-emerald-600" />
          </div>
          <h3 class="text-base font-bold text-navy">Tidak Ada Catatan Kehadiran Bermasalah</h3>
          <p class="text-xs text-neutral-muted mt-1 max-w-md mx-auto">
            Seluruh pegawai tercatat tertib kehadiran sesuai dengan filter dan periode yang dipilih.
          </p>
        </div>

        <!-- Table View of Problematic Attendance: Appears immediately in view without vertical scrolling -->
        <div v-else class="space-y-3 min-w-0 max-w-full">
          <div class="bg-white border border-neutral-border shadow-2xs max-w-full flex flex-col">
            <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-300px)]">
              <table class="w-full text-left border-separate border-spacing-0 text-xs">
                <thead class="sticky top-0 z-20 bg-navy text-white">
                  <tr class="bg-navy text-white border-b border-navy">
                    <th class="py-2.5 px-3 font-semibold text-center w-12 border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy whitespace-nowrap">No</th>
                    <th class="py-2.5 px-3 font-semibold w-28 border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy whitespace-nowrap">Tanggal</th>
                    <th class="py-2.5 px-3 font-semibold w-44 border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy whitespace-nowrap">NIP</th>
                    <th class="py-2.5 px-3 font-semibold border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy">Nama Pegawai</th>
                    <th class="py-2.5 px-3 font-semibold w-40 border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy">Divisi / Bagian</th>
                    <th class="py-2.5 px-2 font-semibold text-center w-20 border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy whitespace-nowrap">Masuk</th>
                    <th class="py-2.5 px-2 font-semibold text-center w-20 border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy whitespace-nowrap">Pulang</th>
                    <th class="py-2.5 px-3 font-semibold w-40 whitespace-nowrap border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy">Status</th>
                    <th class="py-2.5 px-3 font-semibold border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy">Alasan</th>
                    <th class="py-2.5 px-3 font-semibold text-right w-36 border-b-2 border-navy-800 sticky top-0 z-20 bg-navy whitespace-nowrap">Kekurangan</th>
                  </tr>
                </thead>
              <tbody class="divide-y divide-neutral-border bg-white">
                <template v-for="(p, pIdx) in rekapData.pegawai" :key="p.nip">
                  <!-- Row(s) for each attendance record of this employee -->
                  <tr
                    v-for="(r, rIdx) in p.rincian"
                    :key="r.id"
                    class="hover:bg-neutral-50 transition-colors"
                  >
                    <td class="py-2 px-3 text-center text-neutral-500 border-b border-r border-neutral-border bg-inherit">
                      {{ getGlobalRowNumber(pIdx, rIdx) }}
                    </td>
                    <td class="py-2 px-3 font-medium text-neutral-800 border-b border-r border-neutral-border whitespace-nowrap bg-inherit">
                      {{ r.periode_formatted }}
                    </td>
                    <td class="py-2 px-3 font-mono text-neutral-700 border-b border-r border-neutral-border whitespace-nowrap bg-inherit">
                      {{ p.nip }}
                    </td>
                    <td class="py-2 px-3 font-semibold text-navy border-b border-r border-neutral-border bg-inherit">
                      {{ p.nama }}
                    </td>
                    <td class="py-2 px-3 text-neutral-700 border-b border-r border-neutral-border bg-inherit">
                      {{ p.divisi }}
                    </td>
                    <td class="py-2 px-2 text-center text-neutral-800 border-b border-r border-neutral-border whitespace-nowrap bg-inherit">
                      {{ r.jam_masuk }}
                    </td>
                    <td class="py-2 px-2 text-center text-neutral-800 border-b border-r border-neutral-border whitespace-nowrap bg-inherit">
                      {{ r.jam_pulang }}
                    </td>
                    <td class="py-2 px-3 border-b border-r border-neutral-border whitespace-nowrap bg-inherit">
                      <span
                        class="inline-flex items-center justify-center px-2.5 py-0.5 text-[11px] font-semibold border whitespace-nowrap"
                        :class="getStatusBadgeClass(r.status)"
                      >
                        {{ r.status }}
                      </span>
                    </td>
                    <td class="py-2 px-3 text-neutral-700 border-b border-r border-neutral-border bg-inherit">
                      {{ r.alasan }}
                    </td>
                    <td class="py-2 px-3 text-right font-medium text-red-700 whitespace-nowrap border-b border-neutral-border bg-inherit">
                      {{ r.durasi_formatted }}
                    </td>
                  </tr>

                  <!-- Employee Subtotal Row (Merged into 1 column to eliminate excessive gap) -->
                  <tr class="bg-neutral-100 font-semibold border-b border-neutral-300">
                    <td colspan="10" class="py-2.5 px-4 text-right text-xs">
                      <span class="text-navy font-semibold mr-1.5">Total Kekurangan Jam Kerja:</span>
                      <span class="text-red-800 font-bold whitespace-nowrap">{{ p.total_durasi_formatted }}</span>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>

    <!-- Footer: Dikembalikan seperti awal dan di-anchor di dasar layar -->
    <footer class="fixed bottom-0 left-0 right-0 bg-navy text-neutral-300 py-2.5 text-xs text-center border-t-2 border-accent no-print z-40">
      <div class="max-w-7xl mx-auto px-4">
        <p class="font-medium text-white">SIDISIPLIN &bull; Sistem Informasi Disiplin Pegawai</p>
        <p class="mt-0.5 text-[11px] text-neutral-300">Kantor Wilayah Kementerian Hukum RI Provinsi Kalimantan Selatan</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import Navbar from '@/components/Navbar.vue'
import CustomDropdown from '@/components/CustomDropdown.vue'
import SkeletonLoader from '@/components/SkeletonLoader.vue'
import api from '@/composables/useApi'
import {
  FileSpreadsheet,
  FileDown,
  Printer,
  CheckCircle2,
} from 'lucide-vue-next'

const loading = ref(true)
const rekapData = ref(null)
const pegawaiList = ref([])

const filters = reactive({
  mode: 'bulan',
  tanggal: new Date().toISOString().substring(0, 10),
  minggu: new Date().toISOString().substring(0, 10),
  bulan: new Date().toISOString().substring(0, 7),
  nip: '',
  divisi: '',
})

const divisiOptions = [
  'Pelayanan Hukum',
  'P3H',
  'Tata Usaha dan Umum',
]

function setMode(mode) {
  filters.mode = mode
  fetchRekap()
}

function getStatusBadgeClass(status) {
  switch (status) {
    case 'Tanpa Keterangan':
      return 'bg-red-50 text-red-800 border-red-300'
    case 'Terlambat':
      return 'bg-amber-50 text-amber-800 border-amber-300'
    case 'Pulang Duluan':
      return 'bg-blue-50 text-blue-800 border-blue-300'
    default:
      return 'bg-neutral-100 text-neutral-800 border-neutral-300'
  }
}

function getGlobalRowNumber(pIdx, rIdx) {
  let count = 0
  for (let i = 0; i < pIdx; i++) {
    count += rekapData.value.pegawai[i].rincian.length
  }
  return count + rIdx + 1
}

async function fetchPegawaiList() {
  try {
    const res = await api.get('/pegawai/options')
    pegawaiList.value = res.data.data
  } catch (err) {
    console.error('Gagal memuat daftar pegawai:', err)
  }
}

async function fetchRekap() {
  loading.value = true
  try {
    const params = {
      mode: filters.mode,
      nip: filters.nip || undefined,
      divisi: filters.divisi || undefined,
    }

    if (filters.mode === 'tanggal') params.tanggal = filters.tanggal
    else if (filters.mode === 'minggu') params.minggu = filters.minggu
    else if (filters.mode === 'bulan') params.bulan = filters.bulan

    const res = await api.get('/rekap', { params })
    rekapData.value = res.data.data
  } catch (err) {
    console.error('Gagal memuat rekap:', err)
  } finally {
    loading.value = false
  }
}

function exportExcel() {
  const params = new URLSearchParams({
    mode: filters.mode,
  })
  if (filters.mode === 'tanggal') params.append('tanggal', filters.tanggal)
  if (filters.mode === 'minggu') params.append('minggu', filters.minggu)
  if (filters.mode === 'bulan') params.append('bulan', filters.bulan)
  if (filters.nip) params.append('nip', filters.nip)
  if (filters.divisi) params.append('divisi', filters.divisi)

  window.open(`/api/rekap/export-excel?${params.toString()}`, '_blank')
}

function exportPdf() {
  const params = new URLSearchParams({
    mode: filters.mode,
  })
  if (filters.mode === 'tanggal') params.append('tanggal', filters.tanggal)
  if (filters.mode === 'minggu') params.append('minggu', filters.minggu)
  if (filters.mode === 'bulan') params.append('bulan', filters.bulan)
  if (filters.nip) params.append('nip', filters.nip)
  if (filters.divisi) params.append('divisi', filters.divisi)

  window.open(`/api/rekap/export-pdf?${params.toString()}`, '_blank')
}

function printRekap() {
  window.print()
}

onMounted(() => {
  fetchPegawaiList()
  fetchRekap()
})
</script>
