<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-neutral-border pb-5 gap-4">
      <div>
        <h1 class="text-2xl font-bold text-navy tracking-tight">Dashboard Administrator</h1>
        <p class="text-sm text-neutral-muted mt-0.5">
          Selamat datang, <strong class="text-navy">{{ user?.name || 'Admin' }}</strong>. Pantau dan kelola data disiplin kehadiran pegawai.
        </p>
      </div>

      <div class="flex items-center space-x-2">
        <router-link
          to="/admin/absensi"
          class="px-4 py-2 bg-navy text-white text-xs font-semibold hover:bg-navy-800 transition-colors flex items-center space-x-1.5"
        >
          <Plus class="w-4 h-4" />
          <span>Catat Absensi</span>
        </router-link>
        <router-link
          to="/admin/pegawai"
          class="px-4 py-2 border border-neutral-border bg-white text-navy text-xs font-semibold hover:bg-neutral-50 transition-colors flex items-center space-x-1.5"
        >
          <UserPlus class="w-4 h-4" />
          <span>Tambah Pegawai</span>
        </router-link>
      </div>
    </div>

    <!-- Skeleton during loading -->
    <div v-if="loading" class="space-y-6">
      <SkeletonLoader type="stat" :rows="4" />
      <SkeletonLoader type="table" :rows="5" :cols="6" />
    </div>

    <div v-else class="space-y-6">
      <!-- Stat Cards (No card-in-card, clean solid borders) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-neutral-border p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-neutral-muted uppercase tracking-wider">Total Pegawai</span>
            <Users class="w-5 h-5 text-navy" />
          </div>
          <p class="text-3xl font-bold text-navy mt-2">{{ stats.totalPegawai }}</p>
          <p class="text-xs text-neutral-muted mt-1">Terdaftar di 3 Divisi / Bagian</p>
        </div>

        <div class="bg-white border border-neutral-border p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-neutral-muted uppercase tracking-wider">Catatan Absensi</span>
            <CalendarX2 class="w-5 h-5 text-navy" />
          </div>
          <p class="text-3xl font-bold text-navy mt-2">{{ stats.totalAbsensi }}</p>
          <p class="text-xs text-neutral-muted mt-1">Total seluruh catatan bermasalah</p>
        </div>

        <div class="bg-white border border-neutral-border p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-neutral-muted uppercase tracking-wider">Kekurangan Bulan Ini</span>
            <Clock class="w-5 h-5 text-red-700" />
          </div>
          <p class="text-2xl font-bold text-red-700 mt-2 truncate">{{ stats.totalKekuranganBulanIni }}</p>
          <p class="text-xs text-neutral-muted mt-1">Akumulasi bulan {{ currentMonthLabel }}</p>
        </div>

        <div class="bg-white border border-neutral-border p-5">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-neutral-muted uppercase tracking-wider">Jam Kerja</span>
            <Settings class="w-5 h-5 text-navy" />
          </div>
          <p class="text-lg font-bold text-navy mt-2">
            {{ settings.jam_masuk_standar }} &ndash; {{ settings.jam_pulang_standar }}
          </p>
          <p class="text-xs text-neutral-muted mt-1">WITA &bull; Senin &ndash; Jumat</p>
        </div>
      </div>

      <!-- Recent Attendance Records Section -->
      <div class="bg-white border border-neutral-border p-6">
        <div class="flex items-center justify-between border-b border-neutral-border pb-4 mb-4">
          <div>
            <h2 class="text-base font-bold text-navy">Catatan Absensi Bermasalah Terbaru</h2>
            <p class="text-xs text-neutral-muted mt-0.5">Daftar kehadiran pegawai bermasalah yang terakhir dicatat</p>
          </div>
          <router-link
            to="/admin/absensi"
            class="text-xs font-semibold text-navy hover:underline flex items-center space-x-1"
          >
            <span>Kelola Semua Catatan</span>
            <ArrowRight class="w-3.5 h-3.5" />
          </router-link>
        </div>

        <div v-if="recentRecords.length === 0" class="py-8 text-center text-xs text-neutral-muted">
          Belum ada catatan absensi bermasalah yang terekam.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-navy text-white">
                <th class="py-2.5 px-3 font-semibold">Tanggal</th>
                <th class="py-2.5 px-3 font-semibold">Nama Pegawai</th>
                <th class="py-2.5 px-3 font-semibold">NIP</th>
                <th class="py-2.5 px-3 font-semibold text-center">Masuk - Pulang</th>
                <th class="py-2.5 px-3 font-semibold">Status</th>
                <th class="py-2.5 px-3 font-semibold text-right">Kekurangan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-neutral-border">
              <tr v-for="rec in recentRecords" :key="rec.id" class="hover:bg-neutral-50 transition-colors">
                <td class="py-2.5 px-3 font-medium text-neutral-800">{{ rec.periode_formatted }}</td>
                <td class="py-2.5 px-3 font-semibold text-navy">{{ rec.pegawai?.nama }}</td>
                <td class="py-2.5 px-3 font-mono text-neutral-600">{{ rec.nip }}</td>
                <td class="py-2.5 px-3 text-center text-neutral-800">
                  {{ rec.jam_masuk?.substring(0, 5) }} &ndash; {{ rec.jam_pulang?.substring(0, 5) }}
                </td>
                <td class="py-2.5 px-3">
                  <span
                    class="px-2 py-0.5 text-[11px] font-semibold border"
                    :class="getStatusBadgeClass(rec.status)"
                  >
                    {{ rec.status }}
                  </span>
                </td>
                <td class="py-2.5 px-3 text-right font-semibold text-red-700">
                  {{ rec.durasi_formatted }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '@/composables/useAuth'
import SkeletonLoader from '@/components/SkeletonLoader.vue'
import api from '@/composables/useApi'
import {
  Users,
  CalendarX2,
  Clock,
  Settings,
  Plus,
  UserPlus,
  ArrowRight,
} from 'lucide-vue-next'

const { user } = useAuth()
const loading = ref(true)

const stats = ref({
  totalPegawai: 0,
  totalAbsensi: 0,
  totalKekuranganBulanIni: '0 menit',
})

const settings = ref({
  jam_masuk_standar: '08:00',
  jam_pulang_standar: '16:00',
})

const recentRecords = ref([])
const currentMonthLabel = new Date().toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })

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

async function fetchDashboardData() {
  loading.value = true
  try {
    const [pegawaiRes, absensiRes, rekapRes, settingRes] = await Promise.all([
      api.get('/pegawai?per_page=1'),
      api.get('/absensi?per_page=5'),
      api.get('/rekap', { params: { mode: 'bulan', bulan: new Date().toISOString().substring(0, 7) } }),
      api.get('/pengaturan'),
    ])

    stats.value.totalPegawai = pegawaiRes.data.meta?.total || 0
    stats.value.totalAbsensi = absensiRes.data.meta?.total || 0
    stats.value.totalKekuranganBulanIni = rekapRes.data.data?.ringkasan?.grand_total_formatted || '0 menit'

    recentRecords.value = absensiRes.data.data || []
    settings.value = settingRes.data.data
  } catch (err) {
    console.error('Gagal memuat data dashboard:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>
