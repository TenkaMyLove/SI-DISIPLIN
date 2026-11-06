<template>
  <div class="space-y-6 max-w-3xl">
    <!-- Header -->
    <div class="border-b border-neutral-border pb-5">
      <div class="flex items-center space-x-2">
        <Clock class="w-6 h-6 text-navy" />
        <h1 class="text-2xl font-bold text-navy tracking-tight">Pengaturan Jam Kerja</h1>
      </div>
      <p class="text-sm text-neutral-muted mt-1">
        Jadwal operasional kehadiran kantor dan konfigurasi ketentuan Jam Kerja Fleksibel (Flex) sebagai acuan perhitungan otomatis kekurangan jam kerja.
      </p>
    </div>

    <!-- Alert Message -->
    <div
      v-if="alertMessage"
      class="p-4 text-xs font-medium border"
      :class="isError ? 'bg-red-50 text-red-800 border-red-300' : 'bg-emerald-50 text-emerald-800 border-emerald-300'"
      role="alert"
    >
      {{ alertMessage }}
    </div>

    <div v-if="loading" class="space-y-4">
      <SkeletonLoader type="form" :rows="3" />
    </div>

    <div v-else class="space-y-6">
      <form @submit.prevent="handleSave" class="space-y-6">
        <!-- Jadwal Per Hari -->
        <div class="bg-white border border-neutral-border p-6 shadow-sm space-y-6">
          <div class="border-b border-neutral-border pb-3">
            <h2 class="text-sm font-bold text-navy uppercase tracking-wider">Jadwal Jam Kerja Operasional</h2>
            <p class="text-xs text-neutral-500 mt-0.5">Atur jam masuk dan pulang kerja standar untuk setiap kelompok hari kerja.</p>
          </div>

          <div class="space-y-4">
            <!-- 1. SENIN -->
            <div class="p-4 bg-neutral-50 border border-neutral-border transition-colors hover:border-neutral-400">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                <div class="flex items-center space-x-2">
                  <span class="text-sm font-bold text-navy">Senin</span>
                  <span class="px-2 py-0.5 text-[11px] font-semibold bg-neutral-200 text-neutral-800 border border-neutral-300">
                    Non Flex
                  </span>
                </div>
                <span class="text-[11px] text-neutral-500">Keterlambatan langsung dihitung kekurangan</span>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
                    Jam Masuk <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.senin_masuk"
                    type="time"
                    required
                    class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900"
                  />
                  <p class="text-[11px] text-neutral-500 mt-1">Standar: 07:30 WITA</p>
                </div>
                <div>
                  <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
                    Jam Pulang <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.senin_pulang"
                    type="time"
                    required
                    class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900"
                  />
                  <p class="text-[11px] text-neutral-500 mt-1">Standar: 16:00 WITA</p>
                </div>
              </div>
            </div>

            <!-- 2. SELASA - KAMIS (FLEX) -->
            <div class="p-4 bg-emerald-50/50 border border-emerald-300 transition-colors">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                <div class="flex items-center space-x-2">
                  <span class="text-sm font-bold text-navy">Selasa &ndash; Kamis</span>
                  <span class="px-2 py-0.5 text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center space-x-1">
                    <Zap class="w-3 h-3 text-emerald-700 inline" />
                    <span>Flex Time</span>
                  </span>
                </div>
                <span class="text-[11px] text-emerald-800 font-medium">Bisa kompensasi jam pulang jika datang terlambat</span>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
                    Jam Masuk Standar <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.selasa_kamis_masuk"
                    type="time"
                    required
                    class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900"
                  />
                  <p class="text-[11px] text-neutral-500 mt-1">Standar: 07:30 WITA</p>
                </div>
                <div>
                  <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
                    Jam Pulang Standar <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.selasa_kamis_pulang"
                    type="time"
                    required
                    class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900"
                  />
                  <p class="text-[11px] text-neutral-500 mt-1">Standar: 16:00 WITA (510 menit / 8,5 jam kehadiran)</p>
                </div>
              </div>
            </div>

            <!-- 3. JUMAT -->
            <div class="p-4 bg-neutral-50 border border-neutral-border transition-colors hover:border-neutral-400">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                <div class="flex items-center space-x-2">
                  <span class="text-sm font-bold text-navy">Jumat</span>
                  <span class="px-2 py-0.5 text-[11px] font-semibold bg-neutral-200 text-neutral-800 border border-neutral-300">
                    Non Flex
                  </span>
                </div>
                <span class="text-[11px] text-neutral-500">Waktu istirahat ibadah Jumat (11:30 - 13:00)</span>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
                    Jam Masuk <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.jumat_masuk"
                    type="time"
                    required
                    class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900"
                  />
                  <p class="text-[11px] text-neutral-500 mt-1">Standar: 07:30 WITA</p>
                </div>
                <div>
                  <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
                    Jam Pulang <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.jumat_pulang"
                    type="time"
                    required
                    class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900"
                  />
                  <p class="text-[11px] text-neutral-500 mt-1">Standar: 16:30 WITA</p>
                </div>
              </div>
            </div>
          </div>

          <div class="pt-4 border-t border-neutral-border flex justify-end">
            <button
              type="submit"
              :disabled="saving"
              class="px-5 py-2.5 bg-navy text-white text-xs font-semibold hover:bg-navy-800 focus:outline-none disabled:opacity-50 transition-colors shadow-sm"
            >
              {{ saving ? 'Menyimpan...' : 'Simpan Pengaturan Jam Kerja' }}
            </button>
          </div>
        </div>

        <!-- Ketentuan & Panduan Perhitungan Jam Kerja -->
        <div class="bg-white border border-neutral-border p-6 shadow-sm space-y-4">
          <div class="flex items-center space-x-2 border-b border-neutral-border pb-3">
            <Info class="w-4 h-4 text-navy" />
            <h2 class="text-sm font-bold text-navy uppercase tracking-wider">Ketentuan &amp; Rumus Perhitungan</h2>
          </div>

          <!-- Flex Rule Callout -->
          <div class="p-4 bg-emerald-50 border-l-4 border-emerald-600 text-xs text-neutral-700 space-y-2">
            <div class="flex items-center space-x-1.5 font-bold text-emerald-900">
              <Zap class="w-4 h-4 text-emerald-600" />
              <span>Ketentuan Jam Kerja Fleksibel (Flex Time - Selasa s.d. Kamis):</span>
            </div>
            <p>
              Pada hari <strong>Selasa sampai dengan Kamis</strong>, sistem menerapkan sistem <strong>Flex</strong>:
            </p>
            <div class="p-3 bg-white border border-emerald-200 text-xs font-mono space-y-1.5 rounded">
              <p class="font-sans font-semibold text-emerald-900">&bull; Contoh Penerapan Flex:</p>
              <p class="text-neutral-800">
                Jika pegawai datang pukul <strong class="text-navy">07:36</strong> (terlambat 6 menit), maka pegawai tersebut harus pulang minimal pada pukul <strong class="text-navy">16:06</strong> agar memenuhi jam kerja wajib (510 menit / 8,5 jam kehadiran).
              </p>
              <p class="text-neutral-600 text-[11px]">
                &rarr; Bila pulang pada pukul <strong>16:06</strong>: Kekurangan jam kerja = <strong>0 menit</strong> (terpenuhi).<br />
                &rarr; Bila pulang pada pukul <strong>16:00</strong>: Kekurangan jam kerja = <strong>6 menit</strong>.
              </p>
            </div>
          </div>

          <!-- Non Flex Rule Callout -->
          <div class="p-4 bg-neutral-50 border-l-4 border-navy text-xs text-neutral-700 space-y-1.5">
            <div class="flex items-center space-x-1.5 font-bold text-navy">
              <Clock class="w-4 h-4 text-navy" />
              <span>Ketentuan Jam Kerja Reguler (Non-Flex - Senin &amp; Jumat):</span>
            </div>
            <p>
              Pada hari <strong>Senin</strong> dan <strong>Jumat</strong>, jam kerja bersifat <strong>Non-Flex</strong>. Keterlambatan saat masuk kantor (melebihi 07:30) langsung diperhitungkan sebagai kekurangan jam kerja tanpa kompensasi kepulangan lebih lambat.
            </p>
            <p class="text-[11px] text-neutral-500">
              Rumus: <em>(Jam Masuk &minus; Jam Masuk Standar) + (Jam Pulang Standar &minus; Jam Pulang)</em>, masing-masing bernilai minimal 0.
            </p>
          </div>

          <!-- Sanksi Tanpa Keterangan -->
          <div class="p-3 bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1">
            <p class="font-semibold">&bull; Sanksi Tanpa Keterangan:</p>
            <p class="text-[11px] text-amber-800">
              Pegawai dengan status <strong>Tanpa Keterangan</strong> otomatis dikenakan sanksi tetap sebesar <strong>7,5 jam kerja (450 menit)</strong>.
            </p>
          </div>
        </div>
      </form>
    </div>

    <!-- Toast -->
    <Toast
      :show="toast.show"
      :message="toast.message"
      :variant="toast.variant"
      @update:show="toast.show = $event"
    />
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import SkeletonLoader from '@/components/SkeletonLoader.vue'
import Toast from '@/components/Toast.vue'
import api from '@/composables/useApi'
import { Clock, Zap, Info } from 'lucide-vue-next'

