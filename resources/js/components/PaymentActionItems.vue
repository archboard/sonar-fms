<template>
  <div class="p-1">
    <SonarMenuItem @click.prevent="$emit('details', payment)">
      <template v-if="payment.edited">
        {{ __('Details and changelog') }}
      </template>
      <template v-else>
        {{ __('Details') }}
      </template>
    </SonarMenuItem>
    <SonarMenuItem is="a" target="_blank" :href="`/payments/${payment.id}/receipt`">
      {{ __('Receipt') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('payments.update')" is="inertia-link" :href="`/payments/${payment.id}/edit`">
      {{ __('Edit') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('invoices.viewAny') && payment.invoice" is="inertia-link" :href="`/invoices/${payment.invoice.uuid}`">
      {{ __('View invoice') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="payment.invoice" @click.prevent="copy(payment.invoice.invoice_number)">
      {{ __('Copy invoice number') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('students.viewAny') && payment.invoice" is="inertia-link" :href="`/students/${payment.invoice.student_uuid}`">
      {{ __('View student') }}
    </SonarMenuItem>
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import SonarMenuItem from '@/components/forms/SonarMenuItem'
import checksPermissions from '@/composition/checksPermissions'
import copiesToClipboard from '@/composition/copiesToClipboard'

export default defineComponent({
  components: {
    SonarMenuItem,
  },
  emits: ['details'],
  props: {
    payment: Object,
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
