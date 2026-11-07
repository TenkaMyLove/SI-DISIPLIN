import { createRouter, createWebHistory } from 'vue-router'
import PublicRekapView from '@/views/PublicRekapView.vue'
import LoginView from '@/views/admin/LoginView.vue'
import AdminLayout from '@/views/admin/AdminLayout.vue'
import DashboardView from '@/views/admin/DashboardView.vue'
import PegawaiView from '@/views/admin/PegawaiView.vue'
import DivisiView from '@/views/admin/DivisiView.vue'
import AbsensiView from '@/views/admin/AbsensiView.vue'
import RekapView from '@/views/admin/RekapView.vue'
import PengaturanView from '@/views/admin/PengaturanView.vue'
import ChangePasswordView from '@/views/admin/ChangePasswordView.vue'

const routes = [
  {
    path: '/',
    name: 'public-rekap',
    component: PublicRekapView,
    meta: { title: 'Rekap Kehadiran Bermasalah - SIDISIPLIN' },
  },
  {
    path: '/admin/login',
    name: 'admin-login',
    component: LoginView,
    meta: { title: 'Masuk Admin - SIDISIPLIN', guestOnly: true },
  },
  {
    path: '/login',
    redirect: '/admin/login',
  },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/admin/dashboard',
      },
      {
        path: 'dashboard',
        name: 'admin-dashboard',
        component: DashboardView,
        meta: { title: 'Dashboard - SIDISIPLIN' },
      },
      {
        path: 'pegawai',
        name: 'admin-pegawai',
        component: PegawaiView,
        meta: { title: 'Data Pegawai - SIDISIPLIN' },
      },
      {
        path: 'divisi',
        name: 'admin-divisi',
        component: DivisiView,
        meta: { title: 'Manajemen Divisi - SIDISIPLIN' },
      },
      {
        path: 'absensi',
        name: 'admin-absensi',
        component: AbsensiView,
        meta: { title: 'Catatan Absensi - SIDISIPLIN' },
      },
      {
        path: 'rekap',
        name: 'admin-rekap',
        component: RekapView,
        meta: { title: 'Rekapitulasi - SIDISIPLIN' },
      },
      {
        path: 'pengaturan',
        name: 'admin-pengaturan',
        component: PengaturanView,
        meta: { title: 'Jam Standar - SIDISIPLIN' },
      },
      {
        path: 'ganti-password',
        name: 'admin-ganti-password',
        component: ChangePasswordView,
        meta: { title: 'Ganti Kata Sandi - SIDISIPLIN' },
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('sidisiplin_admin_token')
  const isAuthenticated = !!token

  if (to.meta.title) {
    document.title = to.meta.title
  }

  // Auth requirement check (PRD AUTH-4: menu pengelolaan hanya setelah login; akses tanpa login dialihkan ke login)
  if (to.matched.some((record) => record.meta.requiresAuth)) {
    if (!isAuthenticated) {
      return next({ path: '/admin/login', query: { redirect: to.fullPath } })
    }
  }

  // Guest only check (e.g. login page)
  if (to.matched.some((record) => record.meta.guestOnly)) {
    if (isAuthenticated) {
      return next({ path: '/admin/dashboard' })
    }
  }

  next()
})

export default router
