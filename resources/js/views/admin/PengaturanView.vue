<template>
  <div class="space-y-3.5 max-w-5xl">
    <!-- Header: Compact without bottom border line to prevent sinking -->
    <div class="flex items-center space-x-2">
      <Clock class="w-6 h-6 text-navy" />
      <h1 class="text-xl sm:text-2xl font-bold text-navy tracking-tight">Pengaturan Jam Kerja</h1>
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
            <p class="text-xs text-neutral-500 mt-0.5">
              Atur jam masuk, jam pulang, dan ketentuan flexi time untuk setiap hari kerja.
            </p>
          </div>

          <div class="space-y-4">
            <div
              v-for="day in WEEKDAYS"
              :key="day.key"
              class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-stretch"
            >
              <!-- Kartu Jam Kerja Hari -->
              <div class="p-4 bg-neutral-50 border border-neutral-border transition-colors hover:border-neutral-400 flex flex-col justify-between">
                <div>
                  <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="text-sm font-bold text-navy">{{ day.label }}</span>
                    <label class="inline-flex items-center space-x-2 cursor-pointer select-none text-xs font-medium text-neutral-700 hover:text-navy">
                      <input
                        v-model="scheduleForm[day.key].is_flex"
                        type="checkbox"
                        class="w-4 h-4 accent-[#031833] border-neutral-300 cursor-pointer"
                        @change="handleFlexToggle(day.key)"
                      />
                      <span>Apakah flexi time?</span>
                    </label>
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
                        Jam Masuk <span class="text-red-500">*</span>
                      </label>
                      <input
                        v-model="scheduleForm[day.key].masuk"
                        type="time"
                        required
                        class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900"
                        @change="handleMasukChange(day.key)"
                      />
                    </div>
                    <div>
                      <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
                        Jam Pulang <span class="text-red-500">*</span>
                      </label>
                      <input
                        v-model="scheduleForm[day.key].pulang"
                        type="time"
                        required
                        class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Kartu Pengelolaan Flexi Time (muncul di samping saat diceklis) -->
              <transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0 translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
              >
                <div
                  v-if="scheduleForm[day.key].is_flex"
                  class="p-4 bg-navy-50/60 border border-navy-200 flex flex-col justify-between"
                >
                  <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                      <div class="flex items-center space-x-1.5">
                        <Clock class="w-3.5 h-3.5 text-navy" />
                        <span class="text-xs font-bold text-navy uppercase tracking-wider">
                          Pengaturan Flexi Time &ndash; {{ day.label }}
                        </span>
                      </div>
                      <span class="px-2 py-0.5 text-[10px] font-semibold bg-navy text-white uppercase tracking-wider">
                        Aktif
                      </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                      <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
                          Batas Awal Masuk <span class="text-red-500">*</span>
                        </label>
                        <input
                          v-model="scheduleForm[day.key].flex_masuk_mulai"
                          type="time"
                          required
                          class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900"
                        />
                      </div>

                      <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
                          Batas Akhir Masuk <span class="text-red-500">*</span>
                        </label>
                        <input
                          v-model="scheduleForm[day.key].flex_masuk_akhir"
                          type="time"
                          required
                          class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900"
                          @input="syncToleransiFromAkhir(day.key)"
                        />
                      </div>

                      <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
                          Toleransi Masuk (Menit)
                        </label>
                        <input
                          v-model.number="scheduleForm[day.key].flex_toleransi_menit"
                          type="number"
                          min="0"
                          max="240"
                          class="w-full text-xs border border-neutral-border px-3 py-2 bg-white focus:outline-none focus:border-navy text-neutral-900"
                          @input="syncAkhirFromToleransi(day.key)"
                        />
                      </div>

                      <div>
                        <span class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
                          Target Jam Pulang
                        </span>
                        <div class="px-3 py-2 bg-white border border-neutral-border text-xs font-semibold text-navy">
                          {{ getTargetPulangRange(day.key) }}
                        </div>
                      </div>
                    </div>
                  </div>

                  <p class="text-[11px] text-neutral-600 mt-3 pt-2.5 border-t border-navy-200/70">
                    Durasi wajib: <strong class="text-navy">{{ getWorkDurationLabel(day.key) }}</strong>.
                    Masuk {{ scheduleForm[day.key].flex_masuk_mulai }}&ndash;{{ scheduleForm[day.key].flex_masuk_akhir }} WITA
                    menyesuaikan target pulang ({{ getTargetPulangRange(day.key) }}).
                  </p>
                </div>
              </transition>
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
import { reactive, shallowRef, onMounted } from 'vue'
import SkeletonLoader from '@/components/SkeletonLoader.vue'
import Toast from '@/components/Toast.vue'
import api from '@/composables/useApi'
import {
  WEEKDAYS,
  timeToMinutes,
  minutesToTime,
  formatDurationMinutes,
} from '@/utils/workingHours'
import { Clock } from 'lucide-vue-next'

const loading = shallowRef(true)
const saving = shallowRef(false)
const alertMessage = shallowRef('')
const isError = shallowRef(false)

function createInitialDayState(day) {
  const masukMin = timeToMinutes(day.defaultMasuk)
  return {
    masuk: day.defaultMasuk,
    pulang: day.defaultPulang,
    is_flex: day.defaultFlex,
    flex_masuk_mulai: day.defaultMasuk,
    flex_masuk_akhir: minutesToTime(masukMin + 30),
    flex_toleransi_menit: 30,
  }
}

const scheduleForm = reactive(
  Object.fromEntries(WEEKDAYS.map((day) => [day.key, createInitialDayState(day)]))
)

const toast = reactive({
  show: false,
  message: '',
  variant: 'success',
})

