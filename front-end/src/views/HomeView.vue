<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4">
      <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">
              Olá, {{ user?.name }}!
            </h1>
            <p class="text-gray-600">
              Bem-vindo ao seu banco digital
            </p>
          </div>
          <button 
            @click="handleLogout" 
            :disabled="authLoading" 
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
          >
            {{ authLoading ? 'Saindo...' : 'Sair' }}
          </button>
        </div>
      </div>

      <!-- loading -->
      <div v-if="accountsLoading" class="bg-white rounded-lg shadow-lg p-8">
        <div class="text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          <p class="text-gray-600 mt-2">Carregando suas contas...</p>
        </div>
      </div>

      <!-- accounts section -->
      <div v-else-if="!accountsLoading" class="space-y-6">
        <!-- accounts list -->
        <div v-if="hasAccounts" class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">Suas Contas</h2>
          <div class="space-y-3">
            <div 
              v-for="account in activeAccounts" 
              :key="account.id"
              class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors"
            >
              <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                  <div class="flex-shrink-0">
                    <div :class="getAccountIconClass(account.account_type)" class="h-10 w-10 rounded-full flex items-center justify-center">
                    </div>
                  </div>
                  <div>
                    <h3 class="font-medium text-gray-900">
                      Conta {{ getAccountTypeName(account.account_type) }}
                    </h3>
                    <p class="text-sm text-gray-600">{{ account.account_number }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-semibold text-gray-900">
                    R$ {{ formatCurrency(account.balance) }}
                  </p>
                  <!-- make this dynamic -->
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Ativa
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- no accounts -->
        <div v-else class="bg-white rounded-lg shadow-lg p-8">
          <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
              <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">
              Você ainda não possui uma conta
            </h3>
            <p class="text-gray-600 mb-6">
              Deseja abrir uma nova conta bancária?
            </p>
          </div>
        </div>

        <!-- create new account -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <button
            @click="goToCreateAccount"
            class="w-full flex items-center justify-center px-4 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors font-medium"
          >
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ hasAccounts ? 'Criar Nova Conta' : 'Abrir Primeira Conta' }}
          </button>
        </div>

        <!-- error -->
        <div v-if="accountError && !accountsLoading" class="bg-red-50 border border-red-200 rounded-lg p-4">
          <div class="flex">
            <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L3.732 19c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <div class="ml-3">
              <p class="text-red-800">{{ accountError }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAccountStore } from '@/stores/account'

const router = useRouter()
const authStore = useAuthStore()
const accountStore = useAccountStore()

const user = computed(() => authStore.user)
const authLoading = computed(() => authStore.loading)

const accountsLoading = computed(() => accountStore.loading)
const accountError = computed(() => {
  const error = accountStore.error
  // filter success messages that might be incorrectly set as errors
  if (error && (error.toLowerCase().includes('sucesso') || error.toLowerCase().includes('success'))) {
    return null
  }
  return error
})
const hasAccounts = computed(() => accountStore.hasAccounts)
const activeAccounts = computed(() => accountStore.activeAccounts)

onMounted(async () => {
  // redirect if not authenticated
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }

  // load user accounts
  await accountStore.fetchAccounts()
  
  // clear any success messages that might be set as errors
  const error = accountStore.error
  if (error && (error.toLowerCase().includes('sucesso') || error.toLowerCase().includes('success'))) {
    accountStore.clearError()
  }
})

const handleLogout = async () => {
  await authStore.logout()
  accountStore.resetStore()
  router.push('/login')
}

const goToCreateAccount = () => {
  router.push('/create-account')
}

const formatCurrency = (value: number): string => {
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

const getAccountTypeName = (type: 'checking' | 'savings' | 'investment'): string => {
  const typeNames = {
    checking: 'Corrente',
    savings: 'Poupança',
    investment: 'Investimento'
  }
  return typeNames[type]
}

const getAccountIconClass = (type: 'checking' | 'savings' | 'investment'): string => {
  const iconClasses = {
    checking: 'bg-blue-100 text-blue-600',
    savings: 'bg-green-100 text-green-600',
    investment: 'bg-purple-100 text-purple-600'
  }
  return iconClasses[type]
}
</script>