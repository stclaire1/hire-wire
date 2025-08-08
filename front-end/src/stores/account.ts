import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import accountService from '@/services/accountService'
import type { Account, CreateAccountData } from '@/types'

export const useAccountStore = defineStore('account', () => {
  const accounts = ref<Account[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  const hasAccounts = computed(() => accounts.value?.length > 0)

  const fetchAccounts = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await accountService.getAccounts()
      
      if (response.success && response.data) {
        // the accounts are directly in response.data as Array
        accounts.value = response.data
        error.value = null
      } else {
        error.value = response.message || 'Erro ao carregar contas'
        accounts.value = []
      }
    } catch (err: any) {
      error.value = 'Erro inesperado ao carregar contas'
      accounts.value = []
    } finally {
      loading.value = false
    }
  }

  const createAccount = async (data: CreateAccountData) => {
  loading.value = true
  error.value = null

  try {
    const response = await accountService.createAccount(data)
    
    if (response.success) {
      // reload accounts listafter successful creation
      await fetchAccounts()
      return { success: true, message: response.message }
    } else {
      error.value = response.message || 'Erro ao criar conta'
      return { 
        success: false, 
        message: response.message || 'Erro ao criar conta', 
        errors: response.errors 
      }
    }
  } catch (err: any) {
    error.value = 'Erro inesperado ao criar conta'
    return { success: false, message: 'Erro inesperado ao criar conta' }
  } finally {
    loading.value = false
  }
}

  const clearError = () => {
    error.value = null
  }

  const resetStore = () => {
    accounts.value = []
    error.value = null
  }

  return {
    accounts,
    loading,
    error,
    hasAccounts,
    fetchAccounts,
    createAccount,
    clearError,
    resetStore
  }
})
