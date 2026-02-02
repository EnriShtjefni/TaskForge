<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import {type Organization, useOrganizationStore} from "~/stores/organizations";
import OrganizationModal from '@/components/organizations/OrganizationModal.vue'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import OrganizationCard from "~/components/organizations/OrganizationCard.vue";

const auth = useAuthStore()
const router = useRouter()

const orgStore = useOrganizationStore()

const showModal = ref(false)
const editingOrg = ref<Organization | null>(null)

onMounted(() => {
  if (!auth.isAuthenticated) {
    router.push('/login')
  }
  orgStore.fetch()
})

const editOrg = (org: Organization) => {
  editingOrg.value = org
  showModal.value = true
}
</script>

<template>
  <div class="p-10 space-y-6">
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

    <OrganizationModal
        v-if="showModal"
        :organization="editingOrg"
        @close="showModal = false; editingOrg = null"
    />
  </div>
</template>
