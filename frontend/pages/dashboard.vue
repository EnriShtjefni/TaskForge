<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

onMounted(() => {
  if (!auth.isAuthenticated) {
    router.push('/login')
  }
})
</script>

<template>
  <div class="p-10">
    <h1 class="text-3xl font-bold dark:text-white">
      Welcome {{ auth.user?.name }}
    </h1>

    <p class="mt-4 text-gray-600 dark:text-gray-300">
      🎉 You are logged in successfully
    </p>
  </div>
</template>
