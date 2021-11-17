<template>
  <div class="p-1">
    <SonarMenuItem v-if="can('invoices.viewAny') && showView" is="inertia-link" :href="$route('invoices.show', invoice)">
      {{ __('View') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('invoices.update') && !invoice.is_void && invoice.published_at" @click.prevent="$emit('editStatus')">
      {{ __('Change status') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('invoices.update') && !invoice.published_at" is="inertia-link" :href="$route('invoices.edit', invoice)">
      {{ __('Edit') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('invoices.update') && !invoice.published_at" is="inertia-link" :href="$route('batches.edit', invoice.batch_id)">
      {{ __('Edit batch') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('invoices.update') && !invoice.published_at" is="inertia-link" :href="$route('invoices.publish', invoice)" as="button" method="put" preserve-scroll>
      {{ __('Publish') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('payments.create') && invoice.amount_due > 0 && !invoice.is_void && invoice.published_at" is="inertia-link" :href="`/payments/create?invoice_uuid=${invoice.uuid}`">
      {{ __('Record payment') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('students.viewAny') && invoice.student" is="inertia-link" :href="$route('students.show', invoice.student)">
      {{ __('View student') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('invoices.create') && invoice.student" is="inertia-link" :href="$route('students.invoices.create', invoice.student)">
      {{ __('New invoice for student') }}
    </SonarMenuItem>
  </div>
  <div class="p-1">
    <SonarMenuItem v-if="can('invoices.viewAny')" is="a" :href="$route('invoices.preview', invoice)" target="_blank">
      {{ __('Preview PDF') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('invoices.viewAny')" is="a" :href="$route('invoices.download', invoice)" target="_blank">
      {{ __('View PDF') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('invoices.create')" is="inertia-link" :href="$route('invoices.duplicate', invoice)">
      {{ __('Duplicate') }}
    </SonarMenuItem>
    <SonarMenuItem @click.prevent="$emit('convertToTemplate')">
      {{ __('Convert to template') }}
    </SonarMenuItem>
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import SonarMenuItem from '@/components/forms/SonarMenuItem'
import checksPermissions from '@/composition/checksPermissions'
import ConvertInvoiceModal from '@/components/modals/ConvertInvoiceModal'
import InvoiceStatusModal from '@/components/modals/InvoiceStatusModal'

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
  emits: ['editStatus', 'convertToTemplate'],

  setup () {
    const { can, canAny } = checksPermissions()

    return {
      can,
      canAny,
    }
  }
})
</script>
