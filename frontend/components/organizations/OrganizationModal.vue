<script setup lang="ts">
import { useOrganizationStore } from '@/stores/organizations'
import { useAuthStore } from '@/stores/auth'
import { reactive, computed, onMounted } from 'vue'

const emit = defineEmits(['close'])

const orgStore = useOrganizationStore()
const authStore = useAuthStore()

const props = defineProps<{
  organization?: {
    id: number
    name: string
    members?: { user_id: number; role: 'manager' | 'member' }[]
  } | null
}>()

const isEdit = computed(() => !!props.organization)
const users = computed(() => authStore.users)
const isNameValid = computed(() => form.name.trim().length > 0)

const form = reactive({
  name: props.organization?.name ?? '',
  members: props.organization?.members
      ? props.organization.members.map(m => ({
        user_id: m.user_id,
        role: m.role,
      }))
      : [],
})

onMounted(() => {
  if (!authStore.users.length) {
    authStore.fetchUsers()
  }
})

const addMember = () => {
  form.members.push({ user_id: null, role: 'member' })
}

const removeMember = (index: number) => {
  form.members.splice(index, 1)
}

const payload = computed(() => ({
  name: form.name,
  members: form.members.filter(m => m.user_id),
}))

const ownerId = computed(() => {
  return authStore.currentUser?.id
})

const selectableUsersFor = (index: number) => {
  const usedIds = form.members
      .map((m, i) => (i === index ? null : m.user_id))
      .filter((id): id is number => !!id)

  return users.value.filter(u => {
    if (u.id === ownerId.value) return false
    return !usedIds.includes(u.id);
  })
}

const submit = async () => {
  if (isEdit.value) {
    await orgStore.update(props.organization!.id, payload.value)
  } else {
    await orgStore.create(payload.value)
  }
  await orgStore.fetch()
  emit('close')
}
</script>

<template>
  <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 p-6 rounded w-96 space-y-4">
      <h2 class="text-xl font-bold dark:text-white">
        {{ isEdit ? 'Edit Organization' : 'Create Organization' }}
      </h2>

      <input
          v-model="form.name"
          placeholder="Organization name"
          class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:text-white"
      />

      <div class="space-y-2">
        <div class="flex justify-between items-center">
          <span class="font-medium dark:text-white">Members</span>
          <button
              @click="addMember"
              class="flex items-center gap-1 px-3 py-1.5
                     bg-green-600 text-white text-sm font-medium
                     rounded hover:bg-green-700
                     focus:outline-none focus:ring-2 focus:ring-green-400"
          >
            Add member
          </button>
        </div>

        <div v-if="form.members.length === 0" class="text-gray-500 dark:text-gray-300">
          No members yet.
        </div>

        <div
            v-for="(member, index) in form.members"
            :key="index"
            class="flex gap-2 items-center"
        >
          <select v-model="member.user_id" class="flex-1 px-2 py-1 border rounded dark:bg-gray-700 dark:text-white">
            <option :value="null" disabled>Select user</option>
            <option
                v-for="u in selectableUsersFor(index)"
                :key="u.id"
                :value="u.id"
            >
              {{ u.name }}
            </option>
          </select>

          <select v-model="member.role" class="w-32 px-2 py-1 border rounded dark:bg-gray-700 dark:text-white">
            <option value="manager">Manager</option>
            <option value="member">Member</option>
          </select>

          <button
              type="button"
              @click="removeMember(index)"
              class="text-red-500 font-bold"
          >
            −
          </button>
        </div>
      </div>

      <div class="flex justify-end gap-2">
        <button
            @click="$emit('close')"
            class="px-4 py-2 text-gray-600"
        >
          Cancel
        </button>
        <button
            @click="submit"
            :disabled="!isNameValid"
            class="px-4 py-2 rounded text-white"
            :class="isNameValid
              ? 'bg-blue-600 hover:bg-blue-700'
              : 'bg-gray-400 cursor-not-allowed'"
        >
          {{ isEdit ? 'Update' : 'Create' }}
        </button>
      </div>
    </div>
  </div>
</template>
