<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4">
      <!-- back button -->
      <div class="mb-6">
        <button
          @click="goBack"
          class="flex items-center text-gray-600 hover:text-gray-900 transition-colors"
        >
          <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Voltar para Home
        </button>
      </div>

      <!-- loading -->
      <div v-if="loading" class="bg-white rounded-lg shadow-lg p-8">
        <div class="text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          <p class="text-gray-600 mt-2">Carregando detalhes da conta...</p>
        </div>
      </div>

      <!-- account not found -->
      <div v-else-if="!account" class="bg-white rounded-lg shadow-lg p-8">
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L3.732 19c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Conta não encontrada</h3>
          <p class="text-gray-600">A conta que você está procurando não foi encontrada.</p>
        </div>
      </div>

      <!-- account details -->
      <div v-else class="space-y-6">
        <!-- account header -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <div class="flex items-start justify-between">
            <div class="flex items-center space-x-4">
              <div :class="getAccountIconClass(account.account_type)" class="h-12 w-12 rounded-full flex items-center justify-center">
                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
              </div>
              <div>
                <h1 class="text-2xl font-bold text-gray-900">
                  {{ getAccountTypeName(account.account_type) }}
                </h1>
                <p class="text-gray-600">{{ account.account_number }}</p>
              </div>
            </div>
            <div class="text-right flex flex-col items-end">
              <p class="text-3xl font-bold text-gray-900 mb-2">
                R$ {{ formatCurrency(account.balance) }}
              </p>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Ativa
              </span>
            </div>
          </div>
        </div>

        <!-- deposit action -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Transações</h2>
          <button
            @click="showDepositForm = true"
            class="w-full flex items-center justify-center px-4 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors font-medium"
          >
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Realizar Depósito
          </button>
        </div>

        <!-- deposit form -->
        <div v-if="showDepositForm" class="bg-white rounded-lg shadow-lg p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Realizar Depósito</h3>
            <button
              @click="cancelDeposit"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="handleDeposit" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Valor do Depósito
              </label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                  R$
                </span>
                <input
                  v-model="depositAmount"
                  type="number"
                  step="0.01"
                  :min="accountTypeInfo?.minDepositValue || 0.01"
                  :max="accountTypeInfo?.maxDepositValue || 50000"
                  placeholder="0,00"
                  class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  required
                >
              </div>
              <p v-if="accountTypeInfo" class="text-xs text-gray-500 mt-1">
                Valor mínimo: R$ {{ accountTypeInfo.minDeposit }} | 
                Valor máximo: R$ {{ accountTypeInfo.maxDeposit }}
              </p>
            </div>

            <!-- validation errors -->
            <div v-if="depositValidationError" class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
              <p class="text-yellow-800 text-sm">{{ depositValidationError }}</p>
            </div>

            <!-- API errors -->
            <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-3">
              <div class="flex">
                <svg class="h-5 w-5 text-red-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L3.732 19c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <p class="text-red-600 text-sm ml-2">{{ error }}</p>
              </div>
            </div>

            <div class="flex space-x-4">
              <button
                type="button"
                @click="cancelDeposit"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="!isDepositValid || depositLoading"
                class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors flex items-center justify-center"
              >
                <div v-if="depositLoading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                {{ depositLoading ? 'Depositando...' : 'Confirmar Depósito' }}
              </button>
            </div>
          </form>
        </div>

        <!-- transaction history -->
        <div class="bg-white rounded-lg shadow-lg p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Histórico de Transações</h2>
          
          <!-- loading transactions -->
          <div v-if="transactionsLoading" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-600">Carregando transações...</p>
          </div>
          
          <!-- no transactions -->
          <div v-else-if="transactions.length === 0" class="text-center py-8">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 mb-4">
              <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma transação</h3>
            <p class="text-gray-600">Ainda não há transações nesta conta.</p>
          </div>
          
          <!-- transactions list -->
          <div v-else class="space-y-3">
            <div 
              v-for="transaction in transactions" 
              :key="transaction.id"
              class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <div class="flex items-center space-x-3">
                <div :class="getTransactionIconClass(transaction.transaction_type)" class="h-10 w-10 rounded-full flex items-center justify-center">
                  <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path v-if="transaction.transaction_type === 'deposit'" d="M12 4v16m8-8H4"/>
                    <path v-else-if="transaction.transaction_type === 'monthly_correction'" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    <path v-else d="M12 4v16m8-8H4"/>
                  </svg>
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ transaction.transaction_description }}</p>
                  <p class="text-sm text-gray-500">{{ transaction.formatted_date }}</p>
                </div>
              </div>
              <div class="text-right">
                <p :class="getTransactionAmountClass(transaction.transaction_type)" class="font-semibold">
                  {{ transaction.formatted_amount }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- error for account loading -->
        <div v-if="error && !loading" class="bg-red-50 border border-red-200 rounded-lg p-4">
          <div class="flex">
            <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L3.732 19c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <div class="ml-3">
              <p class="text-red-800">{{ error }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAccountStore } from '@/stores/account'
import accountService from '@/services/accountService'
import depositService from '@/services/depositService'
import type { Account, Transaction } from '@/types'
import { 
  getAccountTypeName, 
  getAccountIconClass, 
  formatCurrency,
  validateDepositAmount,
  getAccountTypeInfo
} from '@/utils/accountUtils'

const router = useRouter()
const route = useRoute()
const accountStore = useAccountStore()

const account = ref<Account | null>(null)
const transactions = ref<Transaction[]>([])
const loading = ref(true)
const transactionsLoading = ref(false)
const showDepositForm = ref(false)
const depositAmount = ref('')
const depositLoading = ref(false)
const errorMessage = ref<string | null>(null)

const accountId = computed(() => String(route.params.id))

const error = computed(() => {
  return errorMessage.value || accountStore.error
})

const depositValidationError = computed(() => {
  if (!depositAmount.value || !account.value) return null
  
  const amount = parseFloat(depositAmount.value)
  const validation = validateDepositAmount(amount, account.value.account_type)
  
  return validation.isValid ? null : validation.error
})

const isDepositValid = computed(() => {
  return depositAmount.value && !depositValidationError.value
})

const accountTypeInfo = computed(() => {
  return account.value ? getAccountTypeInfo(account.value.account_type) : null
})

const loadAccountData = async () => {
  try {
    // load account details
    const response = await accountService.getAccount(accountId.value)
    if (response.success && response.data) {
      account.value = response.data
    } else {
      console.error('Failed to load account:', response.message)
      errorMessage.value = response.message || 'Erro ao carregar conta'
    }
    
    // load transactions
    await loadTransactions()
  } catch (error: any) {
    console.error('Error loading account:', error)
    errorMessage.value = error.message || 'Erro ao carregar conta'
  }
}

const loadTransactions = async () => {
  if (!accountId.value) return
  
  transactionsLoading.value = true
  try {
    const response = await accountService.getAccountTransactions(accountId.value)
    if (response.success && response.data) {
      transactions.value = response.data
    } else {
      transactions.value = []
    }
  } catch (error: any) {
    console.error('Error loading transactions:', error)
    // Don't show error for transactions, just leave empty
    transactions.value = []
  } finally {
    transactionsLoading.value = false
  }
}

const refreshBalance = async () => {
  if (!accountId.value || !account.value) return
  
  try {
    const response = await accountService.getAccountBalance(accountId.value)
    if (response.success && response.data) {
      account.value.balance = response.data.balance
    }
  } catch (error: any) {
    console.error('Error refreshing balance:', error)
  }
}

onMounted(async () => {
  try {
    await loadAccountData()
  } catch (error) {
    console.error('Error in onMounted:', error)
  } finally {
    loading.value = false
  }
})

const goBack = () => {
  router.push('/home')
}

const cancelDeposit = () => {
  showDepositForm.value = false
  depositAmount.value = ''
  accountStore.clearError()
  errorMessage.value = null
}

const handleDeposit = async () => {
  if (!isDepositValid.value || !account.value) return
  
  depositLoading.value = true
  errorMessage.value = null
  
  try {
    const amount = parseFloat(depositAmount.value)
    
    // Make the deposit API call - pass the data as an object
    const result = await depositService.makeDeposit(accountId.value, { amount })
    
    if (result.success && result.data) {
      // Update account balance with the new balance from API
      account.value.balance = result.data.new_balance
      
      // Reload transactions to show the new deposit
      await loadTransactions()
      
      // Reset form
      depositAmount.value = ''
      showDepositForm.value = false
      
      console.log('Depósito realizado com sucesso!')
    } else {
      errorMessage.value = result.message || 'Erro ao realizar depósito'
    }
    
  } catch (error: any) {
    console.error('Deposit error:', error)
    errorMessage.value = error.message || 'Erro ao realizar depósito'
  } finally {
    depositLoading.value = false
  }
}

const getTransactionIconClass = (type: string): string => {
  const iconClasses = {
    deposit: 'bg-green-100 text-green-600',
    monthly_correction: 'bg-blue-100 text-blue-600',
  }
  return iconClasses[type as keyof typeof iconClasses] || 'bg-gray-100 text-gray-600'
}

const getTransactionAmountClass = (type: string): string => {
  const amountClasses = {
    deposit: 'text-green-600',
    monthly_correction: 'text-blue-600',
  }
  return amountClasses[type as keyof typeof amountClasses] || 'text-gray-600'
}

// deposit bonus functions
const hasDepositBonus = (accountType: Account['account_type']): boolean => {
  return accountType === 'checking' || accountType === 'investment'
}

const getDepositBonus = (): number => {
  return 0.50
}

const shouldShowDepositBonus = (transaction: Transaction, accountType: Account['account_type']): boolean => {
  return transaction.transaction_type === 'deposit' && hasDepositBonus(accountType)
}
</script>
