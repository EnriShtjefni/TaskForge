// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: ['@pinia/nuxt', 'nuxt-toast'],
  css: ['@/assets/css/main.css'],
  runtimeConfig: {
    public: {
      apiBase: process.env.BACKEND_URL,
    },
  },
  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {},
    },
  },
})