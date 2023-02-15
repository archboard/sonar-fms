import { inject, ref } from 'vue'
import { router } from '@inertiajs/vue3'

export default () => {
  const rollingBackImport = ref({})

  const rollBack = (route) => {
    router.post(route, null, {
      preserveScroll: true,
      onFinish () {
        rollingBackImport.value = {}
      }
    })
  }

  return {
    rollingBackImport,
    rollBack,
  }
}
