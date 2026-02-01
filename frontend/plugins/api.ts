import axios from 'axios'

export default defineNuxtPlugin(() => {
    const config = useRuntimeConfig()

    const api = axios.create({

        withCredentials: true,
        xsrfCookieName: 'XSRF-TOKEN',
        xsrfHeaderName: 'X-XSRF-TOKEN',

        baseURL: config.public.apiBase,
        headers: {
            Accept: 'application/json',
        },

    })

    return {
        provide: { api },
    }
})
