import { inject, ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'

export default () => {
  const $route = inject('$route')
  const importingInvoiceImport = ref({})

  const importImport = () => {
    Inertia.post($route('invoices.imports.start', importingInvoiceImport.value), null, {
      preserveScroll: true,
      onFinish () {
        importingInvoiceImport.value = {}
      }
    })
  }

  return {
    importingInvoiceImport,
    importImport,
  }
}
