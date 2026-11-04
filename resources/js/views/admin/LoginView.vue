<template>
  <div class="min-h-screen bg-neutral-surface flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
      <router-link to="/" class="inline-block focus:outline-none mb-3">
        <PengayomanLogo :size="64" />
      </router-link>
      <h1 class="text-2xl font-bold tracking-tight text-navy uppercase">
        SIDISIPLIN
      </h1>
      <h2 class="text-sm font-medium text-neutral-600 mt-1">
        Masuk Administrator &bull; Kemenkumham Kalsel
      </h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-6 sm:px-10 border border-neutral-border shadow-sm">
        <!-- Error Alert -->
        <div
          v-if="errorMessage"
          class="mb-5 p-3.5 bg-red-50 border border-red-300 text-red-800 text-xs font-medium flex items-center space-x-2"
          role="alert"
        >
          <AlertCircle class="w-4 h-4 text-red-700 flex-shrink-0" />
          <span>{{ errorMessage }}</span>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div>
            <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
              Username
            </label>
            <div class="relative">
              <input
                id="username"
                v-model="form.username"
                type="text"
                required
                autocomplete="username"
                placeholder="Masukkan username admin"
                class="w-full text-sm border border-neutral-border pl-9 pr-3 py-2.5 bg-white focus:outline-none focus:border-navy text-neutral-900"
              />
              <User class="w-4 h-4 text-neutral-400 absolute left-3 top-3" />
            </div>
          </div>

          <div>
            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-neutral-700 mb-1">
              Kata Sandi
            </label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                autocomplete="current-password"
                placeholder="Masukkan kata sandi"
                class="w-full text-sm border border-neutral-border pl-9 pr-10 py-2.5 bg-white focus:outline-none focus:border-navy text-neutral-900"
              />
              <Lock class="w-4 h-4 text-neutral-400 absolute left-3 top-3" />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-3 text-neutral-400 hover:text-navy focus:outline-none"
              >
                <EyeOff v-if="showPassword" class="w-4 h-4" />
                <Eye v-else class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div>
            <button
              type="submit"
              :disabled="loading"
              class="w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-semibold text-white bg-navy hover:bg-navy-800 focus:outline-none transition-colors disabled:opacity-50"
            >
              <span v-if="loading">Memproses...</span>
              <span v-else>Masuk ke Sistem</span>
            </button>
          </div>
        </form>

        <div class="mt-6 pt-5 border-t border-neutral-border text-center">
          <router-link
            to="/"
            class="text-xs text-navy font-semibold hover:underline inline-flex items-center space-x-1"
          >
            <span>&larr; Kembali ke Rekapitulasi Publik</span>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import PengayomanLogo from '@/components/PengayomanLogo.vue'
import { User, Lock, Eye, EyeOff, AlertCircle } from 'lucide-vue-next'

const router = useRouter()
const { login } = useAuth()

const form = reactive({
  username: '',
  password: '',
})

const loading = ref(false)
const errorMessage = ref('')
const showPassword = ref(false)

async function handleSubmit() {
  errorMessage.value = ''
  loading.value = true
  const res = await login(form.username, form.password)
  loading.value = false

  if (res.success) {
    router.push('/admin/dashboard')
  } else {
    errorMessage.value = res.message
  }
}
</script>