const loading = ref(true)
const saving = ref(false)
const alertMessage = ref('')
const isError = ref(false)

const form = reactive({
  senin_masuk: '07:30',
  senin_pulang: '16:00',
  selasa_kamis_masuk: '07:30',
  selasa_kamis_pulang: '16:00',
  jumat_masuk: '07:30',
  jumat_pulang: '16:30',
})

const toast = reactive({
  show: false,
  message: '',
  variant: 'success',
})

async function fetchPengaturan() {
  loading.value = true
  try {
    const res = await api.get('/pengaturan')
    const jadwal = res.data?.data?.jadwal
    if (jadwal) {
      if (jadwal.senin) {
        form.senin_masuk = jadwal.senin.masuk
        form.senin_pulang = jadwal.senin.pulang
      }
      if (jadwal.selasa_kamis) {
        form.selasa_kamis_masuk = jadwal.selasa_kamis.masuk
        form.selasa_kamis_pulang = jadwal.selasa_kamis.pulang
      }
      if (jadwal.jumat) {
        form.jumat_masuk = jadwal.jumat.masuk
        form.jumat_pulang = jadwal.jumat.pulang
      }
    } else {
      form.senin_masuk = res.data.data.jam_masuk_standar || '07:30'
      form.senin_pulang = res.data.data.jam_pulang_standar || '16:00'
    }
  } catch (err) {
    console.error('Gagal mengambil pengaturan:', err)
  } finally {
    loading.value = false
  }
}

async function handleSave() {
  alertMessage.value = ''
  isError.value = false
  saving.value = true
  try {
    const res = await api.put('/pengaturan', form)
    alertMessage.value = res.data.message
    toast.message = res.data.message
    toast.variant = 'success'
    toast.show = true
  } catch (err) {
    alertMessage.value = err.response?.data?.message || 'Gagal menyimpan pengaturan.'
    isError.value = true
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchPengaturan()
})
</script>