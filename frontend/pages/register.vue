<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { reactive } from 'vue'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const submit = async () => {
  await auth.register(form)
  await router.push('/')
}
</script>

<template>
  <div class="max-w-md mx-auto mt-20 bg-white dark:bg-gray-800 p-6 rounded shadow">
    <h2 class="text-2xl font-bold text-center mb-4 dark:text-white">Register</h2>

    <input v-model="form.name" placeholder="Name" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:text-white" />
    <input v-model="form.email" type="email" placeholder="Email" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:text-white mt-2" />
    <input v-model="form.password" type="password" placeholder="Password" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:text-white mt-2" />
    <input v-model="form.password_confirmation" type="password" placeholder="Confirm Password" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:text-white mt-2" />

    <button @click="submit" class="btn-primary w-full mt-4 bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
      Register
    </button>

    <p class="mt-4 text-sm text-center text-gray-600 dark:text-gray-300">
      Already have an account?
      <NuxtLink to="/login" class="text-blue-600 hover:underline">
        Login now
      </NuxtLink>
    </p>
  </div>
</template>
