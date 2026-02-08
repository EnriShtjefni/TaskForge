/**
 * Laravel Echo plugin for real-time task updates using Laravel Reverb.
 */
export default defineNuxtPlugin(async () => {
    const config = useRuntimeConfig()
    if (!config.public.reverbKey) {
        return { provide: { echo: null } }
    }

    if (typeof window === 'undefined') {
        return { provide: { echo: null } }
    }

    try {
        const Echo = (await import('laravel-echo')).default
        const Pusher = (await import('pusher-js')).default
        const { $api } = useNuxtApp()

        const wsHost = config.public.reverbHost || window.location.hostname || 'localhost'
        const wsPort = Number(config.public.reverbPort || 8080)
        const scheme = config.public.reverbScheme || 'http'
        const forceTLS = scheme === 'https'

        ;(window as any).Pusher = Pusher

        const echo = new Echo({
            broadcaster: 'reverb',
            key: config.public.reverbKey,
            wsHost,
            wsPort,
            wssPort: wsPort,
            forceTLS,
            authorizer: (channel: any) => ({
                authorize: (socketId: string, callback: (err: boolean, data?: any) => void) => {
                    $api
                        .post('/broadcasting/auth', {
                            socket_id: socketId,
                            channel_name: channel.name,
                        })
                        .then((r: any) => callback(false, r.data))
                        .catch(() => callback(true))
                },
            }),
        })

        return { provide: { echo } }
    } catch {
        return { provide: { echo: null } }
    }
})
