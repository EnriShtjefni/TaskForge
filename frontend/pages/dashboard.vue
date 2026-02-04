<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useOrganizationStore } from '@/stores/organizations'
import { onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()
const orgStore = useOrganizationStore()

onMounted(async () => {
  if (!auth.isAuthenticated) {
    router.push('/login')
    return
  }
  await orgStore.fetch()
})

const totalOrganizations = computed(
    () => orgStore.organizations.length
)

const totalProjects = computed(() =>
    orgStore.organizations.reduce(
        (sum, org) => sum + (org.projects?.length ?? 0),
        0
    )
)

const cards = computed(() => [
  {
    label: 'Organizations',
    value: totalOrganizations.value,
    href: '/organizations',
    color: 'bg-blue-600 hover:bg-blue-700',
    icon: '🏢',
  },
  {
    label: 'Projects',
    value: totalProjects.value,
    href: '/organizations',
    color: 'bg-green-600 hover:bg-green-700',
    icon: '📁',
  },
])
</script>

<template>
  <div class="p-10 space-y-8">
    <section class="space-y-4">
      <h1 class="text-2xl sm:text-3xl font-bold dark:text-white">
        Dashboard
      </h1>
      <p class="text-gray-500 dark:text-gray-400">
        Hi {{ auth.user?.name }}. Here’s an overview of your workspace.
      </p>
    </section>

    <div v-if="orgStore.loading" class="text-gray-500">
      Loading...
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-6">
      <NuxtLink
          v-for="card in cards"
          :key="card.label"
          :to="card.href"
          class="block p-6 rounded-lg shadow bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow"
      >
        <div class="flex items-start justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
              {{ card.label }}
            </p>
            <p class="mt-2 text-3xl font-bold dark:text-white">
              {{ card.value }}
            </p>
          </div>
          <span class="text-3xl" aria-hidden="true">{{ card.icon }}</span>
        </div>
        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
          View details →
        </p>
      </NuxtLink>
    </div>

    <section class="pt-4">
      <div class="flex flex-wrap gap-4">
        <NuxtLink
            to="/organizations"
            class="inline-flex items-center px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700"
        >
          Organizations & Projects
        </NuxtLink>
        <NuxtLink
            to="/tasks"
            class="inline-flex items-center px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600"
        >
          Task Board (Kanban)
        </NuxtLink>
      </div>
    </section>
  </div>
</template>
