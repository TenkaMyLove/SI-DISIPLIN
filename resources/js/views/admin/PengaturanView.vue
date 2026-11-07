<template>
  <div class="space-y-6 max-w-3xl">
    <!-- Header -->
    <div class="border-b border-neutral-border pb-5">
      <div class="flex items-center space-x-2">
        <Clock class="w-6 h-6 text-navy" />
        <h1 class="text-2xl font-bold text-navy tracking-tight">Pengaturan Jam Kerja</h1>
      </div>
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
              <div class="mb-3">
                <span class="text-sm font-bold text-navy">Senin</span>
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

            <!-- 2. SELASA - KAMIS -->
            <div class="p-4 bg-neutral-50 border border-neutral-border transition-colors hover:border-neutral-400">
              <div class="mb-3">
                <span class="text-sm font-bold text-navy">Selasa &ndash; Kamis</span>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
                    Jam Masuk <span class="text-red-500">*</span>
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
                    Jam Pulang <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.selasa_kamis_pulang"
                    type="time"
                    required
                    class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900"
                  />
                  <p class="text-[11px] text-neutral-500 mt-1">Standar: 16:00 WITA</p>
                </div>
              </div>
            </div>

            <!-- 3. JUMAT -->
            <div class="p-4 bg-neutral-50 border border-neutral-border transition-colors hover:border-neutral-400">
              <div class="mb-3">
                <span class="text-sm font-bold text-navy">Jumat</span>
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
import { Clock } from 'lucide-vue-next'

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