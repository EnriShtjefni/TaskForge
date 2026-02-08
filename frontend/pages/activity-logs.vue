<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useActivityLogsStore } from '@/stores/activityLogs'
import { onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()
const activityLogsStore = useActivityLogsStore()

const page = ref(1)

onMounted(async () => {
    if (!auth.isAuthenticated) {
        await auth.fetchUser()
    }
    if (!auth.isAuthenticated) {
        await router.push('/login')
        return
    }
    await activityLogsStore.fetch(page.value)
})

watch(page, (p) => {
    activityLogsStore.fetch(p)
})

const meta = computed(() => activityLogsStore.meta)
const logs = computed(() => activityLogsStore.logs)
const loading = computed(() => activityLogsStore.loading)

const formatDescription = (d: string) => {
    return d.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())
}

const goPrevPage = () => {
    if (meta.value && meta.value.current_page > 1) page.value--
}

const goNextPage = () => {
    if (meta.value && meta.value.current_page < meta.value.last_page)
        page.value++
}
</script>

<template>
    <div class="p-10 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <h1 class="text-2xl sm:text-3xl font-bold dark:text-white">
                Activity Logs
            </h1>
        </div>

        <div v-if="loading" class="text-gray-500">
            Loading...
        </div>

        <div v-else class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th
                            scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                        >
                            Date
                        </th>
                        <th
                            scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                        >
                            Activity
                        </th>
                        <th
                            scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                        >
                            User
                        </th>
                        <th
                            scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                        >
                            Details
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr
                        v-for="log in logs"
                        :key="log.id"
                        class="hover:bg-gray-50 dark:hover:bg-gray-800"
                    >
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                            {{ log.created_at ? new Date(log.created_at).toLocaleString() : '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                            {{ formatDescription(log.description) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                            {{ log.causer?.name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                            <span v-if="Object.keys(log.properties || {}).length">
                                {{ JSON.stringify(log.properties) }}
                            </span>
                            <span v-else>—</span>
                        </td>
                    </tr>
                    <tr v-if="!logs.length">
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            No activity logs yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="meta && meta.last_page > 1"
            class="flex items-center gap-4"
        >
            <button
                type="button"
                class="px-3 py-1 rounded border dark:border-gray-600 dark:text-white disabled:opacity-50"
                :disabled="meta.current_page <= 1"
                @click="goPrevPage"
            >
                Previous
            </button>
            <span class="text-sm text-gray-600 dark:text-gray-400">
                Page {{ meta.current_page }} of {{ meta.last_page }}
                ({{ meta.total }} total)
            </span>
            <button
                type="button"
                class="px-3 py-1 rounded border dark:border-gray-600 dark:text-white disabled:opacity-50"
                :disabled="meta.current_page >= meta.last_page"
                @click="goNextPage"
            >
                Next
            </button>
        </div>
    </div>
</template>
