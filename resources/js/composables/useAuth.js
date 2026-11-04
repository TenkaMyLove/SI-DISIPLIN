import { ref, computed } from 'vue'
import api from './useApi'

const user = ref(JSON.parse(localStorage.getItem('sidisiplin_admin_user') || 'null'))
const token = ref(localStorage.getItem('sidisiplin_admin_token') || null)
const loading = ref(false)
const error = ref(null)

export function useAuth() {
  const isAuthenticated = computed(() => !!token.value && !!user.value)

  async function login(username, password) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/auth/login', { username, password })
      token.value = response.data.token
      user.value = response.data.user
      localStorage.setItem('sidisiplin_admin_token', token.value)
      localStorage.setItem('sidisiplin_admin_user', JSON.stringify(user.value))
      return { success: true }
    } catch (err) {
      const msg = err.response?.data?.message || err.response?.data?.errors?.username?.[0] || 'Gagal masuk. Periksa username dan kata sandi.'
      error.value = msg
      return { success: false, message: msg }
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } catch {
      // Ignore network errors on logout
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('sidisiplin_admin_token')
      localStorage.removeItem('sidisiplin_admin_user')
    }
  }

  async function checkAuth() {
    if (!token.value) {
      user.value = null
      return false
    }
    try {
      const response = await api.get('/auth/user')
      user.value = response.data.user
      localStorage.setItem('sidisiplin_admin_user', JSON.stringify(user.value))
      return true
    } catch {
      token.value = null
      user.value = null
      localStorage.removeItem('sidisiplin_admin_token')
      localStorage.removeItem('sidisiplin_admin_user')
      return false
    }
  }

  async function changePassword(currentPassword, newPassword, newPasswordConfirmation) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/auth/change-password', {
        current_password: currentPassword,
        new_password: newPassword,
        new_password_confirmation: newPasswordConfirmation,
      })
      return { success: true, message: response.data.message }
    } catch (err) {
      const errors = err.response?.data?.errors
      let msg = err.response?.data?.message || 'Gagal mengubah kata sandi.'
      if (errors) {
        msg = Object.values(errors).flat().join(' ')
      }
      return { success: false, message: msg }
    } finally {
      loading.value = false
    }
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    login,
    logout,
    checkAuth,
    changePassword,
  }
}
