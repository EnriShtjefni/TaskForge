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
import TaskModal from '@/components/tasks/TaskModal.vue'

const auth = useAuthStore()
const router = useRouter()
const orgStore = useOrganizationStore()
const tasksStore = useTasksStore()
const authStore = useAuthStore()

const selectedProjectId = computed({
  get: () => tasksStore.selectedProjectId,
  set: (val) => tasksStore.setSelectedProject(val),
})
const draggedTask = ref<Task | null>(null)
const dragOverColumn = ref<TaskStatus | null>(null)

const search = ref('')
const filterStatus = ref<TaskStatus | ''>('')
const filterAssignedTo = ref<number | null>(null)
const page = ref(1)

const showTaskModal = ref(false)
const editingTask = ref<Task | null>(null)

onMounted(async () => {
  if (!auth.isAuthenticated) {
    await auth.fetchUser()
  }

  if (!auth.isAuthenticated) {
    await router.push('/login')
    return
  }

  await orgStore.fetch()
})

const projects = computed(() => orgStore.allProjects)

const selectedProject = computed(() =>
    projects.value.find((p) => p.id === selectedProjectId.value)
)

const canManageTasks = computed(() => {
    const project = selectedProject.value
    if (!project?.organization?.id) return false
    const org = orgStore.organizations.find(
        (o) => o.id === project.organization?.id
    )
    return org?.role === 'owner' || org?.role === 'manager'
})

const canChangeStatus = (task: Task) => {
    const uid = auth.user?.id
    if (uid == null) return false
    if (task.assigned_to == null) return false
    return Number(task.assigned_to) === Number(uid)
}

const assigneeOptions = computed(() => {
    const project = selectedProject.value
    if (!project?.members?.length) return []
    return project.members
})

const canComment = computed(() =>
    selectedProject.value?.members?.some((m) => m.id === auth.user?.id) ?? false
)

useTaskBroadcast(selectedProjectId)

watch(selectedProjectId, (id) => {
    tasksStore.clear()
    page.value = 1
    if (id) fetchTasks()
})

watch([search, filterStatus, filterAssignedTo, page], () => {
    if (selectedProjectId.value) fetchTasks()
})

function fetchTasks() {
    if (!selectedProjectId.value) return
    const params: Record<string, string | number> = {
        page: page.value,
    }
    if (search.value.trim()) params.search = search.value.trim()
    if (filterStatus.value) params.status = filterStatus.value
    if (filterAssignedTo.value) params.assigned_to = filterAssignedTo.value
    tasksStore.fetchByProject(selectedProjectId.value, params)
}

const meta = computed(() => tasksStore.meta)

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

const newCommentText = ref<Record<number, string>>({})

const submitComment = async (task: Task) => {
    const body = newCommentText.value[task.id]?.trim()
    if (!body) return
    await tasksStore.addComment(task.id, body)
    newCommentText.value[task.id] = ''
}

const canDeleteComment = (commentUserId?: number | null) => {
    return commentUserId != null && commentUserId === authStore.user?.id
}

const deleteComment = async (task: Task, commentId: number) => {
    await tasksStore.deleteComment(task.id, commentId)
}

const openCreateTask = () => {
    editingTask.value = null
    showTaskModal.value = true
}

const openEditTask = (task: Task) => {
    editingTask.value = task
    showTaskModal.value = true
}

const closeTaskModal = () => {
    showTaskModal.value = false
    editingTask.value = null
    if (selectedProjectId.value) fetchTasks()
}

const goPrevPage = () => {
    if (meta.value && meta.value.current_page > 1) page.value--
}

const goNextPage = () => {
    if (meta.value && meta.value.current_page < meta.value.last_page)
        page.value++
}
</script>

