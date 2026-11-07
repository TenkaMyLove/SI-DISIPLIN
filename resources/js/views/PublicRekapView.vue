<template>
  <div class="min-h-screen flex flex-col bg-neutral-surface pt-20 pb-16 print:pt-0 print:pb-0 overflow-x-hidden">
    <Navbar />

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-col min-w-0 max-w-full">
      <!-- Top Header & Live Metric Badges (Single Compact Line) -->
      <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-neutral-border pb-2.5 mb-2.5 gap-2">
        <div>
          <h1 class="text-lg sm:text-xl font-bold text-navy tracking-tight">
            Rekapitulasi Kehadiran Bermasalah
          </h1>
          <p class="text-xs text-neutral-muted">
            Kementerian Hukum Kalimantan Selatan &bull; Zona Waktu WITA
          </p>
        </div>

        <!-- Inline Metric Summary Badges -->
        <div v-if="!loading && rekapData?.ringkasan" class="flex items-center space-x-2 text-xs no-print flex-wrap gap-y-1">
          <div class="px-2.5 py-1 bg-white border border-neutral-border text-navy flex items-center space-x-1.5 shadow-2xs">
            <span class="text-neutral-muted text-[11px] uppercase tracking-wider">Pegawai:</span>
            <span class="font-bold text-navy">{{ rekapData.ringkasan.total_pegawai_bermasalah || 0 }} orang</span>
          </div>
          <div class="px-2.5 py-1 bg-white border border-neutral-border text-navy flex items-center space-x-1.5 shadow-2xs">
            <span class="text-neutral-muted text-[11px] uppercase tracking-wider">Pelanggaran:</span>
            <span class="font-bold text-navy">{{ rekapData.ringkasan.total_catatan_absensi || 0 }} kali</span>
          </div>
          <div class="px-2.5 py-1 bg-red-50 border border-red-300 text-red-900 flex items-center space-x-1.5 shadow-2xs">
            <span class="font-medium text-[11px] uppercase tracking-wider text-red-700">Akumulasi Kekurangan:</span>
            <span class="font-bold text-red-900">{{ rekapData.ringkasan.grand_total_formatted || '0 menit' }}</span>
          </div>
        </div>
      </div>

      <!-- Compact Unified Filter & Export Toolbar (Single Sleek Bar) -->
      <div class="bg-white border border-neutral-border p-2.5 mb-3 shadow-2xs no-print">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-2.5">
          <!-- Filters Group -->
          <div class="flex flex-wrap items-center gap-2 flex-1">
            <!-- Mode Selector (Harian / Mingguan / Bulanan) -->
            <div class="inline-flex rounded-lg border border-neutral-300 p-0.5 bg-neutral-100 h-[38px] items-center shadow-2xs flex-shrink-0">
              <button
                type="button"
                @click="setMode('tanggal')"
                class="px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors"
                :class="filters.mode === 'tanggal' ? 'bg-navy text-white font-semibold' : 'text-neutral-700 hover:bg-neutral-200'"
              >
                Harian
              </button>
              <button
                type="button"
                @click="setMode('minggu')"
                class="px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors"
                :class="filters.mode === 'minggu' ? 'bg-navy text-white font-semibold' : 'text-neutral-700 hover:bg-neutral-200'"
              >
                Mingguan
              </button>
              <button
                type="button"
                @click="setMode('bulan')"
                class="px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors"
                :class="filters.mode === 'bulan' ? 'bg-navy text-white font-semibold' : 'text-neutral-700 hover:bg-neutral-200'"
              >
                Bulanan
              </button>
            </div>

            <!-- Date / Month Picker -->
            <div class="w-36 flex-shrink-0">
              <input
                v-if="filters.mode === 'tanggal'"
                v-model="filters.tanggal"
                type="date"
                class="w-full h-[38px] text-xs border border-neutral-300 rounded-lg px-2.5 bg-white focus:outline-none focus:border-navy focus:ring-1 focus:ring-navy shadow-2xs"
                @change="fetchRekap"
              />
              <input
                v-else-if="filters.mode === 'minggu'"
                v-model="filters.minggu"
                type="date"
                class="w-full h-[38px] text-xs border border-neutral-300 rounded-lg px-2.5 bg-white focus:outline-none focus:border-navy focus:ring-1 focus:ring-navy shadow-2xs"
                @change="fetchRekap"
              />
              <input
                v-else
                v-model="filters.bulan"
                type="month"
                class="w-full h-[38px] text-xs border border-neutral-300 rounded-lg px-2.5 bg-white focus:outline-none focus:border-navy focus:ring-1 focus:ring-navy shadow-2xs"
                @change="fetchRekap"
              />
            </div>

            <!-- Pegawai Search Autocomplete (Figma-styled with click-outside & Escape key support) -->
            <div ref="pegawaiDropdownRef" class="relative w-64 flex-shrink-0">
              <div class="relative">
                <input
                  v-model="pegawaiSearch"
                  type="text"
                  placeholder="Cari NIP / Nama Pegawai..."
                  class="w-full h-[38px] text-xs border border-neutral-300 rounded-lg pl-8 pr-7 py-2 bg-white focus:outline-none focus:border-navy focus:ring-1 focus:ring-navy shadow-2xs transition-all placeholder:text-neutral-400"
                  @focus="showPegawaiDropdown = true"
                  @input="handlePegawaiSearchInput"
                />
                <Search class="w-3.5 h-3.5 text-neutral-400 absolute left-2.5 top-3 pointer-events-none" />
                <button
                  v-if="selectedPegawai || pegawaiSearch"
                  @click="clearSelectedPegawai"
                  type="button"
                  class="w-5 h-5 absolute right-2 top-2.5 flex items-center justify-center text-neutral-400 hover:text-navy hover:bg-neutral-100 rounded transition-colors"
                  title="Bersihkan pilihan"
                >
                  <X class="w-3.5 h-3.5" />
                </button>
              </div>

              <!-- Dropdown List Styled like CustomDropdown -->
              <transition
                enter-active-class="transition duration-100 ease-out"
                enter-from-class="transform scale-95 opacity-0"
                enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in"
                leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0"
              >
                <div
                  v-if="showPegawaiDropdown"
                  class="absolute z-50 w-80 mt-1.5 bg-white rounded-lg border border-neutral-200 shadow-xl overflow-hidden py-1 max-h-60 overflow-y-auto left-0"
                >
                  <div
                    @click="selectPegawai(null)"
                    class="px-3 py-2 text-xs cursor-pointer flex items-center space-x-2.5 transition-colors select-none"
                    :class="!filters.nip ? 'bg-neutral-100 text-navy font-semibold' : 'text-neutral-700 hover:bg-neutral-50'"
                  >
                    <span
                      class="w-4 h-4 rounded border flex items-center justify-center flex-shrink-0 transition-colors"
                      :class="!filters.nip ? 'border-navy bg-navy text-white' : 'border-neutral-300 bg-white'"
                    >
                      <Check v-if="!filters.nip" class="w-2.5 h-2.5 stroke-[3]" />
                    </span>
                    <span class="truncate font-medium">Tampilkan Semua Pegawai</span>
                  </div>

                  <div
                    v-for="p in filteredPegawaiOptions"
                    :key="p.nip"
                    @click="selectPegawai(p)"
                    class="px-3 py-2 text-xs cursor-pointer flex items-center space-x-2.5 transition-colors select-none"
                    :class="filters.nip === p.nip ? 'bg-neutral-100 text-navy font-semibold' : 'text-neutral-700 hover:bg-neutral-50'"
                  >
                    <span
                      class="w-4 h-4 rounded border flex items-center justify-center flex-shrink-0 transition-colors"
                      :class="filters.nip === p.nip ? 'border-navy bg-navy text-white' : 'border-neutral-300 bg-white'"
                    >
                      <Check v-if="filters.nip === p.nip" class="w-2.5 h-2.5 stroke-[3]" />
                    </span>
                    <div class="truncate">
                      <p class="font-medium text-navy truncate">{{ p.nama }}</p>
                      <p class="text-[11px] text-neutral-500 truncate">NIP: {{ p.nip }} &bull; {{ p.divisi }}</p>
                    </div>
                  </div>

                  <div v-if="filteredPegawaiOptions.length === 0" class="px-3 py-3 text-xs text-neutral-400 text-center">
                    Pegawai tidak ditemukan.
                  </div>
                </div>
              </transition>
            </div>

            <!-- Divisi Dropdown -->
            <div class="w-52 flex-shrink-0">
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

            <!-- Reset Button -->
            <button
              @click="resetFilter"
              type="button"
              class="text-xs text-neutral-500 hover:text-navy px-1.5 py-1 underline whitespace-nowrap"
              title="Reset semua filter ke default"
            >
              Reset
            </button>
          </div>

          <!-- Actions: Export Excel, PDF, Print -->
          <div class="flex items-center space-x-1.5 flex-shrink-0 border-t lg:border-t-0 pt-2 lg:pt-0 border-neutral-border">
            <span class="text-[11px] text-neutral-500 mr-1 hidden xl:inline">
              {{ rekapData?.filter?.periode_label || '-' }}
            </span>

            <button
              @click="exportExcel"
              class="inline-flex items-center space-x-1 px-2.5 py-1 border border-neutral-border text-xs font-medium text-navy bg-white hover:bg-neutral-50 transition-colors"
              title="Unduh format spreadsheet Excel"
            >
              <FileSpreadsheet class="w-3.5 h-3.5 text-emerald-700" />
              <span>Excel</span>
            </button>

            <button
              @click="exportPdf"
              class="inline-flex items-center space-x-1 px-2.5 py-1 border border-neutral-border text-xs font-medium text-navy bg-white hover:bg-neutral-50 transition-colors"
              title="Unduh format dokumen PDF"
            >
              <FileDown class="w-3.5 h-3.5 text-red-700" />
              <span>PDF</span>
            </button>

            <button
              @click="printRekap"
              class="inline-flex items-center space-x-1 px-2.5 py-1 border border-neutral-border text-xs font-medium text-navy bg-white hover:bg-neutral-50 transition-colors"
              title="Cetak halaman rekap"
            >
              <Printer class="w-3.5 h-3.5 text-navy" />
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
          <div class="bg-white border border-neutral-border overflow-x-auto shadow-2xs max-w-full">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-navy text-white border-b border-navy">
                  <th class="py-2.5 px-3 font-semibold text-center w-12 border-r border-navy-800">No</th>
                  <th class="py-2.5 px-3 font-semibold w-28 border-r border-navy-800">Tanggal</th>
                  <th class="py-2.5 px-3 font-semibold w-44 border-r border-navy-800">NIP</th>
                  <th class="py-2.5 px-3 font-semibold border-r border-navy-800">Nama Pegawai</th>
                  <th class="py-2.5 px-3 font-semibold w-40 border-r border-navy-800">Divisi / Bagian</th>
                  <th class="py-2.5 px-2 font-semibold text-center w-20 border-r border-navy-800">Masuk</th>
                  <th class="py-2.5 px-2 font-semibold text-center w-20 border-r border-navy-800">Pulang</th>
                  <th class="py-2.5 px-3 font-semibold w-36 border-r border-navy-800">Status</th>
                  <th class="py-2.5 px-3 font-semibold border-r border-navy-800">Alasan</th>
                  <th class="py-2.5 px-3 font-semibold text-right w-36">Kekurangan</th>
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
                    <td class="py-2 px-3 text-center text-neutral-500 border-r border-neutral-border">
                      {{ getGlobalRowNumber(pIdx, rIdx) }}
                    </td>
                    <td class="py-2 px-3 font-medium text-neutral-800 border-r border-neutral-border whitespace-nowrap">
                      {{ r.periode_formatted }}
                    </td>
                    <td class="py-2 px-3 font-mono text-neutral-700 border-r border-neutral-border whitespace-nowrap">
                      {{ p.nip }}
                    </td>
                    <td class="py-2 px-3 font-semibold text-navy border-r border-neutral-border">
                      {{ p.nama }}
                    </td>
                    <td class="py-2 px-3 text-neutral-700 border-r border-neutral-border">
                      {{ p.divisi }}
                    </td>
                    <td class="py-2 px-2 text-center text-neutral-800 border-r border-neutral-border whitespace-nowrap">
                      {{ r.jam_masuk }}
                    </td>
                    <td class="py-2 px-2 text-center text-neutral-800 border-r border-neutral-border whitespace-nowrap">
                      {{ r.jam_pulang }}
                    </td>
                    <td class="py-2 px-3 border-r border-neutral-border">
                      <span
                        class="px-2 py-0.5 text-[11px] font-semibold border"
                        :class="getStatusBadgeClass(r.status)"
                      >
                        {{ r.status }}
                      </span>
                    </td>
                    <td class="py-2 px-3 text-neutral-700 border-r border-neutral-border">
                      {{ r.alasan }}
                    </td>
                    <td class="py-2 px-3 text-right font-medium text-red-700 whitespace-nowrap">
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
    </main>

    <!-- Footer: Dikembalikan seperti awal dan di-anchor di dasar layar -->
    <footer class="fixed bottom-0 left-0 right-0 bg-navy text-neutral-300 py-2.5 text-xs text-center border-t-2 border-accent no-print z-40">
      <div class="max-w-7xl mx-auto px-4">
        <p class="font-medium text-white">SIDISIPLIN &bull; Sistem Informasi Disiplin Pegawai</p>
        <p class="mt-0.5 text-[11px] text-neutral-300">Kementerian Hukum Kalimantan Selatan &bull; Jam Kerja: 07:30 &ndash; 16:00 WITA (Jumat 16:30)</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue'
import Navbar from '@/components/Navbar.vue'
import CustomDropdown from '@/components/CustomDropdown.vue'
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
const pegawaiDropdownRef = ref(null)
const pegawaiOptions = ref([])

function handleClickOutside(e) {
  if (pegawaiDropdownRef.value && !pegawaiDropdownRef.value.contains(e.target)) {
    showPegawaiDropdown.value = false
  }
}

function handleKeyDown(e) {
  if (e.key === 'Escape' && showPegawaiDropdown.value) {
    showPegawaiDropdown.value = false
  }
}

const divisiOptions = [
  'Pelayanan Hukum',
  'P3H',
  'Tata Usaha dan Umum',
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
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleKeyDown)
  fetchPegawaiOptions()
  fetchRekap()
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleKeyDown)
})
</script>
