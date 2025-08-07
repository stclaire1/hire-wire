import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import authService, { type User, type LoginData, type RegisterData } from '@/services/authService'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!user.value)
  /**
   * initialize user from localStorage
   */
  const initAuth = () => {
    const storedUser = authService.getStoredUser()
    if (storedUser && authService.isAuthenticated()) {
      user.value = storedUser
    }
  }

  const login = async (data: LoginData) => {
    loading.value = true
    error.value = null

    try {
      const response = await authService.login(data)
      
      if (response.success && response.data) {
        user.value = response.data.user
        return { success: true, message: response.message }
      } else {
        error.value = response.message
        return { success: false, message: response.message, errors: response.errors }
      }
    } catch (err: any) {
      error.value = 'An unexpected error occurred'
      return { success: false, message: 'An unexpected error occurred' }
    } finally {
      loading.value = false
    }
  }

  const register = async (data: RegisterData) => {
    loading.value = true
    error.value = null

    try {
      const response = await authService.register(data)
      
      if (response.success && response.data) {
        user.value = response.data.user
        return { success: true, message: response.message }
      } else {
        error.value = response.message
        return { success: false, message: response.message, errors: response.errors }
      }
    } catch (err: any) {
      error.value = 'An unexpected error occurred'
      return { success: false, message: 'An unexpected error occurred' }
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    loading.value = true
    
    try {
      await authService.logout()
      user.value = null
      error.value = null
    } catch (err: any) {
      console.error('Logout error:', err)
    } finally {
      loading.value = false
    }
  }

  const clearError = () => {
    error.value = null
  }

  return {
    user,
    loading,
    error,
    isAuthenticated,
    initAuth,
    login,
    register,
    logout,
    clearError
  }
})
