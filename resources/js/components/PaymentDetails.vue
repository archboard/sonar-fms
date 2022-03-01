<template>
  <div>
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
          {{ __('Payment amount') }}
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
      <DescriptionItem>
        <template #dt>
          {{ __('Recorded on') }}
        </template>
        <template #dd>
          {{ payment.created_at }}
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
      <DescriptionItem v-if="payment.receipts.length > 0">
        <template #dt>
          {{ __('Receipts') }}
        </template>
        <template #dd>
          <ul>
            <li v-for="receipt in payment.receipts" :key="receipt.id">
              <Link is="a" :href="`/receipts/${receipt.id}`">{{ receipt.receipt_number }}</Link>
            </li>
          </ul>
        </template>
      </DescriptionItem>
      <DescriptionItem>
        <template #dt>
          <strong class="text-gray-900 dark:text-gray-100">{{ __('Remaining balance at time of payment') }}</strong>
        </template>
        <template #dd>
          <strong>{{ payment.invoice.remaining_balance_formatted }}</strong>
        </template>
      </DescriptionItem>
    </DescriptionList>

    <div v-if="payment.activities.length > 0" class="mt-4">
      <ModalHeadline>{{ __('Changelog') }}</ModalHeadline>
      <DescriptionList>
        <DescriptionItem v-for="activity in payment.activities" :key="activity.id">
          <template #dt>
            <div class="text-gray-900 dark:text-white">{{ activity.causer.full_name }}</div>
            <HelpText>{{ displayDate(activity.properties.attributes.updated_at, 'abbr') }}</HelpText>
          </template>
          <template #dd>
            <Table>
              <Thead>
                <tr>
                  <Th>{{ __('Field') }}</Th>
                  <Th>{{ __('Original') }}</Th>
                  <Th>{{ __('New') }}</Th>
                </tr>
              </Thead>
              <Tbody>
                <template v-for="change in activity.changes" :key="change.attribute">
                  <tr v-if="change.attribute !== 'updated_at'">
                    <Td class="whitespace-nowrap">
                      <span v-if="change.attribute === 'paid_at'">{{ __('Date paid') }}</span>
                      <span v-if="change.attribute === 'payment_method_id'">{{ __('Payment method') }}</span>
                      <span v-if="change.attribute === 'invoice_payment_term_uuid'">{{ __('Payment term') }}</span>
                      <span v-if="change.attribute === 'transaction_details'">{{ __('Transaction details') }}</span>
                      <span v-if="change.attribute === 'amount'">{{ __('Amount') }}</span>
                      <span v-if="change.attribute === 'made_by'">{{ __('Paid by') }}</span>
                      <span v-if="change.attribute === 'notes'">{{ __('Notes') }}</span>
                    </Td>
                    <Td class="whitespace-nowrap">
                      <span v-if="change.attribute === 'paid_at'">{{ displayDate(change.old, 'MMM D, YYYY') }}</span>
                      <span v-else-if="change.attribute === 'amount'">{{ displayCurrency(change.old) }}</span>
                      <span v-else>{{ change.old }}</span>
                    </Td>
                    <Td class="whitespace-nowrap">
                      <span v-if="change.attribute === 'paid_at'">{{ displayDate(change.value, 'MMM, D, YYYY') }}</span>
                      <span v-else-if="change.attribute === 'amount'">{{ displayCurrency(change.value) }}</span>
                      <span v-else>{{ change.value }}</span>
                    </Td>
                  </tr>
                </template>
              </Tbody>
            </Table>
          </template>
        </DescriptionItem>
      </DescriptionList>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import DescriptionList from '@/components/tables/DescriptionList'
import DescriptionItem from '@/components/tables/DescriptionItem'
import Link from '@/components/Link'
import HelpText from '@/components/HelpText'
import ModalHeadline from '@/components/modals/ModalHeadline'
import displaysDate from '@/composition/displaysDate'
import Table from '@/components/tables/Table'
import Thead from '@/components/tables/Thead'
import Th from '@/components/tables/Th'
import Tbody from '@/components/tables/Tbody'
import Td from '@/components/tables/Td'
import displaysCurrency from '@/composition/displaysCurrency'

export default defineComponent({
  components: {
    Td,
    Tbody,
    Th,
    Thead,
    Table,
    ModalHeadline,
    DescriptionItem,
    DescriptionList,
    Link,
    HelpText,
  },
  props: {
    payment: Object,
  },

  setup ({ payment }) {
    const { displayDate } = displaysDate()
    const { displayCurrency } = displaysCurrency()
    const termNumber = payment.payment_term
      ? payment.schedule.terms.findIndex(t => t.uuid === payment.payment_term.uuid) + 1
      : 0

    return {
      termNumber,
      displayDate,
      displayCurrency,
    }
  }
})
</script>
