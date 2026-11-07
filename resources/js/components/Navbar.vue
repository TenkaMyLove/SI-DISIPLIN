<template>
  <header class="fixed top-0 left-0 right-0 h-20 bg-navy text-white border-b-2 border-accent no-print z-40">
    <div :class="fullWidth ? 'w-full px-4 sm:px-6 lg:px-8' : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'">
      <div class="flex items-center justify-between h-20">
        <!-- Brand & Logo -->
        <router-link to="/" class="flex items-center space-x-3.5 focus:outline-none">
          <PengayomanLogo :size="48" />
          <div>
            <div class="flex items-center space-x-2">
              <span class="text-xl font-bold tracking-wider text-white">SIDISIPLIN</span>
            </div>
            <p class="text-xs text-neutral-300 tracking-wide font-normal">
              Sistem Informasi Disiplin Pegawai &bull; Kementerian Hukum Kalsel
            </p>
          </div>
        </router-link>

        <!-- Right Navigation Actions -->
        <div class="flex items-center space-x-3">
          <router-link
            to="/"
            class="px-3.5 py-2 text-sm font-medium text-white hover:text-accent transition-colors"
            active-class="text-accent"
          >
            Rekap Kehadiran
          </router-link>

          <template v-if="isAuthenticated">
            <router-link
              to="/admin/dashboard"
              class="px-3.5 py-2 text-sm font-medium bg-neutral-800 hover:bg-neutral-700 text-white border border-neutral-700 transition-colors flex items-center space-x-1.5"
            >
              <LayoutDashboard class="w-4 h-4" />
              <span>Panel Admin</span>
            </router-link>
            <button
              @click="handleLogout"
              class="px-3 py-2 text-sm font-medium text-neutral-300 hover:text-white border border-transparent hover:border-neutral-700 transition-colors"
              title="Keluar dari sistem"
            >
              <LogOut class="w-4 h-4" />
            </button>
          </template>

          <template v-else>
            <router-link
              to="/admin/login"
              class="px-4 py-2 text-sm font-medium bg-white text-navy hover:bg-neutral-100 transition-colors flex items-center space-x-1.5 font-semibold"
            >
              <Lock class="w-3.5 h-3.5" />
              <span>Masuk Admin</span>
            </router-link>
          </template>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import PengayomanLogo from './PengayomanLogo.vue'
import { LayoutDashboard, Lock, LogOut } from 'lucide-vue-next'

defineProps({
  fullWidth: {
    type: Boolean,
    default: false,
  },
})

const router = useRouter()
const { isAuthenticated, logout } = useAuth()

async function handleLogout() {
  await logout()
  router.push('/admin/login')
}
</script>
