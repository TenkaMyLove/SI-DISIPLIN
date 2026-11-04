<template>
  <div class="space-y-6 max-w-2xl">
    <!-- Header -->
    <div class="border-b border-neutral-border pb-5">
      <h1 class="text-2xl font-bold text-navy tracking-tight">Ganti Kata Sandi Akun</h1>
      <p class="text-sm text-neutral-muted mt-0.5">
        Perbarui kata sandi untuk akun administrator tunggal SIDISIPLIN.
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

    <div class="bg-white border border-neutral-border p-6 shadow-sm">
      <form @submit.prevent="handleChangePassword" class="space-y-5">
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
            Kata Sandi Saat Ini <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.current_password"
            type="password"
            required
            autocomplete="current-password"
            placeholder="Masukkan kata sandi lama"
            class="w-full text-xs border border-neutral-border px-3 py-2.5 bg-white focus:outline-none focus:border-navy text-neutral-900"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
            Kata Sandi Baru <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.new_password"
            type="password"
            required
            minlength="6"
            autocomplete="new-password"
            placeholder="Minimal 6 karakter"
            class="w-full text-xs border border-neutral-border px-3 py-2.5 bg-white focus:outline-none focus:border-navy text-neutral-900"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
            Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.new_password_confirmation"
            type="password"
            required
            minlength="6"
            autocomplete="new-password"
            placeholder="Ketik ulang kata sandi baru"
            class="w-full text-xs border border-neutral-border px-3 py-2.5 bg-white focus:outline-none focus:border-navy text-neutral-900"
          />
        </div>

        <div class="pt-4 border-t border-neutral-border flex justify-end">
          <button
            type="submit"
            :disabled="loading"
            class="px-5 py-2.5 bg-navy text-white text-xs font-semibold hover:bg-navy-800 focus:outline-none disabled:opacity-50 transition-colors"
          >
            {{ loading ? 'Menyimpan...' : 'Perbarui Kata Sandi' }}
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
import { reactive, ref } from 'vue'
import { useAuth } from '@/composables/useAuth'
import Toast from '@/components/Toast.vue'

const { changePassword } = useAuth()

const form = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

const loading = ref(false)
const alertMessage = ref('')
const isError = ref(false)

const toast = reactive({
  show: false,
  message: '',
  variant: 'success',
})

async function handleChangePassword() {
  alertMessage.value = ''
  isError.value = false

  if (form.new_password !== form.new_password_confirmation) {
    alertMessage.value = 'Konfirmasi kata sandi baru tidak cocok.'
    isError.value = true
    return
  }

  loading.value = true
  const res = await changePassword(
    form.current_password,
    form.new_password,
    form.new_password_confirmation
  )
  loading.value = false

  if (res.success) {
    alertMessage.value = res.message
    isError.value = false
    form.current_password = ''
    form.new_password = ''
    form.new_password_confirmation = ''
    toast.message = 'Kata sandi berhasil diperbarui.'
    toast.variant = 'success'
    toast.show = true
  } else {
    alertMessage.value = res.message
    isError.value = true
  }
}
</script>
