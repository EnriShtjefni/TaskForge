import { defineStore } from 'pinia'

export type ActivityLog = {
    id: number
    description: string
    causer: { id: number; name: string | null } | null
    subject_type: string | null
    subject_id: number | null
    properties: Record<string, unknown>
    created_at: string
}

export type ActivityLogsMeta = {
    current_page: number
    last_page: number
    total: number
    from: number | null
    to: number | null
}

export const useActivityLogsStore = defineStore('activityLogs', {
    state: () => ({
        logs: [] as ActivityLog[],
        meta: null as ActivityLogsMeta | null,
        loading: false,
    }),

    actions: {
        async fetch(page = 1) {
            const { $api } = useNuxtApp()
            this.loading = true
            try {
                const { data } = await $api.get('/api/activity-logs', {
                    params: { page },
                })
                this.logs = data.data ?? []
                this.meta = data.meta ?? null
            } finally {
                this.loading = false
            }
        },
    },
})
