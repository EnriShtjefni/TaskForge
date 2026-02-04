<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useOrganizationStore } from '@/stores/organizations'
import {
  useTasksStore,
  type Task,
  type TaskStatus,
  TASK_STATUSES,
  STATUS_TRANSITIONS,
} from '@/stores/tasks'
import { onMounted, ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()
const orgStore = useOrganizationStore()
const tasksStore = useTasksStore()

const selectedProjectId = ref<number | null>(null)
const draggedTask = ref<Task | null>(null)
const dragOverColumn = ref<TaskStatus | null>(null)

onMounted(async () => {
  if (!auth.isAuthenticated) {
    router.push('/login')
    return
  }
  await orgStore.fetch()
})

const projects = computed(() => orgStore.allProjects)

const selectedProject = computed(() =>
    projects.value.find((p) => p.id === selectedProjectId.value)
)

watch(selectedProjectId, (id) => {
  tasksStore.clear()
  if (id) tasksStore.fetchByProject(id)
})

const columnLabels: Record<TaskStatus, string> = {
  todo: 'To Do',
  in_progress: 'In Progress',
  review: 'Review',
  done: 'Done',
}

const tasksForColumn = (status: TaskStatus) =>
    tasksStore.tasksByStatus(status)

const canDrop = (task: Task, targetStatus: TaskStatus) => {
  if (task.status === targetStatus) return false
  return STATUS_TRANSITIONS[task.status]?.includes(targetStatus) ?? false
}

const onDragStart = (e: DragEvent, task: Task) => {
  if (!e.dataTransfer) return
  draggedTask.value = task
  e.dataTransfer.effectAllowed = 'move'
  e.dataTransfer.setData('text/plain', String(task.id))
  e.dataTransfer.setData('application/json', JSON.stringify({ taskId: task.id, status: task.status }))
}

const onDragEnd = () => {
  draggedTask.value = null
  dragOverColumn.value = null
}

const onDragOver = (e: DragEvent, status: TaskStatus) => {
  e.preventDefault()
  if (!e.dataTransfer) return
  e.dataTransfer.dropEffect = 'move'
  if (draggedTask.value && canDrop(draggedTask.value, status)) {
    dragOverColumn.value = status
  }
}

const onDragLeave = () => {
  dragOverColumn.value = null
}

const onDrop = (e: DragEvent, targetStatus: TaskStatus) => {
  e.preventDefault()
  dragOverColumn.value = null
  const task = draggedTask.value
  if (!task || !canDrop(task, targetStatus)) return
  tasksStore.updateStatus(task.id, targetStatus)
  draggedTask.value = null
}
</script>

<template>
  <div class="p-10 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
      <h1 class="text-2xl sm:text-3xl font-bold dark:text-white">
        Task Board (Kanban)
      </h1>
      <div class="flex-1 min-w-0">
        <select
            v-model="selectedProjectId"
            class="w-full sm:max-w-xs px-4 py-2 rounded border dark:bg-gray-800 dark:border-gray-600 dark:text-white"
        >
          <option :value="null">Select a project</option>
          <option
              v-for="p in projects"
              :key="p.id"
              :value="p.id"
          >
            {{ p.name }} — {{ p.organization?.name ?? '' }}
          </option>
        </select>
      </div>
    </div>

    <p
        v-if="!selectedProjectId"
        class="text-gray-500 dark:text-gray-400"
    >
      Select a project to view and manage tasks.
    </p>

    <div
        v-else-if="tasksStore.loading"
        class="text-gray-500"
    >
      Loading tasks...
    </div>

    <div
        v-else
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 overflow-x-auto"
    >
      <div
          v-for="status in TASK_STATUSES"
          :key="status"
          class="min-w-[260px] rounded-lg bg-white dark:bg-gray-800 p-3 border-2 transition-colors shadow"
          :class="
                    dragOverColumn === status
                        ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                        : 'border-transparent'
                "
          @dragover="(e) => onDragOver(e, status)"
          @dragleave="onDragLeave"
          @drop="(e) => onDrop(e, status)"
      >
        <h2 class="font-semibold text-sm text-gray-700 dark:text-gray-300 mb-3 px-1">
          {{ columnLabels[status] }}
        </h2>
        <div class="space-y-2 min-h-[120px]">
          <div
              v-for="task in tasksForColumn(status)"
              :key="task.id"
              draggable="true"
              class="p-3 rounded bg-white dark:bg-gray-700 shadow border border-gray-200 dark:border-gray-600 cursor-grab active:cursor-grabbing"
              @dragstart="(e) => onDragStart(e, task)"
              @dragend="onDragEnd"
          >
            <p class="font-medium text-gray-900 dark:text-white text-sm">
              {{ task.name }}
            </p>
            <p
                v-if="task.description"
                class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2"
            >
              {{ task.description }}
            </p>
            <p
                v-if="task.assignee"
                class="text-xs text-gray-500 dark:text-gray-400 mt-1"
            >
              {{ task.assignee.name }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>