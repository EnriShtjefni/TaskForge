import { defineStore } from 'pinia'

export const TASK_STATUSES = [
    'todo',
    'in_progress',
    'review',
    'done',
] as const

export type TaskStatus = (typeof TASK_STATUSES)[number]

export const STATUS_TRANSITIONS: Record<TaskStatus, TaskStatus[]> = {
    todo: ['in_progress'],
    in_progress: ['review'],
    review: ['done', 'in_progress'],
    done: [],
}

export type Task = {
    id: number
    name: string
    description: string | null
    status: TaskStatus
    assigned_to: number | null
    assignee: { id: number; name: string } | null
    project_id: number
    created_at: string
    comments?: {
        id: number
        body: string
        created_at: string
        user?: { id: number; name: string } | null
    }[]
}

export type TaskListParams = {
    search?: string
    assigned_to?: number
    page?: number
}

export type PaginationMeta = {
    current_page: number
    last_page: number
    total: number
    from: number | null
    to: number | null
}

export const useTasksStore = defineStore('tasks', {
    state: () => ({
        tasks: [] as Task[],
        selectedProjectId: null as number | null,
        meta: null as PaginationMeta | null,
        loading: false,
    }),

    getters: {
        tasksByStatus: (state) => (status: TaskStatus) =>
            state.tasks.filter((t) => t.status === status),
        canTransition: () => (from: TaskStatus, to: TaskStatus) =>
            STATUS_TRANSITIONS[from]?.includes(to) ?? false,
    },

    actions: {
        async fetchByProject(projectId: number, params: TaskListParams = {}) {
            const { $api } = useNuxtApp()
            this.loading = true
            try {
                const { data } = await $api.get(
                    `/api/projects/${projectId}/tasks`,
                    { params }
                )
                this.tasks = data.data ?? []
                this.meta = data.meta ?? null
            } finally {
                this.loading = false
            }
        },

        async updateStatus(taskId: number, newStatus: TaskStatus) {
            const { $api } = useNuxtApp()
            const toast = useToast()
            const task = this.tasks.find((t) => t.id === taskId)
            if (!task || !STATUS_TRANSITIONS[task.status]?.includes(newStatus)) {
                toast.error({ message: 'Invalid status transition' })
                return
            }
            const previousStatus = task.status
            const index = this.tasks.findIndex((t) => t.id === taskId)
            if (index !== -1) this.tasks[index] = { ...task, status: newStatus }
            try {
                const { data } = await $api.put(
                    `/api/tasks/${taskId}/status`,
                    { status: newStatus }
                )
                if (index !== -1) this.tasks[index] = data.data
                toast.success({ message: 'Status updated' })
            } catch {
                if (index !== -1)
                    this.tasks[index] = { ...task, status: previousStatus }
                toast.error({ message: 'Could not update status' })
            }
        },

        async create(payload: {
            project_id: number
            name: string
            description?: string | null
            assigned_to?: number | null
        }) {
            const { $api } = useNuxtApp()
            const toast = useToast()
            try {
                const { data } = await $api.post('/api/tasks', payload)
                const task = data?.data ?? data
                if (task && typeof task.id === 'number') {
                    this.tasks.push(task)
                }
                toast.success({ message: 'Task created' })
                return task
            } catch {
                toast.error({ message: 'Could not create task' })
                throw new Error('Create failed')
            }
        },

        async update(
            taskId: number,
            payload: {
                name?: string
                description?: string | null
                assigned_to?: number | null
            }
        ) {
            const { $api } = useNuxtApp()
            const toast = useToast()
            const task = this.tasks.find((t) => t.id === taskId)
            if (!task) return
            try {
                const { data } = await $api.put(`/api/tasks/${taskId}`, payload)
                const index = this.tasks.findIndex((t) => t.id === taskId)
                if (index !== -1) this.tasks[index] = data.data
                toast.success({ message: 'Task updated' })
            } catch {
                toast.error({ message: 'Could not update task' })
                await this.fetchByProject(task.project_id)
            }
        },

        async delete(taskId: number) {
            const { $api } = useNuxtApp()
            const toast = useToast()
            const task = this.tasks.find((t) => t.id === taskId)
            if (!task) return
            try {
                await $api.delete(`/api/tasks/${taskId}`)
                this.tasks = this.tasks.filter((t) => t.id !== taskId)
                toast.success({ message: 'Task deleted' })
            } catch {
                toast.error({ message: 'Could not delete task' })
            }
        },

        setSelectedProject(projectId: number | null) {
            this.selectedProjectId = projectId
        },

        async addComment(taskId: number, body: string) {
            const { $api } = useNuxtApp()
            const toast = useToast()
            const task = this.tasks.find((t) => t.id === taskId)
            if (!task) return
            try {
                const { data } = await $api.post('/api/comments', {
                    task_id: taskId,
                    body,
                })
                const comment = data?.data ?? data
                const index = this.tasks.findIndex((t) => t.id === taskId)
                if (index !== -1) {
                    const existingComments = this.tasks[index].comments ?? []
                    this.tasks[index] = {
                        ...this.tasks[index],
                        comments: [...existingComments, comment],
                    }
                }
                toast.success({ message: 'Comment added' })
            } catch (err: any) {
                const status = err?.response?.status
                const message =
                    status === 403
                        ? 'Forbidden – you are not authorized to comment on this task.'
                        : 'Could not add comment'
                toast.error({ message })
            }
        },

        async deleteComment(taskId: number, commentId: number) {
            const { $api } = useNuxtApp()
            const toast = useToast()
            const task = this.tasks.find((t) => t.id === taskId)
            if (!task) return
            try {
                await $api.delete(`/api/comments/${commentId}`)
                const index = this.tasks.findIndex((t) => t.id === taskId)
                if (index !== -1) {
                    this.tasks[index] = {
                        ...this.tasks[index],
                        comments:
                            this.tasks[index].comments?.filter(
                                (c) => c.id !== commentId
                            ) ?? [],
                    }
                }
                toast.success({ message: 'Comment deleted' })
            } catch {
                toast.error({ message: 'Could not delete comment' })
            }
        },

        mergeTask(task: Task) {
            const index = this.tasks.findIndex((t) => t.id === task.id)
            if (index !== -1) this.tasks[index] = task
            else this.tasks.push(task)
        },

        removeTask(taskId: number) {
            this.tasks = this.tasks.filter((t) => t.id !== taskId)
        },

        clear() {
            this.tasks = []
            this.meta = null
        },
    },
})
