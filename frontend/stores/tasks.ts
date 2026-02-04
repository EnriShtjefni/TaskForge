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
}

export const useTasksStore = defineStore('tasks', {
    state: () => ({
        tasks: [] as Task[],
        loading: false,
    }),

    getters: {
        tasksByStatus: (state) => (status: TaskStatus) =>
            state.tasks.filter((t) => t.status === status),
        canTransition: () => (from: TaskStatus, to: TaskStatus) =>
            STATUS_TRANSITIONS[from]?.includes(to) ?? false,
    },

    actions: {
        async fetchByProject(projectId: number) {
            const { $api } = useNuxtApp()
            this.loading = true
            try {
                const { data } = await $api.get(
                    `/api/projects/${projectId}/tasks`
                )
                this.tasks = data.data ?? []
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
            try {
                const { data } = await $api.put(
                    `/api/tasks/${taskId}/status`,
                    { status: newStatus }
                )
                const index = this.tasks.findIndex((t) => t.id === taskId)
                if (index !== -1) this.tasks[index] = data.data
                toast.success({ message: 'Status updated' })
            } catch {
                toast.error({ message: 'Could not update status' })
                await this.fetchByProject(task.project_id)
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
                this.tasks.push(data.data)
                toast.success({ message: 'Task created' })
                return data.data
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

        clear() {
            this.tasks = []
        },
    },
})
