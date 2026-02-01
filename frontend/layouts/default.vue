<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <Navbar />
    <main class="p-4">
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
import Navbar from "../components/Navbar.vue";
</script>