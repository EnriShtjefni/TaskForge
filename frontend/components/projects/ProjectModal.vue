<script setup lang="ts">
import { useProjectStore } from '@/stores/projects'
import { useOrganizationStore } from '@/stores/organizations'
import { useAuthStore } from '@/stores/auth'

const emit = defineEmits(['close'])
const projectStore = useProjectStore()
const orgStore = useOrganizationStore()
const authStore = useAuthStore()

const props = defineProps<{ project?: any | null }>()

const isEdit = computed(() => !!props.project)

const form = reactive({
  name: props.project?.name ?? '',
  organization_id: props.project?.organization?.id ?? null,
  members: props.project?.members?.map(m => m.id) ?? [],
})

const ownerOrgs = computed(() =>
    orgStore.organizations.filter(o => o.role === 'owner')
)

const orgUsers = computed(() => {
  const org = orgStore.organizations.find(o => o.id === form.organization_id)
  return org?.members ?? []
})

const submit = async () => {
  if (!form.name || !form.organization_id) return

  const payload = {
    name: form.name,
    organization_id: form.organization_id,
    members: form.members,
  }

  isEdit.value
      ? await projectStore.update(props.project.id, payload)
      : await projectStore.create(payload)

  emit('close')
}
</script>

<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 p-6 rounded w-96 space-y-4">
      <h2 class="text-xl font-bold dark:text-white">
        {{ isEdit ? 'Edit Project' : 'Create Project' }}
      </h2>

      <input v-model="form.name" placeholder="Project name" class="input" />

      <select v-model="form.organization_id" class="input">
        <option disabled :value="null">Select organization</option>
        <option v-for="o in ownerOrgs" :key="o.id" :value="o.id">
          {{ o.name }}
        </option>
      </select>

      <select v-model="form.members" multiple class="input h-32">
        <option v-for="u in orgUsers" :key="u.user_id" :value="u.user_id">
          {{ u.name }}
        </option>
      </select>

      <div class="flex justify-end gap-2">
        <button @click="$emit('close')" class="px-4 py-2">Cancel</button>
        <button @click="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
          {{ isEdit ? 'Update' : 'Create' }}
        </button>
      </div>
    </div>
  </div>
</template>
