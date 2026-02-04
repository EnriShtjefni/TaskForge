<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import {
  type Organization,
  useOrganizationStore,
} from '@/stores/organizations'
import { type Project, useProjectStore } from '@/stores/projects'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import OrganizationModal from '@/components/organizations/OrganizationModal.vue'
import OrganizationSection from '@/components/organizations/OrganizationSection.vue'
import ProjectModal from '@/components/projects/ProjectModal.vue'

const auth = useAuthStore()
const router = useRouter()
const orgStore = useOrganizationStore()
const projectStore = useProjectStore()

const showOrgModal = ref(false)
const editingOrg = ref<Organization | null>(null)

const showProjectModal = ref(false)
const editingProject = ref<Project | null>(null)
const createProjectOrg = ref<Organization | null>(null)

onMounted(() => {
  if (!auth.isAuthenticated) {
    router.push('/login')
    return
  }
  orgStore.fetch()
})

const openEditOrg = (org: Organization) => {
  editingOrg.value = org
  showOrgModal.value = true
}

const openCreateProject = (org: Organization) => {
  createProjectOrg.value = org
  editingProject.value = null
  showProjectModal.value = true
}

const openEditProject = (project: Project) => {
  editingProject.value = project
  createProjectOrg.value = null
  showProjectModal.value = true
}

const closeOrgModal = () => {
  showOrgModal.value = false
  editingOrg.value = null
}

const closeProjectModal = () => {
  showProjectModal.value = false
  editingProject.value = null
  createProjectOrg.value = null
}

const handleDeleteOrg = (id: number) => {
  orgStore.delete(id)
}

const handleDeleteProject = (id: number) => {
  projectStore.delete(id)
}
</script>

<template>
  <div class="p-10 space-y-8">
    <section class="space-y-6">
      <div
          class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4"
      >
        <h1 class="text-2xl sm:text-3xl font-bold dark:text-white">
          Organizations & Projects
        </h1>
        <button
            type="button"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full sm:w-auto"
            @click="editingOrg = null; showOrgModal = true"
        >
          + Create Organization
        </button>
      </div>

      <div v-if="orgStore.loading" class="text-gray-500">
        Loading organizations...
      </div>
      <div
          v-else-if="orgStore.organizations.length === 0"
          class="text-gray-500"
      >
        You are not part of any organization yet.
      </div>
      <div v-else class="space-y-8">
        <OrganizationSection
            v-for="org in orgStore.organizations"
            :key="org.id"
            :organization="org"
            @edit-org="openEditOrg"
            @delete-org="handleDeleteOrg"
            @create-project="openCreateProject"
            @edit-project="openEditProject"
            @delete-project="handleDeleteProject"
        />
      </div>
    </section>

    <OrganizationModal
        v-if="showOrgModal"
        :organization="editingOrg"
        @close="closeOrgModal"
    />

    <ProjectModal
        v-if="showProjectModal"
        :project="editingProject"
        :preselected-organization="createProjectOrg"
        @close="closeProjectModal"
    />
  </div>
</template>