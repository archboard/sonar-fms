import { inject, ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'

export default () => {
  const $route = inject('$route')
  const rollingBackImport = ref({})

  const rollBack = () => {
    Inertia.post($route('invoices.imports.rollback', rollingBackImport.value), null, {
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
