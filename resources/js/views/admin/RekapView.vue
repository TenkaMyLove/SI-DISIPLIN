<template>
  <div class="space-y-3.5">
    <!-- Header: Compact without bottom border line to prevent sinking -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 no-print">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">Rekapitulasi Kehadiran Bermasalah</h1>
        <p class="text-xs sm:text-sm text-neutral-muted mt-0.5">
          Tinjauan rekapitulasi data absensi bermasalah pegawai dan total akumulasi kekurangan jam kerja.
        </p>
      </div>

      <div class="flex items-center space-x-2 shrink-0">
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

    <!-- Filter Panel: Compact -->
    <div class="bg-white border border-neutral-border p-3 sm:px-4 sm:py-2.5 grid grid-cols-1 sm:grid-cols-3 gap-3 no-print shadow-2xs">
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

    <!-- Skeleton Loader -->
    <div v-if="loading" class="space-y-4">
      <SkeletonLoader type="stat" :rows="3" />
      <SkeletonLoader type="table" :rows="6" :cols="8" />
    </div>

    <!-- Content -->
    <div v-else class="space-y-3.5">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 no-print">
        <div class="bg-white border border-neutral-border p-3 shadow-2xs">
          <p class="text-[11px] font-semibold text-neutral-muted uppercase tracking-wider">Pegawai Terkena Rekap</p>
          <p class="text-xl font-bold text-navy mt-0.5">{{ rekapData?.ringkasan?.total_pegawai_bermasalah || 0 }} orang</p>
        </div>
        <div class="bg-white border border-neutral-border p-3 shadow-2xs">
          <p class="text-[11px] font-semibold text-neutral-muted uppercase tracking-wider">Total Pelanggaran</p>
          <p class="text-xl font-bold text-navy mt-0.5">{{ rekapData?.ringkasan?.total_catatan_absensi || 0 }} kali</p>
        </div>
      </div>

      <!-- Print-Only Official Report Header -->
      <div class="hidden print-only mb-5 text-center border-b-2 border-navy pb-3">
        <h1 class="text-base font-bold uppercase tracking-wider text-navy">SIDISIPLIN &bull; SISTEM INFORMASI DISIPLIN PEGAWAI</h1>
        <h2 class="text-xs font-bold text-neutral-800 tracking-wide uppercase">KANTOR WILAYAH KEMENTERIAN HUKUM RI PROVINSI KALIMANTAN SELATAN</h2>
        <p class="text-xs text-neutral-600 mt-1 font-medium">Laporan Rekapitulasi Kehadiran Bermasalah &bull; {{ rekapData?.filter?.periode_label }}</p>
      </div>

      <div v-if="!rekapData?.pegawai || rekapData.pegawai.length === 0" class="bg-white border border-neutral-border p-10 text-center text-xs text-neutral-muted">
        Tidak ada data absensi bermasalah pada filter yang dipilih.
      </div>

      <!-- Rekap Table Card Container -->
      <div v-else class="bg-white border border-neutral-border shadow-sm flex flex-col print:border-none print:shadow-none">
        <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-270px)] print:overflow-visible print:max-h-none print:h-auto">
          <table class="w-full text-left border-separate border-spacing-0 text-xs print:border-collapse">
            <thead class="sticky top-0 z-20 bg-navy text-white">
              <tr class="bg-navy text-white">
                <th class="py-2.5 px-3 font-semibold text-center w-12 border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy text-white">No</th>
                <th class="py-2.5 px-3 font-semibold w-28 border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy text-white whitespace-nowrap">Tanggal</th>
                <th class="py-2.5 px-3 font-semibold w-44 border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy text-white whitespace-nowrap">NIP</th>
                <th class="py-2.5 px-3 font-semibold border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy text-white">Nama Pegawai</th>
                <th class="py-2.5 px-3 font-semibold w-40 border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy text-white">Divisi / Bagian</th>
                <th class="py-2.5 px-2 font-semibold text-center w-24 border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy text-white whitespace-nowrap">Masuk-Pulang</th>
                <th class="py-2.5 px-3 font-semibold w-40 whitespace-nowrap border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy text-white">Status</th>
                <th class="py-2.5 px-3 font-semibold border-b-2 border-r border-navy-800 sticky top-0 z-20 bg-navy text-white">Alasan</th>
                <th class="py-2.5 px-3 font-semibold text-right w-32 border-b-2 border-navy-800 sticky top-0 z-20 bg-navy text-white whitespace-nowrap">Kekurangan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-neutral-border bg-white">
            <template v-for="(p, pIdx) in rekapData.pegawai" :key="p.nip">
              <tr v-for="(r, rIdx) in p.rincian" :key="r.id" class="hover:bg-neutral-50 transition-colors">
                <td class="py-2 px-3 text-center text-neutral-500 border-b border-r border-neutral-border bg-inherit">
                  {{ getGlobalRowNumber(pIdx, rIdx) }}
                </td>
                <td class="py-2 px-3 text-neutral-800 border-b border-r border-neutral-border bg-inherit">{{ r.periode_formatted }}</td>
                <td class="py-2 px-3 font-mono text-neutral-700 border-b border-r border-neutral-border bg-inherit">{{ p.nip }}</td>
                <td class="py-2 px-3 font-semibold text-navy border-b border-r border-neutral-border bg-inherit">{{ p.nama }}</td>
                <td class="py-2 px-3 text-neutral-700 border-b border-r border-neutral-border bg-inherit">{{ p.divisi }}</td>
                <td class="py-2 px-2 text-center text-neutral-800 border-b border-r border-neutral-border bg-inherit">
                  {{ r.jam_masuk }} &ndash; {{ r.jam_pulang }}
                </td>
                <td class="py-2 px-3 border-b border-r border-neutral-border whitespace-nowrap bg-inherit">
                  <span class="inline-flex items-center justify-center px-2.5 py-0.5 text-[11px] font-semibold border whitespace-nowrap" :class="getStatusBadgeClass(r.status)">
                    {{ r.status }}
                  </span>
                </td>
                <td class="py-2 px-3 text-neutral-700 border-b border-r border-neutral-border bg-inherit">{{ r.alasan }}</td>
                <td class="py-2 px-3 text-right font-medium text-red-700 border-b border-neutral-border bg-inherit">{{ r.durasi_formatted }}</td>
              </tr>
              <!-- Employee Subtotal Row: Merged into 1 column to eliminate excessive gap -->
              <tr class="bg-neutral-100 font-semibold border-b border-neutral-300">
                <td colspan="9" class="py-2 px-4 text-right text-xs">
                  <span class="text-navy font-semibold mr-1.5">Total Kekurangan Jam Kerja:</span>
                  <span class="text-red-800 font-bold whitespace-nowrap">{{ p.total_durasi_formatted }}</span>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Print-Only Official Signature Block -->
      <div class="hidden print-only mt-6 pt-2">
        <table class="w-full text-xs" style="border: none !important;">
          <tr style="border: none !important;">
            <td style="border: none !important; width: 65%; vertical-align: top; color: #64748B;">
              Dicetak secara otomatis melalui Sistem Informasi Disiplin Pegawai (SIDISIPLIN) pada {{ currentWitaTimestamp }} WITA.
            </td>
            <td style="border: none !important; width: 35%; text-align: center; vertical-align: top;">
              <p>Banjarmasin, {{ currentWitaDate }}</p>
              <p class="mt-12 font-bold underline text-navy">Administrator Disiplin</p>
              <p class="text-neutral-600">Kantor Wilayah Kementerian Hukum RI Provinsi Kalimantan Selatan</p>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import SkeletonLoader from '@/components/SkeletonLoader.vue'
