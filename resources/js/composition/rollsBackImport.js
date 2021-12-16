import { inject, ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'

export default () => {
  const rollingBackImport = ref({})

  const rollBack = (route) => {
    Inertia.post(route, null, {
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
