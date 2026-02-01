<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useDarkMode } from "~/composables/useDarkMode";
import { onClickOutside} from "@vueuse/core";
import sunIcon from '~/assets/icons/sun.svg'
import moonIcon from '~/assets/icons/moon.svg'
import arrowDownIcon from '~/assets/icons/arrowDown.svg'

const authUser = useAuthStore()
const { isDark, toggle, init } = useDarkMode()

onMounted(init)

const openLogoutDropdown = ref(false)
const dropdownRef = ref(null)

onClickOutside(dropdownRef, () => {
  openLogoutDropdown.value = false
})
</script>

<template>
  <nav class="bg-white dark:bg-gray-800 shadow px-4 py-3 flex justify-between items-center">
    <h1 class="font-bold text-xl text-gray-800 dark:text-white">
      TaskForge
    </h1>

    <div class="flex items-center gap-4">
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
          {{ authUser.user.name }}
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
              @click="authUser.logout"
              class="w-full text-left px-4 py-2 text-sm text-black dark:text-white hover:bg-gray-100 dark:hover:bg-gray-600"
          >
            Logout
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>
