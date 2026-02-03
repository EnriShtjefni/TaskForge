<script setup lang="ts">
defineProps<{ project: any }>()
defineEmits(['edit', 'delete'])
</script>

<template>
  <div class="bg-white dark:bg-gray-800 p-4 rounded shadow">
    <h2 class="font-semibold text-lg dark:text-white">
      {{ project.name }}
    </h2>

    <p class="text-sm text-gray-500">
      {{ project.organization.name }}
    </p>

    <div class="flex gap-2 mt-4">
      <button
          @click="$emit('edit', project)"
          class="bg-blue-600 text-white px-3 py-1 rounded"
      >
        Edit
      </button>
      <button
          @click="$emit('delete', project.id)"
          class="bg-red-600 text-white px-3 py-1 rounded"
      >
        Delete
      </button>
    </div>
  </div>
</template>
