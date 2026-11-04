<template>
  <div class="space-y-6 max-w-2xl">
    <!-- Header -->
    <div class="border-b border-neutral-border pb-5">
      <h1 class="text-2xl font-bold text-navy tracking-tight">Pengaturan Jam Kerja Standar</h1>
      <p class="text-sm text-neutral-muted mt-0.5">
        Konfigurasi jam masuk dan pulang kerja standar sebagai acuan perhitungan otomatis kekurangan jam kerja.
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
      <SkeletonLoader type="form" :rows="2" />
    </div>

    <div v-else class="bg-white border border-neutral-border p-6 shadow-sm">
      <form @submit.prevent="handleSave" class="space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
              Jam Masuk Standar <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.jam_masuk_standar"
              type="time"
              required
              class="w-full text-xs border border-neutral-border px-3 py-2.5 bg-white focus:outline-none focus:border-navy text-neutral-900"
            />
            <p class="text-[11px] text-neutral-muted mt-1">Standar saat ini: 08:00 WITA</p>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
              Jam Pulang Standar <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.jam_pulang_standar"
              type="time"
              required
              class="w-full text-xs border border-neutral-border px-3 py-2.5 bg-white focus:outline-none focus:border-navy text-neutral-900"
            />
            <p class="text-[11px] text-neutral-muted mt-1">Standar saat ini: 16:00 WITA</p>
          </div>
        </div>

        <div class="p-3.5 bg-neutral-50 border border-neutral-border text-xs text-neutral-600 space-y-1">
          <p class="font-semibold text-navy">Rumus Perhitungan Kekurangan Jam Kerja:</p>
          <p>&bull; <strong>Tanpa Keterangan:</strong> Dikenai sanksi tetap sebesar 7,5 jam kerja (450 menit).</p>
          <p>&bull; <strong>Terlambat / Pulang Duluan:</strong> (Jam Masuk &minus; Jam Masuk Standar) + (Jam Pulang Standar &minus; Jam Pulang), masing-masing bernilai minimal 0.</p>
        </div>

        <div class="pt-4 border-t border-neutral-border flex justify-end">
          <button
            type="submit"
            :disabled="saving"
            class="px-5 py-2.5 bg-navy text-white text-xs font-semibold hover:bg-navy-800 focus:outline-none disabled:opacity-50 transition-colors"
          >
            {{ saving ? 'Menyimpan...' : 'Simpan Pengaturan' }}
          </button>
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

const loading = ref(true)
const saving = ref(false)
const alertMessage = ref('')
const isError = ref(false)

const form = reactive({
  jam_masuk_standar: '08:00',
  jam_pulang_standar: '16:00',
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
    form.jam_masuk_standar = res.data.data.jam_masuk_standar
    form.jam_pulang_standar = res.data.data.jam_pulang_standar
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
