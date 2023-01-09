<template>
  <div class="p-1">
    <SonarMenuItem v-if="showView" is="InertiaLink" :href="`/my-invoices/${invoice.uuid}`">
      {{ __('View invoice') }}
    </SonarMenuItem>
    <SonarMenuItem @click.prevent="copy(invoice.invoice_number)">
      {{ __('Copy invoice number') }}
    </SonarMenuItem>
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import SonarMenuItem from '@/components/forms/SonarMenuItem.vue'
import ConvertInvoiceModal from '@/components/modals/ConvertInvoiceModal.vue'
import InvoiceStatusModal from '@/components/modals/InvoiceStatusModal.vue'
import copiesToClipboard from '@/composition/copiesToClipboard.js'

export default defineComponent({
  components: {
    InvoiceStatusModal,
    ConvertInvoiceModal,
    SonarMenuItem,
  },
  props: {
    invoice: Object,
    showView: {
      type: Boolean,
      default: false,
    },
  },

  setup () {
    const { copy } = copiesToClipboard()

    return {
      copy,
    }
  }
})
</script>
