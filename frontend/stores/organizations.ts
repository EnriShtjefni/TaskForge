import { defineStore } from 'pinia'

export type Organization = {
    id: number
    name: string
    role: 'owner' | 'manager' | 'member'
    members?: OrganizationMember[]
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
    }),

    getters: {
        isOwner: () => (org: Organization) => org.role === 'owner',
    },

    actions: {
        async fetch() {
            const { $api, $toast } = useNuxtApp()
            this.loading = true

            const { data } = await $api.get('/api/organizations')
            this.organizations = data.data

            this.loading = false
        },

        async create(payload: { name: string }) {
            const { $api, $toast } = useNuxtApp()
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
            const { $api, $toast } = useNuxtApp()
            const toast = useToast()

            try {
                const { data } = await $api.put(`/api/organizations/${id}`, payload)

                const index = this.organizations.findIndex(o => o.id === id)
                if (index !== -1) {
                    this.organizations[index] = data.data
                }

                toast.success({ message: 'Organization updated' })
            } catch {
                toast.error({ message: 'Could not update organization' })
            }
        },

        async delete(id: number) {
            const { $api, $toast } = useNuxtApp()
            const toast = useToast()

            try {
                await $api.delete(`/api/organizations/${id}`)
                this.organizations = this.organizations.filter(o => o.id !== id)

                toast.success({ message: 'Organization deleted' })
            } catch {
                toast.error({ message: 'Could not delete organization' })
            }
        },
    },
})
