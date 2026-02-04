<script setup lang="ts">
import type { Organization } from '@/stores/organizations'
import type { Project } from '@/stores/projects'
import OrganizationCard from '@/components/organizations/OrganizationCard.vue'
import ProjectCard from '@/components/projects/ProjectCard.vue'

const props = defineProps<{
  organization: Organization
}>()

const emit = defineEmits<{
  editOrg: [org: Organization]
  deleteOrg: [id: number]
  createProject: [org: Organization]
  editProject: [project: Project]
  deleteProject: [id: number]
}>()

const projects = computed(() => props.organization.projects ?? [])
const canManageProjects = computed(() => props.organization.role === 'owner')
</script>

<template>
  <section class="space-y-4">
    <OrganizationCard
        :organization="organization"
        :is-owner="organization.role === 'owner'"
        @edit="emit('editOrg', organization)"
        @delete="emit('deleteOrg', organization.id)"
    />

    <div class="ml-4 sm:ml-6 space-y-3">
      <div class="flex items-center justify-between gap-2">
        <h3 class="text-lg font-semibold dark:text-white">Projects</h3>
        <button
            v-if="canManageProjects"
            type="button"
            class="bg-green-600 text-white px-3 py-1.5 rounded hover:bg-green-700 text-sm"
            @click="emit('createProject', organization)"
        >
          + Add Project
        </button>
      </div>

      <div
          v-if="projects.length === 0"
          class="text-gray-500 dark:text-gray-400 text-sm"
      >
          No projects in this organization.
      </div>
      <div
          v-else
          class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3"
      >
        <ProjectCard
            v-for="project in projects"
            :key="project.id"
            :project="project"
            :can-manage="canManageProjects"
            @edit="emit('editProject', project)"
            @delete="emit('deleteProject', project.id)"
        />
      </div>
    </div>
  </section>
</template>
