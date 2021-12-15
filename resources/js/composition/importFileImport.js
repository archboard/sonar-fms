import { inject, ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'

export default () => {
  const importingImport = ref({})

  const importImport = (route) => {
    Inertia.post(route, null, {
      preserveScroll: true,
      onFinish () {
        importingImport.value = {}
      }
    })
  }

  return {
    importingImport,
    importImport,
  }
}
