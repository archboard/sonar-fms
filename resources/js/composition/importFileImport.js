import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

export default () => {
  const importingImport = ref({})

  const importImport = (route) => {
    router.post(route, null, {
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