<template>
    <div class="p-10 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <h1 class="text-2xl sm:text-3xl font-bold dark:text-white">
                Task Board (Kanban)
            </h1>
            <div class="flex-1 min-w-0 flex flex-wrap items-center gap-2">
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
                <button
                    v-if="selectedProjectId && canManageTasks"
                    type="button"
                    class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700"
                    @click="openCreateTask"
                >
                    + Create Task
                </button>
            </div>
        </div>

        <p
            v-if="!selectedProjectId"
            class="text-gray-500 dark:text-gray-400"
        >
            Select a project to view and manage tasks.
        </p>

        <template v-else>
            <div class="flex flex-wrap items-center gap-3">
                <input
                    v-model="search"
                    type="search"
                    placeholder="Search tasks..."
                    class="px-3 py-2 rounded border dark:bg-gray-800 dark:border-gray-600 dark:text-white min-w-[180px]"
                />
                <select
                    v-model="filterAssignedTo"
                    class="px-3 py-2 rounded border dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                >
                    <option :value="null">All assignees</option>
                    <option
                        v-for="m in assigneeOptions"
                        :key="m.id"
                        :value="m.id"
                    >
                        {{ m.name }}
                    </option>
                </select>
            </div>

            <div
                v-if="tasksStore.loading"
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
                            :class="
                                canChangeStatus(task)
                                    ? 'cursor-grab'
                                    : 'cursor-not-allowed opacity-90'
                            "
                            @dragstart="(e) => canChangeStatus(task) ? onDragStart(e, task) : e.preventDefault()"
                            @dragend="onDragEnd"
                            @dblclick="canManageTasks ? openEditTask(task) : null"
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
                            <p
                                v-if="canManageTasks"
                                class="text-xs text-blue-600 dark:text-blue-400 mt-1"
                            >
                                Double-click to edit
                            </p>

                            <div class="mt-3 border-t border-gray-200 dark:border-gray-600 pt-2 space-y-2">
                                <div
                                    v-if="task.comments && task.comments.length"
                                    class="space-y-1 max-h-32 overflow-y-auto pr-1"
                                >
                                    <div
                                        v-for="comment in task.comments"
                                        :key="comment.id"
                                        class="text-xs text-gray-700 dark:text-gray-200 flex items-start gap-2"
                                    >
                                        <div class="flex-1">
                                            <span class="font-semibold">
                                                {{ comment.user?.name ?? 'Unknown' }}:
                                            </span>
                                            <span>
                                                {{ comment.body }}
                                            </span>
                                        </div>
                                        <button
                                            v-if="canDeleteComment(comment.user?.id)"
                                            type="button"
                                            class="text-[10px] text-red-500 hover:text-red-600"
                                            @click.stop="deleteComment(task, comment.id)"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </div>
                                <form
                                    v-if="canComment"
                                    class="flex items-center gap-1"
                                    @submit.prevent="submitComment(task)"
                                >
                                    <input
                                        v-model="newCommentText[task.id]"
                                        type="text"
                                        placeholder="Add a comment..."
                                        class="flex-1 px-2 py-1 rounded border text-xs dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                    />
                                    <button
                                        type="submit"
                                        class="px-2 py-1 text-xs rounded bg-blue-600 text-white hover:bg-blue-700"
                                    >
                                        Send
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="meta && meta.last_page > 1"
                class="flex items-center gap-4"
            >
                <button
                    type="button"
                    class="px-3 py-1 rounded border dark:border-gray-600 dark:text-white disabled:opacity-50"
                    :disabled="meta.current_page <= 1"
                    @click="goPrevPage"
                >
                    Previous
                </button>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    Page {{ meta.current_page }} of {{ meta.last_page }}
                    ({{ meta.total }} tasks)
                </span>
                <button
                    type="button"
                    class="px-3 py-1 rounded border dark:border-gray-600 dark:text-white disabled:opacity-50"
                    :disabled="meta.current_page >= meta.last_page"
                    @click="goNextPage"
                >
                    Next
                </button>
            </div>
        </template>

        <TaskModal
            v-if="showTaskModal && selectedProject"
            :project="selectedProject"
            :task="editingTask"
            @close="closeTaskModal"
        />
    </div>
</template>
