<script setup lang="ts">
import { useTasksStore } from '@/stores/tasks'
import type { Task } from '@/stores/tasks'
import type { Project } from '@/stores/projects'

const emit = defineEmits<{ close: [] }>()

const tasksStore = useTasksStore()

const props = withDefaults(
    defineProps<{
        project: Project | null
        task?: Task | null
    }>(),
    { task: null }
)

const isEdit = computed(() => !!props.task)

const form = reactive({
    name: props.task?.name ?? '',
    description: props.task?.description ?? '',
    assigned_to: props.task?.assigned_to ?? null as number | null,
})

const projectMembers = computed(() => props.project?.members ?? [])

const canSubmit = computed(() => form.name.trim().length > 0)

const submit = async () => {
    if (!canSubmit.value || !props.project) return
    if (isEdit.value && props.task) {
        await tasksStore.update(props.task.id, {
            name: form.name.trim(),
            description: form.description.trim() || null,
            assigned_to: form.assigned_to,
        })
    } else {
        await tasksStore.create({
            project_id: props.project.id,
            name: form.name.trim(),
            description: form.description.trim() || null,
            assigned_to: form.assigned_to,
        })
    }
    emit('close')
}

watch(
    () => [props.task, props.project],
    () => {
        form.name = props.task?.name ?? ''
        form.description = props.task?.description ?? ''
        form.assigned_to = props.task?.assigned_to ?? null
    },
    { immediate: true }
)
</script>

<template>
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 p-6 rounded w-full max-w-md space-y-4">
            <h2 class="text-xl font-bold dark:text-white">
                {{ isEdit ? 'Edit Task' : 'Create Task' }}
            </h2>

            <input
                v-model="form.name"
                placeholder="Task name"
                class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:text-white"
            />

            <textarea
                v-model="form.description"
                placeholder="Description (optional)"
                rows="3"
                class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:text-white"
            />

            <div class="space-y-1">
                <label class="block text-sm font-medium dark:text-white">
                    Assignee
                </label>
                <select
                    v-model="form.assigned_to"
                    class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:text-white"
                >
                    <option :value="null">Unassigned</option>
                    <option
                        v-for="m in projectMembers"
                        :key="m.id"
                        :value="m.id"
                    >
                        {{ m.name }}
                    </option>
                </select>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Only project members can be assigned. Only the assignee can
                    change task status.
                </p>
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
