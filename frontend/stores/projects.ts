import { defineStore } from 'pinia'

export type Project = {
    id: number
    name: string
    organization: { id: number; name: string }
    organization_id?: number
    members: { id: number; name: string }[]
}

export const useProjectStore = defineStore('projects', {
    state: () => ({
        projects: [] as Project[],
        loading: false,
    }),

    actions: {
        async create(payload: {
            name: string
            organization_id: number
            members?: number[]
        }) {
            const { $api } = useNuxtApp()
            const toast = useToast()
            const orgStore = useOrganizationStore()
            try {
                const { data } = await $api.post('/api/projects', payload)
                await orgStore.fetch()
                toast.success({ message: 'Project created' })
                return data.data
            } catch {
                toast.error({ message: 'Could not create project' })
                throw new Error('Create failed')
            }
        },

        async update(
            id: number,
            payload: {
                name: string
                organization_id: number
                members?: number[]
            }
        ) {
            const { $api } = useNuxtApp()
            const toast = useToast()
            const orgStore = useOrganizationStore()
            try {
                const { data } = await $api.put(`/api/projects/${id}`, payload)
                await orgStore.fetch()
                toast.success({ message: 'Project updated' })
                return data.data
            } catch {
                toast.error({ message: 'Could not update project' })
                throw new Error('Update failed')
            }
        },

        async delete(id: number) {
            const { $api } = useNuxtApp()
            const toast = useToast()
            const orgStore = useOrganizationStore()
            try {
                await $api.delete(`/api/projects/${id}`)
                await orgStore.fetch()
                toast.success({ message: 'Project deleted' })
            } catch {
                toast.error({ message: 'Could not delete project' })
                throw new Error('Delete failed')
            }
        },
    },
})
