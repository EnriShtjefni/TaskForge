<script setup lang="ts">
import type { Organization } from '@/stores/organizations'

defineProps<{
  organization: Organization
  isOwner: boolean
}>()

defineEmits(['edit', 'delete'])
</script>

<template>
  <div class="bg-white dark:bg-gray-800 p-4 rounded shadow flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
    <div>
      <h2 class="font-semibold text-lg dark:text-white">
        {{ organization.name }}
      </h2>
      <p class="text-sm text-gray-500">
        Role: {{ organization.role }}
      </p>
    </div>

    <div v-if="isOwner" class="flex gap-2">
      <button
          @click="$emit('edit', organization)"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
      >
        Edit
      </button>
      <button
          @click="$emit('delete', organization.id)"
          class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
      >
        Delete
      </button>
    </div>
  </div>
</template>
