<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full mx-4">
      <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Criar Nova Conta</h1>
        <p class="text-gray-600 mt-2">Escolha o tipo de conta que deseja abrir</p>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Tipo de Conta
          </label>
          <div class="space-y-3">
            <label 
              v-for="(label, type) in accountTypeOptions" 
              :key="type"
              class="flex items-start space-x-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer transition-colors"
              :class="{ 'border-blue-500 bg-blue-50': accountType === type }"
            >
              <input 
                type="radio" 
                v-model="accountType" 
                :value="type"
                class="mt-1 text-blue-600 focus:ring-blue-500"
              >
              <div class="flex-1">
                <div class="flex items-center space-x-2">
                  <div :class="getAccountIconClass(type as Account['account_type'])" class="h-8 w-8 rounded-full"></div>
                  <span class="font-medium text-gray-900">{{ label }}</span>
                </div>
                <p class="text-sm text-gray-500 mt-1">{{ getAccountDescription(type as Account['account_type']) }}</p>
              </div>
            </label>
          </div>
        </div>

        <!-- validation errors -->
        <div v-if="validationErrors.account_type" class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
          <p class="text-yellow-800 text-sm">{{ validationErrors.account_type }}</p>
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
            @click="goBack"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Voltar
          </button>
          <button
            type="submit"
            :disabled="!isFormValid || loading"
            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors flex items-center justify-center"
          >
            <div v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
            {{ loading ? 'Criando...' : 'Criar Conta' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAccountStore } from '@/stores/account'
import { validateCreateAccountData } from '@/services/accountUtils'
import { ACCOUNT_TYPE_LABELS } from '@/types'
import type { Account, CreateAccountData } from '@/types'

const router = useRouter()
const accountStore = useAccountStore()

const accountType = ref<CreateAccountData['account_type'] | ''>('')

const isSuccessMessage = (message: string | null): boolean => {
  if (!message) return false
  const lowerMessage = message.toLowerCase()
  return lowerMessage.includes('sucesso') || lowerMessage.includes('success')
}

const loading = computed(() => accountStore.loading)
const error = computed(() => {
  const error = accountStore.error
  // filter out success messages that might be incorrectly set as errors
  return isSuccessMessage(error) ? null : error
})

// use ACCOUNT_TYPE_LABELS from types
const accountTypeOptions = computed(() => ACCOUNT_TYPE_LABELS)

// validation using utils function
const formValidation = computed(() => {
  if (!accountType.value) {
    return { isValid: false, errors: {} }
  }
  return validateCreateAccountData({ account_type: accountType.value })
})

const validationErrors = computed(() => formValidation.value.errors)
const isFormValid = computed(() => formValidation.value.isValid && accountType.value !== '')

const getAccountIconClass = (type: Account['account_type']): string => {
  const iconClasses = {
    checking: 'bg-blue-100 text-blue-600',
    savings: 'bg-green-100 text-green-600',
    investment: 'bg-purple-100 text-purple-600'
  }
  return iconClasses[type]
}

const getAccountDescription = (type: Account['account_type']): string => {
  const descriptions = {
    checking: 'Para movimentações do dia a dia e pagamentos.',
    savings: 'Para guardar dinheiro de forma prática e segura.',
    investment: 'Para aplicações e investimentos de maior rentabilidade.'
  }
  return descriptions[type]
}

const handleSubmit = async () => {
  if (!isFormValid.value) return
  
  // clear any previous errors
  accountStore.clearError()
  
  const result = await accountStore.createAccount({
    account_type: accountType.value as CreateAccountData['account_type']
  })

  if (result.success) {
    // clear any success messages that might be set as errors
    if (isSuccessMessage(accountStore.error)) {
      accountStore.clearError()
    }
    
    // redirect to home
    router.push('/home')
  }
}

const goBack = () => {
  router.push('/home')
}
</script>
