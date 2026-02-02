export const useBackendToast = () => {
    const toast = useToast()

    const showError = (error: any) => {
        if (error?.message) {
            toast.error({ message: error.message })
            return
        }

        if (error?.errors) {
            Object.values(error.errors)
                .flat()
                .forEach((msg: any) => {
                    toast.error({ message: String(msg) })
                })
            return
        }

        toast.error({ message: 'Something went wrong' })
    }

    return { showError }
}
