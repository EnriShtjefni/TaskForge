<script setup lang="ts">
import { useProjectStore } from '@/stores/projects'
import { useOrganizationStore } from '@/stores/organizations'
import type { Project } from '@/stores/projects'
import type { Organization } from '@/stores/organizations'
import { onClickOutside } from "@vueuse/core";

const emit = defineEmits<{ close: [] }>()

const projectStore = useProjectStore()
const orgStore = useOrganizationStore()

const membersOpen = ref(false)
const membersDropdownRef = ref<HTMLElement | null>(null)

const props = withDefaults(
    defineProps<{
      project?: Project | null
      preselectedOrganization?: Organization | null
    }>(),
    {
      project: null,
      preselectedOrganization: null,
    }
)

const isEdit = computed(() => !!props.project)

const form = reactive({
  name: props.project?.name ?? '',
  organization_id:
      props.project?.organization?.id ?? props.preselectedOrganization?.id ?? null,
  members: props.project?.members?.map((m) => m.id) ?? [],
})

const ownerOrgs = computed(() =>
    orgStore.organizations.filter(
        (o) => o.role === 'owner' || o.role === 'manager'
    )
)

const selectedOrg = computed(() =>
    orgStore.organizations.find((o) => o.id === form.organization_id)
)

const orgMembersForSelect = computed(() => {
  const org = selectedOrg.value
  if (!org?.members?.length) return []
  return org.members.map((m) => ({
    id: m.user_id,
    name: (m as { user_id: number; name?: string }).name ?? `User ${m.user_id}`,
    role: (m as { user_id: number; name?: string; role?: string }).role ?? '',
  }))
})

const selectedMembers = computed(() =>
    orgMembersForSelect.value.filter((u) => form.members.includes(u.id))
)

const canSubmit = computed(
    () => form.name.trim().length > 0 && form.organization_id != null
)

onClickOutside(membersDropdownRef, () => {
  membersOpen.value = false
})

const toggleMember = (id: number) => {
  if (form.members.includes(id)) {
    form.members = form.members.filter((mId) => mId !== id)
  } else {
    form.members.push(id)
  }
}

const submit = async () => {
  if (!canSubmit.value) return
  const payload = {
    name: form.name.trim(),
    organization_id: form.organization_id!,
    members: form.members,
  }

  if (isEdit.value && props.project) {
    await projectStore.update(props.project.id, payload)
  } else {
    await projectStore.create(payload)
  }
  emit('close')
}

watch(
    () => [props.project, props.preselectedOrganization],
    () => {
      form.name = props.project?.name ?? ''
      form.organization_id =
          props.project?.organization?.id ??
          props.preselectedOrganization?.id ??
          null
      form.members = props.project?.members?.map((m) => m.id) ?? []
    },
    { immediate: true }
)
</script>

<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 p-6 rounded w-96 space-y-4">
      <h2 class="text-xl font-bold dark:text-white">
        {{ isEdit ? 'Edit Project' : 'Create Project' }}
      </h2>

      <input
          v-model="form.name"
          placeholder="Project name"
          class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:text-white"
      />

      <select
          v-model="form.organization_id"
          class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:text-white"
          :disabled="!!preselectedOrganization && !isEdit"
      >
        <option :value="null" disabled>Select organization</option>
        <option v-for="o in ownerOrgs" :key="o.id" :value="o.id">
          {{ o.name }}
        </option>
      </select>

      <div class="space-y-1" ref="membersDropdownRef">
        <label class="block text-sm font-medium dark:text-white">
          Members
        </label>

        <button
            type="button"
            class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:text-white flex items-center justify-between"
            @click="membersOpen = !membersOpen"
        >
          <span v-if="selectedMembers.length === 0" class="text-gray-400">
              Select members
          </span>
          <span v-else class="flex flex-wrap gap-1 text-left">
            <span
                v-for="u in selectedMembers"
                :key="u.id"
                class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100 text-xs"
            >
              {{ u.name }}
            </span>
          </span>
          <span class="ml-2 text-xs text-gray-400">▼</span>
        </button>

        <div
            v-if="membersOpen"
            class="mt-1 max-h-48 overflow-auto border rounded bg-white dark:bg-gray-800 dark:border-gray-600 shadow z-20"
        >
          <div
              v-if="orgMembersForSelect.length === 0"
              class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400"
          >
            No members available for this organization.
          </div>

          <label
              v-for="u in orgMembersForSelect"
              :key="u.id"
              class="flex items-center gap-2 px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200"
          >
            <input
                type="checkbox"
                class="rounded border-gray-300 dark:border-gray-600"
                :value="u.id"
                :checked="form.members.includes(u.id)"
                @change="toggleMember(u.id)"
            />
            <span>{{ u.name }}</span>
            <span class="italic text-gray-500 dark:text-gray-400 text-xs ml-1">
              ({{ u.role }})
            </span>
          </label>
        </div>
      </div>

      <div class="flex justify-end gap-2">
        <button
            type="button"
            class="px-4 py-2 rounded border dark:border-gray-600 dark:text-white"
            @click="emit('close')"
        >
          Cancel
        </button>
        <button
            type="button"
            class="px-4 py-2 rounded text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="!canSubmit"
            @click="submit"
        >
          {{ isEdit ? 'Update' : 'Create' }}
        </button>
      </div>
    </div>
  </div>
</template>
