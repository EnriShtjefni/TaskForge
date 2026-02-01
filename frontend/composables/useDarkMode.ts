export const useDarkMode = () => {
    const isDark = ref(false)

    const apply = (value: boolean) => {
        isDark.value = value
        document.documentElement.classList.toggle('dark', value)
        localStorage.setItem('theme', value ? 'dark' : 'light')
    }

    const init = () => {
        const savedTheme = localStorage.getItem('theme')

        if (savedTheme) {
            apply(savedTheme === 'dark')
            return
        }
    }

    const toggle = () => {
        apply(!isDark.value)
    }

    return {
        isDark,
        init,
        toggle,
    }
}
