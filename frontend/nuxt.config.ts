// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: ['@pinia/nuxt', 'nuxt-toast'],
  css: ['@/assets/css/main.css'],
  runtimeConfig: {
    public: {
      apiBase: process.env.BACKEND_URL,
      reverbKey: process.env.VITE_REVERB_APP_KEY || '',
      reverbHost: process.env.VITE_REVERB_HOST || 'localhost',
      reverbPort: process.env.VITE_REVERB_PORT || '8080',
      reverbScheme: process.env.VITE_REVERB_SCHEME || 'http',
    },
  },
  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {},
    },
  },
})