<template>
  <div class="p-1">
    <SonarMenuItem v-if="showView" is="InertiaLink" :href="`/my-invoices/${invoice.uuid}`">
      {{ __('View invoice') }}
    </SonarMenuItem>
    <SonarMenuItem is="a" :href="`/invoices/${invoice.uuid}/preview`" target="_blank">
      {{ __('Preview PDF') }}
    </SonarMenuItem>
    <SonarMenuItem is="a" :href="`/invoices/${invoice.uuid}/pdf`" target="_blank">
      {{ __('Download PDF') }}
    </SonarMenuItem>
    <SonarMenuItem @click.prevent="copy(invoice.invoice_number)">
      {{ __('Copy invoice number') }}
    </SonarMenuItem>
  </div>
</template>

<script setup>
import SonarMenuItem from '@/components/forms/SonarMenuItem.vue'
import copiesToClipboard from '@/composition/copiesToClipboard.js'

const props = defineProps({
  invoice: Object,
  showView: {
    type: Boolean,
    default: false,
  },
})
const { copy } = copiesToClipboard()
</script>
