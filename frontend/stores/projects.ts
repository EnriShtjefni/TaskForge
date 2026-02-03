import { defineStore } from 'pinia'

export type Project = {
    id: number
    name: string
    organization: { id: number; name: string }
    members: { id: number; name: string }[]
}

export const useProjectStore = defineStore('projects', {
    state: () => ({
        projects: [] as Project[],
        loading: false,
    }),

    actions: {
        async fetch(orgId: number) {
            const { $api } = useNuxtApp()
            this.loading = true
            const { data } = await $api.get(`/api/organizations/${orgId}/projects`)
            this.projects = data.data
            this.loading = false
        },

        async create(payload: any) {
            const { $api } = useNuxtApp()
            const { data } = await $api.post('/api/projects', payload)
            this.projects.unshift(data.data)
        },

        async update(id: number, payload: any) {
            const { $api } = useNuxtApp()
            const { data } = await $api.put(`/api/projects/${id}`, payload)
            this.projects = this.projects.map(p => p.id === id ? data.data : p)
        },

        async delete(id: number) {
            const { $api } = useNuxtApp()
            await $api.delete(`/api/projects/${id}`)
            this.projects = this.projects.filter(p => p.id !== id)
        },
    },
})
