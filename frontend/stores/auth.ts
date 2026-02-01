import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null as any,
        loading: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.user,
    },

    actions: {
        async csrf() {
            const { $api } = useNuxtApp()
            await $api.get('/sanctum/csrf-cookie')
        },

        async login(credentials: { email: string; password: string }) {
            const { $api } = useNuxtApp()
            this.loading = true

            await this.csrf()
            const csrf = useCookie('XSRF-TOKEN').value
            console.log('csrf cookie:', csrf)

            const { data } = await $api.post('/api/auth/login', credentials)
            console.log("data: ", data)

            this.user = data.user
            this.loading = false
        },

        async register(payload: {
            name: string
            email: string
            password: string
            password_confirmation: string
        }) {
            const { $api } = useNuxtApp()
            this.loading = true

            await this.csrf()
            const { data } = await $api.post('/api/auth/register', payload)

            this.user = data.user
            this.loading = false
        },

        async fetchUser() {
            const { $api } = useNuxtApp()
            try {
                const { data } = await $api.get('/api/auth/me')
                this.user = data
            } catch {
                this.user = null
            }
        },

        async logout() {
            const { $api } = useNuxtApp()
            await $api.post('/api/auth/logout')
            this.user = null
        },
    },
})
