import axios from 'axios'

export default defineNuxtPlugin(() => {
    const config = useRuntimeConfig()

    const api = axios.create({
        baseURL: config.public.apiBase,
        withCredentials: true,
        withXSRFToken: true,
        xsrfCookieName: 'XSRF-TOKEN',
        xsrfHeaderName: 'X-XSRF-TOKEN',
        headers: {
            Accept: 'application/json',
        },

    })

    return {
        provide: { api },
    }
})
