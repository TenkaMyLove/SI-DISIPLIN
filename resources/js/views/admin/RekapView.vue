<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-neutral-border pb-5 gap-4">
      <div>
        <h1 class="text-2xl font-bold text-navy tracking-tight">Rekapitulasi Kehadiran Bermasalah</h1>
        <p class="text-sm text-neutral-muted mt-0.5">
          Tinjauan rekapitulasi data absensi bermasalah pegawai dan total akumulasi kekurangan jam kerja.
        </p>
      </div>

      <div class="flex items-center space-x-2">
        <button
          @click="exportExcel"
          class="inline-flex items-center space-x-1.5 px-3 py-2 border border-neutral-border text-xs font-semibold text-navy bg-white hover:bg-neutral-50 transition-colors"
        >
          <FileSpreadsheet class="w-4 h-4 text-emerald-700" />
          <span>Ekspor Excel</span>
        </button>

        <button
          @click="exportPdf"
          class="inline-flex items-center space-x-1.5 px-3 py-2 border border-neutral-border text-xs font-semibold text-navy bg-white hover:bg-neutral-50 transition-colors"
        >
          <FileDown class="w-4 h-4 text-red-700" />
          <span>Ekspor PDF</span>
        </button>

        <button
          @click="printRekap"
          class="inline-flex items-center space-x-1.5 px-3 py-2 bg-navy text-white text-xs font-semibold hover:bg-navy-800 transition-colors"
        >
          <Printer class="w-4 h-4" />
          <span>Cetak</span>
        </button>
      </div>
    </div>

    <!-- Filter Panel -->
    <div class="bg-white border border-neutral-border p-4 grid grid-cols-1 sm:grid-cols-3 gap-4 no-print">
      <div>
        <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1">
          Mode Periode
        </label>
        <div class="grid grid-cols-3 border border-neutral-border p-1 bg-neutral-50 gap-1 mb-2">
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
          class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy"
          @change="fetchRekap"
        />
        <input
          v-else-if="filters.mode === 'minggu'"
          v-model="filters.minggu"
          type="date"
          class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy"
          @change="fetchRekap"
        />
        <input
          v-else
          v-model="filters.bulan"
          type="month"
          class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy"
          @change="fetchRekap"
        />
      </div>

      <div>
        <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-1">
          Pegawai
        </label>
        <div class="mt-8">
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
          Divisi / Unit Kerja
        </label>
        <div class="mt-8">
          <CustomDropdown
            v-model="filters.divisi"
            :options="[
              { value: '', label: 'Semua Divisi' },
              ...divisiOptions.map(d => ({ value: d, label: d }))
            ]"
            placeholder="Semua Divisi"
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
    <div v-else class="space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 no-print">
        <div class="bg-white border border-neutral-border p-4">
          <p class="text-xs font-semibold text-neutral-muted uppercase tracking-wider">Pegawai Terkena Rekap</p>
          <p class="text-2xl font-bold text-navy mt-1">{{ rekapData?.ringkasan?.total_pegawai_bermasalah || 0 }} orang</p>
        </div>
        <div class="bg-white border border-neutral-border p-4">
          <p class="text-xs font-semibold text-neutral-muted uppercase tracking-wider">Total Pelanggaran</p>
          <p class="text-2xl font-bold text-navy mt-1">{{ rekapData?.ringkasan?.total_catatan_absensi || 0 }} kali</p>
        </div>
        <div class="bg-white border border-neutral-border p-4">
          <p class="text-xs font-semibold text-neutral-muted uppercase tracking-wider">Grand Total Kekurangan</p>
          <p class="text-xl font-bold text-red-700 mt-1">{{ rekapData?.ringkasan?.grand_total_formatted || '0 menit' }}</p>
        </div>
      </div>

      <div v-if="!rekapData?.pegawai || rekapData.pegawai.length === 0" class="bg-white border border-neutral-border p-10 text-center text-xs text-neutral-muted">
        Tidak ada data absensi bermasalah pada filter yang dipilih.
      </div>

      <div v-else class="bg-white border border-neutral-border overflow-x-auto shadow-sm">
        <table class="w-full text-left border-collapse text-xs">
          <thead class="sticky top-0 z-10">
            <tr class="bg-navy text-white">
              <th class="py-2.5 px-3 font-semibold text-center w-12 border-r border-navy-800">No</th>
              <th class="py-2.5 px-3 font-semibold w-28 border-r border-navy-800">Tanggal</th>
              <th class="py-2.5 px-3 font-semibold w-44 border-r border-navy-800">NIP</th>
              <th class="py-2.5 px-3 font-semibold border-r border-navy-800">Nama Pegawai</th>
              <th class="py-2.5 px-3 font-semibold w-36 border-r border-navy-800">Divisi</th>
              <th class="py-2.5 px-2 font-semibold text-center w-24 border-r border-navy-800">Masuk-Pulang</th>
              <th class="py-2.5 px-3 font-semibold w-32 border-r border-navy-800">Status</th>
              <th class="py-2.5 px-3 font-semibold border-r border-navy-800">Alasan</th>
              <th class="py-2.5 px-3 font-semibold text-right w-32">Kekurangan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-border">
            <template v-for="(p, pIdx) in rekapData.pegawai" :key="p.nip">
              <tr v-for="(r, rIdx) in p.rincian" :key="r.id" class="hover:bg-neutral-50">
                <td class="py-2 px-3 text-center text-neutral-500 border-r border-neutral-border">
                  {{ getGlobalRowNumber(pIdx, rIdx) }}
                </td>
                <td class="py-2 px-3 text-neutral-800 border-r border-neutral-border">{{ r.periode_formatted }}</td>
                <td class="py-2 px-3 font-mono text-neutral-700 border-r border-neutral-border">{{ p.nip }}</td>
                <td class="py-2 px-3 font-semibold text-navy border-r border-neutral-border">{{ p.nama }}</td>
                <td class="py-2 px-3 text-neutral-700 border-r border-neutral-border">{{ p.divisi }}</td>
                <td class="py-2 px-2 text-center text-neutral-800 border-r border-neutral-border">
                  {{ r.jam_masuk }} &ndash; {{ r.jam_pulang }}
                </td>
                <td class="py-2 px-3 border-r border-neutral-border">
                  <span class="px-2 py-0.5 text-[11px] font-semibold border" :class="getStatusBadgeClass(r.status)">
                    {{ r.status }}
                  </span>
                </td>
                <td class="py-2 px-3 text-neutral-700 border-r border-neutral-border">{{ r.alasan }}</td>
                <td class="py-2 px-3 text-right font-medium text-red-700">{{ r.durasi_formatted }}</td>
              </tr>
              <tr class="bg-neutral-100 font-semibold border-b border-neutral-300">
                <td colspan="8" class="py-2.5 px-4 text-right text-navy border-r border-neutral-border">
                  Total Kekurangan Jam Kerja:
                </td>
                <td class="py-2.5 px-3 text-right text-red-800 font-bold whitespace-nowrap">
                  {{ p.total_durasi_formatted }}
                </td>
              </tr>
            </template>
            <tr class="bg-neutral-200 font-bold text-navy border-t-2 border-navy">
              <td colspan="8" class="py-3 px-4 text-right uppercase tracking-wider text-xs">
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
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
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
  'Kekayaan Intelektual',
  'Administrasi Hukum Umum',
  'Humas',
  'SDM',
  'Perencanaan',
  'Analis Hukum',
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

function printRekap() {
  window.print()
}

onMounted(() => {
  fetchPegawaiList()
  fetchRekap()
})
</script>
