<template>
  <div class="min-h-screen flex flex-col bg-neutral-surface">
    <Navbar />

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Title Section without pill/emoji -->
      <div class="mb-6 border-b border-neutral-border pb-5">
        <h1 class="text-2xl sm:text-3xl font-bold text-navy tracking-tight">
          Rekapitulasi Kehadiran Bermasalah
        </h1>
        <p class="text-sm text-neutral-muted mt-1">
          Kementerian Hukum Kalimantan Selatan &bull; Zona Waktu WITA
        </p>
      </div>

      <!-- Filter Section (No card-in-card, clean solid panel) -->
      <div class="bg-white border border-neutral-border p-5 mb-6 no-print">
        <div class="flex items-center justify-between border-b border-neutral-border pb-3 mb-4">
          <h2 class="text-sm font-bold text-navy uppercase tracking-wider flex items-center space-x-2">
            <Filter class="w-4 h-4 text-navy" />
            <span>Filter Rekapitulasi</span>
          </h2>
          <button
            @click="resetFilter"
            class="text-xs text-neutral-muted hover:text-navy underline focus:outline-none"
          >
            Reset Filter
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <!-- 1. Mode Periode Selector -->
          <div>
            <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1.5">
              Mode Periode
            </label>
            <div class="grid grid-cols-3 border border-neutral-border p-1 bg-neutral-50 gap-1">
              <button
                type="button"
                @click="setMode('tanggal')"
                class="py-1.5 text-xs font-medium text-center transition-colors"
                :class="filters.mode === 'tanggal' ? 'bg-navy text-white font-semibold' : 'text-neutral-700 hover:bg-neutral-200'"
              >
                Harian
              </button>
              <button
                type="button"
                @click="setMode('minggu')"
                class="py-1.5 text-xs font-medium text-center transition-colors"
                :class="filters.mode === 'minggu' ? 'bg-navy text-white font-semibold' : 'text-neutral-700 hover:bg-neutral-200'"
              >
                Mingguan
              </button>
              <button
                type="button"
                @click="setMode('bulan')"
                class="py-1.5 text-xs font-medium text-center transition-colors"
                :class="filters.mode === 'bulan' ? 'bg-navy text-white font-semibold' : 'text-neutral-700 hover:bg-neutral-200'"
              >
                Bulanan
              </button>
            </div>

            <!-- Value input based on active mode -->
            <div class="mt-2.5">
              <input
                v-if="filters.mode === 'tanggal'"
                v-model="filters.tanggal"
                type="date"
                class="w-full text-sm border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy"
                @change="fetchRekap"
              />
              <div v-else-if="filters.mode === 'minggu'" class="space-y-1">
                <input
                  v-model="filters.minggu"
                  type="date"
                  class="w-full text-sm border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy"
                  @change="fetchRekap"
                />
                <p class="text-[11px] text-neutral-muted">Minggu dihitung dari hari Senin sampai Jumat.</p>
              </div>
              <input
                v-else
                v-model="filters.bulan"
                type="month"
                class="w-full text-sm border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy"
                @change="fetchRekap"
              />
            </div>
          </div>

          <!-- 2. Pegawai Autocomplete Filter -->
          <div class="relative">
            <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1.5">
              Pegawai (Nama / NIP)
            </label>
            <div class="relative">
              <input
                v-model="pegawaiSearch"
                type="text"
                placeholder="Semua Pegawai (Ketik untuk cari)..."
                class="w-full text-sm border border-neutral-border pl-9 pr-8 py-2 bg-white focus:outline-none focus:border-navy"
                @focus="showPegawaiDropdown = true"
                @input="handlePegawaiSearchInput"
              />
              <Search class="w-4 h-4 text-neutral-400 absolute left-3 top-2.5" />
              <button
                v-if="selectedPegawai"
                @click="clearSelectedPegawai"
                type="button"
                class="absolute right-2.5 top-2.5 text-neutral-400 hover:text-navy"
              >
                <X class="w-4 h-4" />
              </button>
            </div>

            <!-- Autocomplete Results Dropdown -->
            <div
              v-if="showPegawaiDropdown"
              class="absolute z-20 w-full mt-1 bg-white border border-neutral-border shadow-lg max-h-60 overflow-y-auto divide-y divide-neutral-100"
            >
              <div
                @click="selectPegawai(null)"
                class="p-2.5 text-xs hover:bg-neutral-50 cursor-pointer font-semibold text-navy flex justify-between items-center"
              >
                <span>Tampilkan Semua Pegawai</span>
                <Check v-if="!filters.nip" class="w-3.5 h-3.5 text-navy" />
              </div>
              <div
                v-for="p in filteredPegawaiOptions"
                :key="p.nip"
                @click="selectPegawai(p)"
                class="p-2.5 text-xs hover:bg-neutral-50 cursor-pointer flex justify-between items-center"
              >
                <div>
                  <p class="font-medium text-navy">{{ p.nama }}</p>
                  <p class="text-[11px] text-neutral-muted">NIP: {{ p.nip }} &bull; {{ p.divisi }}</p>
                </div>
                <Check v-if="filters.nip === p.nip" class="w-3.5 h-3.5 text-navy" />
              </div>
              <div v-if="filteredPegawaiOptions.length === 0" class="p-3 text-xs text-neutral-muted text-center">
                Pegawai tidak ditemukan.
              </div>
            </div>
          </div>

          <!-- 3. Divisi Filter -->
          <div>
            <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1.5">
              Divisi / Unit Kerja
            </label>
            <select
              v-model="filters.divisi"
              class="w-full text-sm border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy"
              @change="fetchRekap"
            >
              <option value="">Semua Divisi</option>
              <option v-for="d in divisiOptions" :key="d" :value="d">
                {{ d }}
              </option>
            </select>
          </div>
        </div>

        <!-- Action Buttons: Apply, Export Excel, Export PDF, Print -->
        <div class="mt-5 pt-4 border-t border-neutral-border flex flex-wrap items-center justify-between gap-3">
          <div class="text-xs text-neutral-600">
            <strong>Periode Terpilih:</strong> {{ rekapData?.filter?.periode_label || '-' }}
          </div>

          <div class="flex items-center space-x-2">
            <button
              @click="exportExcel"
              class="inline-flex items-center space-x-1.5 px-3 py-2 border border-neutral-border text-xs font-medium text-navy bg-white hover:bg-neutral-50 transition-colors"
              title="Unduh format spreadsheet Excel"
            >
              <FileSpreadsheet class="w-4 h-4 text-emerald-700" />
              <span>Ekspor Excel</span>
            </button>

            <button
              @click="exportPdf"
              class="inline-flex items-center space-x-1.5 px-3 py-2 border border-neutral-border text-xs font-medium text-navy bg-white hover:bg-neutral-50 transition-colors"
              title="Unduh format dokumen PDF"
            >
              <FileDown class="w-4 h-4 text-red-700" />
              <span>Ekspor PDF</span>
            </button>

            <button
              @click="printRekap"
              class="inline-flex items-center space-x-1.5 px-3 py-2 border border-neutral-border text-xs font-medium text-navy bg-white hover:bg-neutral-50 transition-colors"
              title="Cetak halaman rekap"
            >
              <Printer class="w-4 h-4 text-navy" />
              <span>Cetak</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Print-Only Official Report Header -->
      <div class="hidden print-only mb-6 text-center border-b-2 border-navy pb-4">
        <h1 class="text-xl font-bold uppercase tracking-wider text-navy">SIDISIPLIN - SISTEM INFORMASI DISIPLIN PEGAWAI</h1>
        <h2 class="text-sm font-semibold text-neutral-800">KEMENTERIAN HUKUM KALIMANTAN SELATAN</h2>
        <p class="text-xs text-neutral-600 mt-1">Laporan Rekapitulasi Kehadiran Bermasalah &bull; {{ rekapData?.filter?.periode_label }}</p>
      </div>

      <!-- Loading State: SKELETON LOADER (PRD requirement) -->
      <div v-if="loading" class="space-y-4">
        <SkeletonLoader type="stat" :rows="4" />
        <SkeletonLoader type="table" :rows="8" :cols="10" />
      </div>

      <!-- Data Loaded Content -->
      <div v-else class="space-y-6">
        <!-- Summary Stats Strip (Solid borders, no card in card) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 no-print">
          <div class="bg-white border border-neutral-border p-4">
            <p class="text-xs font-semibold text-neutral-muted uppercase tracking-wider">Pegawai Bermasalah</p>
            <p class="text-2xl font-bold text-navy mt-1">
              {{ rekapData?.ringkasan?.total_pegawai_bermasalah || 0 }}
              <span class="text-xs font-normal text-neutral-muted">orang</span>
            </p>
          </div>
          <div class="bg-white border border-neutral-border p-4">
            <p class="text-xs font-semibold text-neutral-muted uppercase tracking-wider">Total Catatan</p>
            <p class="text-2xl font-bold text-navy mt-1">
              {{ rekapData?.ringkasan?.total_catatan_absensi || 0 }}
              <span class="text-xs font-normal text-neutral-muted">kali pelanggaran</span>
            </p>
          </div>
          <div class="bg-white border border-neutral-border p-4 sm:col-span-2">
            <p class="text-xs font-semibold text-neutral-muted uppercase tracking-wider">Akumulasi Kekurangan Jam Kerja</p>
            <p class="text-2xl font-bold text-navy mt-1 text-red-700">
              {{ rekapData?.ringkasan?.grand_total_formatted || '0 menit' }}
            </p>
            <p class="text-[11px] text-neutral-muted mt-0.5">Konversi bertingkat: 60 menit = 1 jam, 24 jam = 1 hari</p>
          </div>
        </div>

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

        <!-- Table View of Problematic Attendance -->
        <div v-else class="space-y-6">
          <div class="bg-white border border-neutral-border overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-navy text-white border-b border-navy">
                  <th class="py-3 px-3 font-semibold text-center w-12 border-r border-navy-800">No</th>
                  <th class="py-3 px-3 font-semibold w-28 border-r border-navy-800">Tanggal</th>
                  <th class="py-3 px-3 font-semibold w-44 border-r border-navy-800">NIP</th>
                  <th class="py-3 px-3 font-semibold border-r border-navy-800">Nama Pegawai</th>
                  <th class="py-3 px-3 font-semibold w-40 border-r border-navy-800">Divisi</th>
                  <th class="py-3 px-2 font-semibold text-center w-20 border-r border-navy-800">Masuk</th>
                  <th class="py-3 px-2 font-semibold text-center w-20 border-r border-navy-800">Pulang</th>
                  <th class="py-3 px-3 font-semibold w-36 border-r border-navy-800">Status</th>
                  <th class="py-3 px-3 font-semibold border-r border-navy-800">Alasan</th>
                  <th class="py-3 px-3 font-semibold text-right w-36">Kekurangan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-neutral-border">
                <template v-for="(p, pIdx) in rekapData.pegawai" :key="p.nip">
                  <!-- Row(s) for each attendance record of this employee -->
                  <tr
                    v-for="(r, rIdx) in p.rincian"
                    :key="r.id"
                    class="hover:bg-neutral-50 transition-colors"
                  >
                    <td class="py-2.5 px-3 text-center text-neutral-500 border-r border-neutral-border">
                      {{ getGlobalRowNumber(pIdx, rIdx) }}
                    </td>
                    <td class="py-2.5 px-3 font-medium text-neutral-800 border-r border-neutral-border whitespace-nowrap">
                      {{ r.periode_formatted }}
                    </td>
                    <td class="py-2.5 px-3 font-mono text-neutral-700 border-r border-neutral-border whitespace-nowrap">
                      {{ p.nip }}
                    </td>
                    <td class="py-2.5 px-3 font-semibold text-navy border-r border-neutral-border">
                      {{ p.nama }}
                    </td>
                    <td class="py-2.5 px-3 text-neutral-700 border-r border-neutral-border">
                      {{ p.divisi }}
                    </td>
                    <td class="py-2.5 px-2 text-center text-neutral-800 border-r border-neutral-border whitespace-nowrap">
                      {{ r.jam_masuk }}
                    </td>
                    <td class="py-2.5 px-2 text-center text-neutral-800 border-r border-neutral-border whitespace-nowrap">
                      {{ r.jam_pulang }}
                    </td>
                    <td class="py-2.5 px-3 border-r border-neutral-border">
                      <span
                        class="px-2 py-0.5 text-[11px] font-semibold border"
                        :class="getStatusBadgeClass(r.status)"
                      >
                        {{ r.status }}
                      </span>
                    </td>
                    <td class="py-2.5 px-3 text-neutral-700 border-r border-neutral-border">
                      {{ r.alasan }}
                    </td>
                    <td class="py-2.5 px-3 text-right font-medium text-red-700 whitespace-nowrap">
                      {{ r.durasi_formatted }}
                    </td>
                  </tr>

                  <!-- Employee Subtotal Row (REK-7, REK-8, REK-9) -->
                  <tr class="bg-neutral-100 font-semibold border-b border-neutral-300">
                    <td colspan="9" class="py-2.5 px-4 text-right text-navy border-r border-neutral-border">
                      Total Kekurangan Jam Kerja &mdash; <span class="font-bold">{{ p.nama }}</span>:
                    </td>
                    <td class="py-2.5 px-3 text-right text-red-800 font-bold whitespace-nowrap">
                      {{ p.total_durasi_formatted }}
                    </td>
                  </tr>
                </template>

                <!-- Grand Total Row -->
                <tr class="bg-neutral-200 font-bold text-navy border-t-2 border-navy">
                  <td colspan="9" class="py-3 px-4 text-right uppercase tracking-wider text-xs">
                    GRAND TOTAL AKUMULASI KEKURANGAN JAM KERJA:
                  </td>
                  <td class="py-3 px-3 text-right text-red-900 text-sm whitespace-nowrap">
                    {{ rekapData.ringkasan.grand_total_formatted }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-navy text-neutral-400 py-6 text-xs text-center border-t-2 border-accent no-print mt-auto">
      <div class="max-w-7xl mx-auto px-4">
        <p class="font-medium text-white">SIDISIPLIN &bull; Sistem Informasi Disiplin Pegawai</p>
        <p class="mt-1">Kementerian Hukum Kalimantan Selatan &bull; Jam Kerja Standar: 08:00 &ndash; 16:00 WITA</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import Navbar from '@/components/Navbar.vue'
import SkeletonLoader from '@/components/SkeletonLoader.vue'
import api from '@/composables/useApi'
import {
  Filter,
  Search,
  X,
  Check,
  FileSpreadsheet,
  FileDown,
  Printer,
  CheckCircle2,
} from 'lucide-vue-next'

const loading = ref(true)
const rekapData = ref(null)

const filters = reactive({
  mode: 'bulan',
  tanggal: new Date().toISOString().substring(0, 10),
  minggu: new Date().toISOString().substring(0, 10),
  bulan: new Date().toISOString().substring(0, 7),
  nip: '',
  divisi: '',
})

const pegawaiSearch = ref('')
const selectedPegawai = ref(null)
const showPegawaiDropdown = ref(false)
const pegawaiOptions = ref([])

const divisiOptions = [
  'Pelayanan Hukum',
  'P3H',
  'Tata Usaha dan Umum',
  'Kekayaan Intelektual',
  'Administrasi Hukum Umum',
  'Humas',
  'SDM',
  'Perencanaan',
  'Analis Hukum',
]

const filteredPegawaiOptions = computed(() => {
  if (!pegawaiSearch.value) return pegawaiOptions.value
  const q = pegawaiSearch.value.toLowerCase()
  return pegawaiOptions.value.filter(
    (p) => p.nama.toLowerCase().includes(q) || p.nip.includes(q)
  )
})

function setMode(mode) {
  filters.mode = mode
  fetchRekap()
}

function handlePegawaiSearchInput() {
  showPegawaiDropdown.value = true
  if (selectedPegawai.value && selectedPegawai.value.nama !== pegawaiSearch.value) {
    selectedPegawai.value = null
    filters.nip = ''
  }
}

function selectPegawai(p) {
  if (p) {
    selectedPegawai.value = p
    filters.nip = p.nip
    pegawaiSearch.value = `${p.nama} (${p.nip})`
  } else {
    clearSelectedPegawai()
  }
  showPegawaiDropdown.value = false
  fetchRekap()
}

function clearSelectedPegawai() {
  selectedPegawai.value = null
  filters.nip = ''
  pegawaiSearch.value = ''
  fetchRekap()
}

function resetFilter() {
  filters.mode = 'bulan'
  filters.tanggal = new Date().toISOString().substring(0, 10)
  filters.minggu = new Date().toISOString().substring(0, 10)
  filters.bulan = new Date().toISOString().substring(0, 7)
  filters.divisi = ''
  clearSelectedPegawai()
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

async function fetchPegawaiOptions() {
  try {
    const res = await api.get('/pegawai/options')
    pegawaiOptions.value = res.data.data
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
  fetchPegawaiOptions()
  fetchRekap()
})
</script>
