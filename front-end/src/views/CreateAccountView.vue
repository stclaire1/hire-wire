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
            <label class="flex items-start space-x-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
              <input 
                type="radio" 
                v-model="accountType" 
                value="checking"
                class="mt-1 text-blue-600 focus:ring-blue-500"
              >
              <div>
                <span class="font-medium text-gray-900">Conta Corrente</span>
                <p class="text-sm text-gray-500">Para movimentações do dia a dia.</p>
              </div>
            </label>
            <label class="flex items-start space-x-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
              <input 
                type="radio" 
                v-model="accountType" 
                value="savings"
                class="mt-1 text-blue-600 focus:ring-blue-500"
              >
              <div>
                <span class="font-medium text-gray-900">Conta Poupança</span>
                <p class="text-sm text-gray-500">Para guardar dinheiro de forma prática</p>
              </div>
            </label>
            <label class="flex items-start space-x-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
              <input 
                type="radio" 
                v-model="accountType" 
                value="investment"
                class="mt-1 text-blue-600 focus:ring-blue-500"
              >
              <div>
                <span class="font-medium text-gray-900">Conta Investimento</span>
                <p class="text-sm text-gray-500">Para aplicações e investimentos de maior rentabilidade</p>
              </div>
            </label>
          </div>
        </div>

        <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-3">
          <p class="text-red-600 text-sm">{{ error }}</p>
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
            :disabled="!accountType || loading"
            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
          >
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

const router = useRouter()
const accountStore = useAccountStore()

const accountType = ref<'checking' | 'savings' | 'investment' | ''>('')

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

const handleSubmit = async () => {
  if (!accountType.value) return
  // clear any previous errors
  accountStore.clearError()
  const result = await accountStore.createAccount({
    account_type: accountType.value
  })

  if (result.success) {
    // clear any success messages that might be set as errors
    if (isSuccessMessage(accountStore.error)) {
      accountStore.clearError()
    }
    
    // redirect to home
    router.push('/home')
  } else {
  }
}

const goBack = () => {
  router.push('/home')
}
</script>
