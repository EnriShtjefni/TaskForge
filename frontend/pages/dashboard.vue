<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { type Organization, useOrganizationStore } from "~/stores/organizations";
import { type Project, useProjectStore } from '~/stores/projects'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import OrganizationModal from '@/components/organizations/OrganizationModal.vue'
import OrganizationCard from "~/components/organizations/OrganizationCard.vue";
import ProjectCard from "~/components/projects/ProjectCard.vue";
import ProjectModal from "~/components/projects/ProjectModal.vue";

const auth = useAuthStore()
const router = useRouter()

const orgStore = useOrganizationStore()
const projectStore = useProjectStore()

const showModal = ref(false)
const editingOrg = ref<Organization | null>(null)

const showProjectModal = ref(false)
const editingProject = ref<Project | null>(null)

onMounted(() => {
  if (!auth.isAuthenticated) {
    router.push('/login')
  }
  orgStore.fetch()
  projectStore.fetch()
})

const editOrg = (org: Organization) => {
  editingOrg.value = org
  showModal.value = true
}

const editProject = (project: Project) => {
  editingProject.value = project
  showProjectModal.value = true
}
</script>

<template>
  <div class="p-10 space-y-6">
    <section class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <h1 class="text-2xl sm:text-3xl font-bold dark:text-white">
          Your Organizations, {{ auth.user?.name }}
        </h1>

        <button
            @click="showModal = true"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full sm:w-auto"
        >
          + Create Organization
        </button>
      </div>

      <div v-if="orgStore.loading" class="text-gray-500">
        Loading organizations...
      </div>

      <div v-else-if="orgStore.organizations.length === 0" class="text-gray-500">
        You are not part of any organization yet.
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <OrganizationCard
            v-for="org in orgStore.organizations"
            :key="org.id"
            :organization="org"
            :is-owner="orgStore.isOwner(org)"
            @edit="editOrg"
            @delete="orgStore.delete"
        />
      </div>
    </section>

    <section class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <h2 class="text-2xl font-bold dark:text-white">
          Projects
        </h2>

        <button
            v-if="projectStore.canCreate"
            @click="showProjectModal = true"
            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
        >
          + Create Project
        </button>
      </div>

      <div v-if="projectStore.loading" class="text-gray-500">
        Loading projects...
      </div>

      <div v-else-if="projectStore.projects.length === 0" class="text-gray-500">
        No projects yet.
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <ProjectCard
            v-for="project in projectStore.projects"
            :key="project.id"
            :project="project"
            :is-owner="projectStore.isOwner(project)"
            @edit="editProject"
            @delete="projectStore.delete"
        />
      </div>
    </section>

    <OrganizationModal
        v-if="showModal"
        :organization="editingOrg"
        @close="showModal = false; editingOrg = null"
    />

    <ProjectModal
        v-if="showProjectModal"
        :project="editingProject"
        @close="showProjectModal = false; editingProject = null"
    />
  </div>
</template>
