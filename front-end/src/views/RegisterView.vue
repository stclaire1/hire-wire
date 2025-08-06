<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12 sm:px-6 lg:px-8">
    <div class="w-full max-w-lg space-y-8">
      <div class="rounded-lg bg-white px-6 py-8 shadow-md">
        <h2 class="mb-6 text-center text-2xl font-bold text-gray-900">Cadastro</h2>
        
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nome:</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              :disabled="loading"
              placeholder="Digite seu nome completo"
              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm placeholder-gray-400 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
            />
            <span v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</span>
          </div>

          <div>
            <label for="cpf" class="block text-sm font-medium text-gray-700">CPF:</label>
            <input
              id="cpf"
              v-model="form.cpf"
              type="text"
              required
              :disabled="loading"
              placeholder="000.000.000-00"
              @input="formatCPF"
              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm placeholder-gray-400 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
            />
            <span v-if="errors.cpf" class="mt-1 text-sm text-red-600">{{ errors.cpf[0] }}</span>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              :disabled="loading"
              placeholder="Digite seu email"
              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm placeholder-gray-400 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
            />
            <span v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</span>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Senha:</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              :disabled="loading"
              placeholder="Digite sua senha (mínimo 8 caracteres)"
              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm placeholder-gray-400 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
            />
            <span v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</span>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Senha:</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              :disabled="loading"
              placeholder="Confirme sua senha"
              class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm placeholder-gray-400 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
            />
            <span v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ errors.password_confirmation[0] }}</span>
          </div>

          <div v-if="error" class="rounded-md bg-red-50 border border-red-200 p-3">
            <p class="text-sm text-red-800">{{ error }}</p>
          </div>

          <button 
            type="submit" 
            :disabled="loading" 
            class="w-full rounded-md bg-green-600 px-4 py-2 text-white font-medium shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
          >
            {{ loading ? 'Cadastrando...' : 'Cadastrar' }}
          </button>
        </form>

        <div class="mt-6 text-center">
          <p class="text-sm text-gray-600">
            Já tem uma conta? 
            <router-link to="/login" class="font-medium text-blue-600 hover:text-blue-500 hover:underline">
              Entre aqui
            </router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  name: '',
  cpf: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const errors = ref<Record<string, string[]>>({})
const error = ref<string | null>(null)
const loading = ref(false)

const formatCPF = (event: Event) => {
  const target = event.target as HTMLInputElement
  let value = target.value.replace(/\D/g, '')
  
  if (value.length <= 11) {
    value = value.replace(/(\d{3})(\d)/, '$1.$2')
    value = value.replace(/(\d{3})(\d)/, '$1.$2')
    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2')
  }
  
  form.cpf = value
}

const handleSubmit = async () => {
  loading.value = true
  errors.value = {}
  error.value = null

  const result = await authStore.register(form)
  
  if (result.success) {
    router.push('/home')
  } else {
    error.value = result.message
    if (result.errors) {
      errors.value = result.errors
    }
  }
  
  loading.value = false
}
</script>