import CustomDropdown from '@/components/CustomDropdown.vue'
import api from '@/composables/useApi'
import { FileSpreadsheet, FileDown, Printer } from 'lucide-vue-next'

const loading = ref(true)
const rekapData = ref(null)
const pegawaiList = ref([])

const divisiOptions = [
  'Pelayanan Hukum',
  'P3H',
  'Tata Usaha dan Umum',
]

const filters = reactive({
  mode: 'bulan',
  tanggal: new Date().toISOString().substring(0, 10),
  minggu: new Date().toISOString().substring(0, 10),
  bulan: new Date().toISOString().substring(0, 7),
  nip: '',
  divisi: '',
})

function setMode(mode) {
  filters.mode = mode
  fetchRekap()
}

function getStatusBadgeClass(status) {
  switch (status) {
    case 'Tanpa Keterangan': return 'bg-red-50 text-red-800 border-red-300'
    case 'Terlambat': return 'bg-amber-50 text-amber-800 border-amber-300'
    case 'Pulang Duluan': return 'bg-blue-50 text-blue-800 border-blue-300'
    default: return 'bg-neutral-100 text-neutral-800 border-neutral-300'
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
    console.error('Gagal mengambil data pegawai:', err)
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
  const params = new URLSearchParams({ mode: filters.mode })
  if (filters.mode === 'tanggal') params.append('tanggal', filters.tanggal)
  if (filters.mode === 'minggu') params.append('minggu', filters.minggu)
  if (filters.mode === 'bulan') params.append('bulan', filters.bulan)
  if (filters.nip) params.append('nip', filters.nip)
  if (filters.divisi) params.append('divisi', filters.divisi)
  window.open(`/api/rekap/export-excel?${params.toString()}`, '_blank')
}

function exportPdf() {
  const params = new URLSearchParams({ mode: filters.mode })
  if (filters.mode === 'tanggal') params.append('tanggal', filters.tanggal)
  if (filters.mode === 'minggu') params.append('minggu', filters.minggu)
  if (filters.mode === 'bulan') params.append('bulan', filters.bulan)
  if (filters.nip) params.append('nip', filters.nip)
  if (filters.divisi) params.append('divisi', filters.divisi)
  window.open(`/api/rekap/export-pdf?${params.toString()}`, '_blank')
}

const currentWitaTimestamp = computed(() => {
  return new Intl.DateTimeFormat('id-ID', {
    timeZone: 'Asia/Makassar',
    day: '2-digit',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  }).format(new Date()).replace('.', ':')
})

const currentWitaDate = computed(() => {
  return new Intl.DateTimeFormat('id-ID', {
    timeZone: 'Asia/Makassar',
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  }).format(new Date())
})

function printRekap() {
  window.print()
}

onMounted(() => {
  fetchPegawaiList()
  fetchRekap()
})
</script>
