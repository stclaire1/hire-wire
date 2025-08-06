<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full mx-4">
      <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">
          Seja bem-vindo, {{ user?.name }}!
        </h1>
        <p class="text-gray-600 mb-6">
          Você foi autenticado com sucesso.
        </p>
        <button 
          @click="handleLogout" 
          :disabled="loading" 
          class="w-full rounded bg-red-600 px-4 py-2 text-white transition-colors hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-gray-500"
        >
          {{ loading ? 'Saindo...' : 'Sair' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user)
const loading = computed(() => authStore.loading)

onMounted(() => {
  // Redirect if not authenticated
  if (!authStore.isAuthenticated) {
    router.push('/login')
  }
})

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>