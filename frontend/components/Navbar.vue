<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useDarkMode } from '~/composables/useDarkMode'
import { onClickOutside } from '@vueuse/core'
import sunIcon from '~/assets/icons/sun.svg'
import moonIcon from '~/assets/icons/moon.svg'
import arrowDownIcon from '~/assets/icons/arrowDown.svg'

const authUser = useAuthStore()
const { isDark, toggle, init } = useDarkMode()
const route = useRoute()

onMounted(init)

const openLogoutDropdown = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)

onClickOutside(dropdownRef, () => {
  openLogoutDropdown.value = false
})

const isActive = (to: string) => {
  if (to === '/dashboard') return route.path === '/dashboard'
  if (to === '/organizations') return route.path === '/organizations'
  if (to === '/activity-logs') return route.path === '/activity-logs'
  return route.path.startsWith(to)
}
</script>

<template>
  <nav
      class="bg-white dark:bg-gray-800 shadow px-4 py-3 flex justify-between items-center gap-4"
  >
    <div class="flex items-center min-w-0">
      <NuxtLink
          to="/dashboard"
          class="font-bold text-xl text-gray-800 dark:text-white hover:opacity-90 shrink-0"
      >
        TaskForge
      </NuxtLink>
    </div>

    <div
        v-if="authUser.isAuthenticated"
        class="hidden sm:flex items-center justify-center gap-1 flex-1"
    >
      <NuxtLink
          to="/dashboard"
          class="px-3 py-2 rounded text-sm font-medium transition-colors"
          :class="isActive('/dashboard')
                  ? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white'
                  : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
      >
        Dashboard
      </NuxtLink>

      <NuxtLink
          to="/organizations"
          class="px-3 py-2 rounded text-sm font-medium transition-colors"
          :class="isActive('/organizations')
                   ? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white'
                   : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
      >
        Organizations
      </NuxtLink>

      <NuxtLink
          to="/tasks"
          class="px-3 py-2 rounded text-sm font-medium transition-colors"
          :class="isActive('/tasks')
                   ? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white'
                   : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
      >
        Tasks
      </NuxtLink>

      <NuxtLink
          to="/activity-logs"
          class="px-3 py-2 rounded text-sm font-medium transition-colors"
          :class="isActive('/activity-logs')
                   ? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white'
                   : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
      >
        Activity Logs
      </NuxtLink>
    </div>

    <div class="flex items-center gap-4 shrink-0">
      <button
          @click="toggle"
          class="w-8 h-8 flex items-center justify-center bg-gray-200 dark:bg-gray-500 rounded-full"
          aria-label="Toggle dark mode"
      >
        <img
            :src="isDark ? moonIcon : sunIcon"
            class="w-5 h-5"
            alt="Theme toggle"
        />
      </button>

      <div
          v-if="authUser.isAuthenticated"
          ref="dropdownRef"
          class="relative"
      >
        <button
            @click="openLogoutDropdown = !openLogoutDropdown"
            class="flex items-center gap-2 text-gray-700 dark:text-gray-200 font-medium"
        >
          {{ authUser.user?.name }}
          <img
              :src="arrowDownIcon"
              class="w-3 h-3 dark:invert"
              alt="User Icon"
          />
        </button>

        <div
            v-if="openLogoutDropdown"
            class="absolute right-0 mt-4 w-32 bg-white dark:bg-gray-700 rounded shadow-md border dark:border-gray-600"
        >
          <button
              @click="() => {
                openLogoutDropdown = false
                authUser.logout()
              }"
              class="w-full text-left px-4 py-2 text-sm text-black dark:text-white hover:bg-gray-100 dark:hover:bg-gray-600"
          >
            Logout
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>
