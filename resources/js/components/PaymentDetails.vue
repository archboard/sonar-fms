<template>
  <DescriptionList>
    <DescriptionItem>
      <template #dt>
        {{ __('Invoice') }}
      </template>
      <template #dd>
        <Link :href="`/invoices/${payment.invoice.uuid}`">
          {{ payment.invoice.invoice_number }}
        </Link>
      </template>
    </DescriptionItem>
    <DescriptionItem v-if="payment.invoice.student">
      <template #dt>
        {{ __('Student') }}
      </template>
      <template #dd>
        {{ payment.invoice.student.full_name }} ({{ payment.invoice.student.student_number }})
      </template>
    </DescriptionItem>
    <DescriptionItem v-if="payment.invoice.students.length > 0">
      <template #dt>
        {{ __('Students') }}
      </template>
      <template #dd>
        <ul>
          <li v-for="student in payment.invoice.students">
            {{ student.full_name }} ({{ student.student_number }})
          </li>
        </ul>
      </template>
    </DescriptionItem>
    <DescriptionItem>
      <template #dt>
        {{ __('Payment') }}
      </template>
      <template #dd>
        {{ payment.amount_formatted }}
      </template>
    </DescriptionItem>
    <DescriptionItem v-if="payment.payment_method">
      <template #dt>
        {{ __('Payment method') }}
      </template>
      <template #dd>
        {{ payment.payment_method.driver_data.label }}
      </template>
    </DescriptionItem>
    <DescriptionItem v-if="payment.transaction_details">
      <template #dt>
        {{ __('Transaction details') }}
      </template>
      <template #dd>
        {{ payment.transaction_details }}
      </template>
    </DescriptionItem>
    <DescriptionItem v-if="termNumber !== 0">
      <template #dt>
        {{ __('Payment schedule') }}
      </template>
      <template #dd>
        {{ __('Paid toward payment :number of :total_payments payments', { number: termNumber, total_payments: payment.schedule.terms.length }) }}
      </template>
    </DescriptionItem>
    <DescriptionItem>
      <template #dt>
        {{ __('Date paid') }}
      </template>
      <template #dd>
        {{ payment.paid_at_formatted }}
      </template>
    </DescriptionItem>
    <DescriptionItem>
      <template #dt>
        {{ __('Recorded by') }}
      </template>
      <template #dd>
        {{ payment.recorded_by.full_name }}
      </template>
    </DescriptionItem>
    <DescriptionItem v-if="payment.made_by">
      <template #dt>
        {{ __('Paid by') }}
      </template>
      <template #dd>
        {{ payment.made_by?.full_name }}
      </template>
    </DescriptionItem>
    <DescriptionItem>
      <template #dt>
        <strong class="text-gray-900">{{ __('Remaining balance') }}</strong>
      </template>
      <template #dd>
        <strong>{{ payment.invoice.remaining_balance_formatted }}</strong>
      </template>
    </DescriptionItem>
  </DescriptionList>
</template>

<script>
import { defineComponent } from 'vue'
import DescriptionList from '@/components/tables/DescriptionList'
import DescriptionItem from '@/components/tables/DescriptionItem'
import Link from '@/components/Link'

export default defineComponent({
  components: {
    DescriptionItem,
    DescriptionList,
    Link,
  },
  props: {
    payment: Object,
  },

  setup ({ payment }) {
    document.documentElement.classList.remove('dark')
    const termNumber = payment.payment_term
      ? payment.schedule.terms.findIndex(t => t.uuid === payment.payment_term.uuid) + 1
      : 0

    return {
      termNumber,
    }
  }
})
</script>
