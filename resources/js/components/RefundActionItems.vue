<template>
  <div class="p-1">
    <SonarMenuItem @click.prevent="$emit('details', refund)">
      {{ __('Details') }}
    </SonarMenuItem>
    <SonarMenuItem is="a" target="_blank" :href="`/refunds/${refund.id}/receipt`">
      {{ __('Receipt') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('invoices.view') && refund.invoice" is="inertia-link" :href="`/invoices/${refund.invoice.uuid}`">
      {{ __('View invoice') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('students.view') && refund.invoice" is="inertia-link" :href="`/students/${refund.invoice.student_uuid}`">
      {{ __('View student') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="refund.invoice" @click.prevent="copy(refund.invoice.invoice_number)">
      {{ __('Copy invoice number') }}
    </SonarMenuItem>
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import SonarMenuItem from '@/components/forms/SonarMenuItem.vue'
import checksPermissions from '@/composition/checksPermissions.js'
import copiesToClipboard from '@/composition/copiesToClipboard.js'

export default defineComponent({
  components: {
    SonarMenuItem,
  },
  emits: ['details'],
  props: {
    refund: Object,
  },

  setup () {
    const { can } = checksPermissions()
    const { copy } = copiesToClipboard()

    return {
      can,
      copy,
    }
  }
})
</script>
