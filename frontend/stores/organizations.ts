import { defineStore } from 'pinia'
import type { Project } from './projects'

export type Organization = {
    id: number
    name: string
    role: 'owner' | 'manager' | 'member'
    members?: OrganizationMember[]
    projects?: Project[]
}

export type OrganizationMember = {
    user_id: number
    name?: string
    role: 'manager' | 'member'
}

export const useOrganizationStore = defineStore('organizations', {
    state: () => ({
        organizations: [] as Organization[],
        loading: false,
        selectedOrganizationId: null as number | null,
    }),

    getters: {
        isOwner: () => (org: Organization) => org.role === 'owner',
        canManageProjects: () => (org: Organization) =>
            org.role === 'owner' || org.role === 'manager',
        projectsForOrg: (state) => (orgId: number) => {
            const org = state.organizations.find((o) => o.id === orgId)
            return org?.projects ?? []
        },
        allProjects: (state) =>
            state.organizations.flatMap((o) => o.projects ?? []),
    },

    actions: {
        async fetch() {
            const { $api } = useNuxtApp()
            this.loading = true
            try {
                const { data } = await $api.get('/api/organizations')
                this.organizations = data.data ?? []
            } finally {
                this.loading = false
            }
        },

        async create(payload: { name: string }) {
            const { $api } = useNuxtApp()
            const toast = useToast()
            try {
                const { data } = await $api.post('/api/organizations', payload)
                this.organizations.unshift(data.data)
                toast.success({ message: 'Organization created' })
            } catch {
                toast.error({ message: 'Could not create organization' })
            }
        },

        async update(id: number, payload: { name: string }) {
            const { $api } = useNuxtApp()
            const toast = useToast()
            try {
                const { data } = await $api.put(`/api/organizations/${id}`, payload)
                const index = this.organizations.findIndex((o) => o.id === id)
                if (index !== -1) this.organizations[index] = data.data
                toast.success({ message: 'Organization updated' })
            } catch {
                toast.error({ message: 'Could not update organization' })
            }
        },

        async delete(id: number) {
            const { $api } = useNuxtApp()
            const toast = useToast()
            try {
                await $api.delete(`/api/organizations/${id}`)
                this.organizations = this.organizations.filter((o) => o.id !== id)
                toast.success({ message: 'Organization deleted' })
            } catch {
                toast.error({ message: 'Could not delete organization' })
            }
        },

        setSelectedOrg(id: number | null) {
            this.selectedOrganizationId = id
        },
    },
})