function applyJadwalToForm(jadwal) {
  if (!jadwal) return
  for (const day of WEEKDAYS) {
    const item = jadwal[day.key]
    if (!item) continue

    const target = scheduleForm[day.key]
    target.masuk = item.masuk || day.defaultMasuk
    target.pulang = item.pulang || day.defaultPulang
    target.is_flex = Boolean(item.is_flex)
    target.flex_masuk_mulai = item.flex_masuk_mulai || target.masuk
    target.flex_masuk_akhir = item.flex_masuk_akhir || minutesToTime(timeToMinutes(target.masuk) + 30)
    target.flex_toleransi_menit =
      item.flex_toleransi_menit ?? Math.max(0, timeToMinutes(target.flex_masuk_akhir) - timeToMinutes(target.masuk))
  }
}

function handleFlexToggle(dayKey) {
  const dayState = scheduleForm[dayKey]
  if (!dayState.is_flex) return

  const masukMin = timeToMinutes(dayState.masuk)
  const pulangMin = timeToMinutes(dayState.pulang)
  if (!dayState.flex_masuk_mulai || timeToMinutes(dayState.flex_masuk_mulai) > masukMin) {
    dayState.flex_masuk_mulai = dayState.masuk
  }
  if (!dayState.flex_masuk_akhir || timeToMinutes(dayState.flex_masuk_akhir) < masukMin) {
    const defaultAkhirMin = Math.min(Math.max(masukMin, pulangMin - 1), masukMin + 30)
    dayState.flex_masuk_akhir = minutesToTime(defaultAkhirMin)
  }
  syncToleransiFromAkhir(dayKey)
}

function handleMasukChange(dayKey) {
  const dayState = scheduleForm[dayKey]
  const masukMin = timeToMinutes(dayState.masuk)
  const pulangMin = timeToMinutes(dayState.pulang)

  if (timeToMinutes(dayState.flex_masuk_mulai) > masukMin) {
    dayState.flex_masuk_mulai = dayState.masuk
  }

  const toleransi = Math.max(0, Number(dayState.flex_toleransi_menit) || 0)
  const nextAkhirMin = Math.min(Math.max(masukMin, pulangMin - 1), masukMin + toleransi)
  dayState.flex_masuk_akhir = minutesToTime(nextAkhirMin)
  dayState.flex_toleransi_menit = Math.max(0, nextAkhirMin - masukMin)
}

function syncToleransiFromAkhir(dayKey) {
  const dayState = scheduleForm[dayKey]
  if (!dayState.flex_masuk_akhir) return
  const masukMin = timeToMinutes(dayState.masuk)
  const akhirMin = timeToMinutes(dayState.flex_masuk_akhir)
  dayState.flex_toleransi_menit = Math.max(0, akhirMin - masukMin)
}

function syncAkhirFromToleransi(dayKey) {
  const dayState = scheduleForm[dayKey]
  const masukMin = timeToMinutes(dayState.masuk)
  const pulangMin = timeToMinutes(dayState.pulang)
  const toleransi = Math.max(0, Number(dayState.flex_toleransi_menit) || 0)
  const targetMin = Math.min(Math.max(masukMin, pulangMin - 1), masukMin + toleransi)
  dayState.flex_masuk_akhir = minutesToTime(targetMin)
}

function getTargetPulangRange(dayKey) {
  const dayState = scheduleForm[dayKey]
  const stdDuration = Math.max(0, timeToMinutes(dayState.pulang) - timeToMinutes(dayState.masuk))
  const mulaiPulang = minutesToTime(timeToMinutes(dayState.flex_masuk_mulai) + stdDuration)
  const akhirPulang = minutesToTime(timeToMinutes(dayState.flex_masuk_akhir) + stdDuration)
  return mulaiPulang === akhirPulang ? `${mulaiPulang} WITA` : `${mulaiPulang} \u2013 ${akhirPulang} WITA`
}

function getWorkDurationLabel(dayKey) {
  const dayState = scheduleForm[dayKey]
  const stdDuration = Math.max(0, timeToMinutes(dayState.pulang) - timeToMinutes(dayState.masuk))
  return formatDurationMinutes(stdDuration)
}

async function fetchPengaturan() {
  loading.value = true
  try {
    const res = await api.get('/pengaturan')
    const jadwal = res.data?.data?.jadwal
    if (jadwal) {
      applyJadwalToForm(jadwal)
    } else {
      scheduleForm.senin.masuk = res.data?.data?.jam_masuk_standar || '07:30'
      scheduleForm.senin.pulang = res.data?.data?.jam_pulang_standar || '16:00'
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
    const payload = {}
    for (const day of WEEKDAYS) {
      const state = scheduleForm[day.key]
      payload[`${day.key}_masuk`] = state.masuk
      payload[`${day.key}_pulang`] = state.pulang
      payload[`${day.key}_flex`] = state.is_flex
      payload[`${day.key}_flex_masuk_mulai`] = state.flex_masuk_mulai
      payload[`${day.key}_flex_masuk_akhir`] = state.flex_masuk_akhir
    }

    const res = await api.put('/pengaturan', payload)
    applyJadwalToForm(res.data?.data?.jadwal)
    alertMessage.value = res.data.message
    toast.message = res.data.message
    toast.variant = 'success'
    toast.show = true
  } catch (err) {
    const errors = err.response?.data?.errors
    if (errors) {
      alertMessage.value = Object.values(errors).flat().join(' ')
    } else {
      alertMessage.value = err.response?.data?.message || 'Gagal menyimpan pengaturan.'
    }
    isError.value = true
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchPengaturan()
})
</script>
