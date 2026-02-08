export function useTaskBroadcast(projectId: Ref<number | null>) {
    const nuxt = useNuxtApp()
    const $echo = (nuxt as any).$echo
    const tasksStore = useTasksStore()
    let channel: any = null
    let channelName = ''

    const subscribe = (id: number) => {
        if (!$echo) return
        channelName = `project.${id}`
        channel = $echo.private(channelName)
        channel
            .listen('.task.created', (e: { task: any }) => {
                if (e.task) tasksStore.mergeTask(e.task)
            })
            .listen('.task.updated', (e: { task: any }) => {
                if (e.task) tasksStore.mergeTask(e.task)
            })
            .listen('.task.deleted', (e: { task_id: number }) => {
                if (e.task_id) tasksStore.removeTask(e.task_id)
            })
    }

    const unsubscribe = () => {
        if (channel) {
            channel.stopListening('.task.created')
            channel.stopListening('.task.updated')
            channel.stopListening('.task.deleted')
            if ($echo && channelName) $echo.leave(channelName)
            channel = null
            channelName = ''
        }
    }

    watch(
        projectId,
        (id) => {
            unsubscribe()
            if (id) subscribe(id)
        },
        { immediate: true }
    )

    onUnmounted(() => unsubscribe())
}
