import { inject, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'

export default () => {
  const $http = inject('$http')
  const $route = inject('$route')

  // Ping every minute to make sure the user is authenticated
  // Redirect to the login page if session expired
  const interval = setInterval(async () => {
    try {
      await $http.get($route('auth.status'))
    } catch (err) {
      clearInterval(interval)
      router.visit($route('login'))
    }
  }, 60000)

  // Before the component is unmounted,
  // clear the interval to prevent a memory leak
  onBeforeUnmount(() => {
    clearInterval(interval)
  })
}
