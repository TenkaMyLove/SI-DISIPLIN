<template>
  <aside class="fixed top-20 left-0 bottom-0 w-64 bg-navy text-white z-30 border-r border-navy-800 no-print flex flex-col justify-between select-none">
    <!-- Top Nav Links with internal smooth scrolling if window height is small -->
    <div class="py-4 flex-1 overflow-y-auto">
      <div class="px-5 mb-4 pb-3 border-b border-navy-800">
        <p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Menu Pengelolaan</p>
      </div>

      <nav class="space-y-1 px-3">
        <router-link
          to="/admin/dashboard"
          class="flex items-center space-x-3 px-3 py-2.5 text-sm font-medium transition-colors border-l-4"
          :class="isActive('/admin/dashboard') ? 'bg-navy-800 text-white border-accent' : 'text-neutral-300 hover:bg-navy-800 hover:text-white border-transparent'"
        >
          <LayoutDashboard class="w-4 h-4 flex-shrink-0" :class="isActive('/admin/dashboard') ? 'text-accent' : ''" />
          <span>Dashboard</span>
        </router-link>

        <router-link
          to="/admin/pegawai"
          class="flex items-center space-x-3 px-3 py-2.5 text-sm font-medium transition-colors border-l-4"
          :class="isActive('/admin/pegawai') ? 'bg-navy-800 text-white border-accent' : 'text-neutral-300 hover:bg-navy-800 hover:text-white border-transparent'"
        >
          <Users class="w-4 h-4 flex-shrink-0" :class="isActive('/admin/pegawai') ? 'text-accent' : ''" />
          <span>Data Pegawai</span>
        </router-link>

        <router-link
          to="/admin/divisi"
          class="flex items-center space-x-3 px-3 py-2.5 text-sm font-medium transition-colors border-l-4"
          :class="isActive('/admin/divisi') ? 'bg-navy-800 text-white border-accent' : 'text-neutral-300 hover:bg-navy-800 hover:text-white border-transparent'"
        >
          <Building2 class="w-4 h-4 flex-shrink-0" :class="isActive('/admin/divisi') ? 'text-accent' : ''" />
          <span>Manajemen Divisi</span>
        </router-link>

        <router-link
          to="/admin/absensi"
          class="flex items-center space-x-3 px-3 py-2.5 text-sm font-medium transition-colors border-l-4"
          :class="isActive('/admin/absensi') ? 'bg-navy-800 text-white border-accent' : 'text-neutral-300 hover:bg-navy-800 hover:text-white border-transparent'"
        >
          <CalendarX2 class="w-4 h-4 flex-shrink-0" :class="isActive('/admin/absensi') ? 'text-accent' : ''" />
          <span>Catatan Absensi</span>
        </router-link>

        <router-link
          to="/admin/rekap"
          class="flex items-center space-x-3 px-3 py-2.5 text-sm font-medium transition-colors border-l-4"
          :class="isActive('/admin/rekap') ? 'bg-navy-800 text-white border-accent' : 'text-neutral-300 hover:bg-navy-800 hover:text-white border-transparent'"
        >
          <FileBarChart class="w-4 h-4 flex-shrink-0" :class="isActive('/admin/rekap') ? 'text-accent' : ''" />
          <span>Rekapitulasi</span>
        </router-link>

        <router-link
          to="/admin/pengaturan"
          class="flex items-center space-x-3 px-3 py-2.5 text-sm font-medium transition-colors border-l-4"
          :class="isActive('/admin/pengaturan') ? 'bg-navy-800 text-white border-accent' : 'text-neutral-300 hover:bg-navy-800 hover:text-white border-transparent'"
        >
          <Clock class="w-4 h-4 flex-shrink-0" :class="isActive('/admin/pengaturan') ? 'text-accent' : ''" />
          <span>Jam Kerja</span>
        </router-link>

        <router-link
          to="/admin/ganti-password"
          class="flex items-center space-x-3 px-3 py-2.5 text-sm font-medium transition-colors border-l-4"
          :class="isActive('/admin/ganti-password') ? 'bg-navy-800 text-white border-accent' : 'text-neutral-300 hover:bg-navy-800 hover:text-white border-transparent'"
        >
          <KeyRound class="w-4 h-4 flex-shrink-0" :class="isActive('/admin/ganti-password') ? 'text-accent' : ''" />
          <span>Ganti Kata Sandi</span>
        </router-link>
      </nav>

      <div class="px-5 mt-6 mb-2 pt-3 border-t border-navy-800">
        <p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Akses Lain</p>
      </div>

      <div class="px-3">
        <router-link
          to="/"
          target="_blank"
          class="flex items-center space-x-3 px-3 py-2 text-sm text-neutral-300 hover:bg-navy-800 hover:text-white transition-colors"
        >
          <ExternalLink class="w-4 h-4 flex-shrink-0" />
          <span>Buka Rekap Publik</span>
        </router-link>
      </div>
    </div>

    <!-- Admin User Info & Logout: Anchored at bottom -->
    <div class="p-4 border-t border-navy-800 bg-navy-950 flex-shrink-0">
      <div class="flex items-center justify-between">
        <div class="min-w-0 mr-2">
          <p class="text-xs font-semibold text-white truncate">{{ user?.name || 'Administrator' }}</p>
          <p class="text-xs text-neutral-400 truncate">@{{ user?.username || 'admin' }}</p>
        </div>
        <button
          @click="handleLogout"
          class="p-1.5 text-neutral-400 hover:text-accent transition-colors flex-shrink-0"
          title="Keluar (Logout)"
        >
          <LogOut class="w-4 h-4" />
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import {
  LayoutDashboard,
  Users,
  Building2,
  CalendarX2,
  FileBarChart,
  Clock,
  KeyRound,
  ExternalLink,
  LogOut,
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const { user, logout } = useAuth()

function isActive(path) {
  return route.path === path
}

async function handleLogout() {
  await logout()
  router.push('/admin/login')
}
</script>
