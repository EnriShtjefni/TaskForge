import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null as any,
        users: [] as any[],
        loading: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.user,
        currentUser: (state) => state.user,
    },

    actions: {
        async csrf() {
            const { $api } = useNuxtApp()
            await $api.get('/sanctum/csrf-cookie')
        },

        async login(credentials: { email: string; password: string }) {
            const { $api } = useNuxtApp()
            this.loading = true

            try {
                await this.csrf()
                const { data } = await $api.post('/auth/login', credentials)
                this.user = data.user
            } catch (error: any) {
                throw error.response?.data || error
            } finally {
                this.loading = false
            }
        },

        async register(payload: {
            name: string
            email: string
            password: string
            password_confirmation: string
        }) {
            const { $api } = useNuxtApp()
            this.loading = true

            try {
                await this.csrf()
                const { data } = await $api.post('/auth/register', payload)
                this.user = data.user
            } catch (error: any) {
                throw error.response?.data || error
            } finally {
                this.loading = false
            }
        },

        async fetchUser() {
            const { $api } = useNuxtApp()
            try {
                const { data } = await $api.get('/auth/me')
                this.user = data
            } catch {
                this.user = null
            }
        },

        async fetchUsers() {
            const { $api } = useNuxtApp()
            try {
                const { data } = await $api.get('/auth/users')
                this.users = data.data
            } catch {
                this.users = []
            }
        },

        async logout() {
            const { $api } = useNuxtApp()
            const router = useRouter()

            await $api.post('/auth/logout')
            this.user = null

            await router.push('/login')
        },
    },
})
