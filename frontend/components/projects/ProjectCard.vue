<script setup lang="ts">
import type { Project } from '@/stores/projects'

defineProps<{
  project: Project
  canManage: boolean
}>()

defineEmits<{
  edit: [project: Project]
  delete: [id: number]
}>()
</script>

<template>
  <div class="bg-white dark:bg-gray-800 p-4 rounded shadow">
    <h2 class="font-semibold text-lg dark:text-white">
      {{ project.name }}
    </h2>
    <p class="text-sm text-gray-500 dark:text-gray-400">
      Members:
      <span v-if="project.members && project.members.length > 0">
        {{ project.members.map(m => m.name).join(', ') }}
      </span>
    </p>
    <div v-if="canManage" class="flex gap-2 mt-4">
      <button
          type="button"
          class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700"
          @click="$emit('edit', project)"
      >
        Edit
      </button>
      <button
          type="button"
          class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700"
          @click="$emit('delete', project.id)"
      >
        Delete
      </button>
    </div>
  </div>
</template>
