<template>
  <DescriptionList>
    <DescriptionItem>
      <template #dt>
        {{ __('Invoice') }}
      </template>
      <template #dd>
        <Link :href="`/invoices/${refund.invoice.uuid}`">
          {{ refund.invoice.invoice_number }}
        </Link>
      </template>
    </DescriptionItem>
    <DescriptionItem v-if="refund.invoice.student">
      <template #dt>
        {{ __('Student') }}
      </template>
      <template #dd>
        {{ refund.invoice.student.full_name }} ({{ refund.invoice.student.student_number }})
      </template>
    </DescriptionItem>
    <DescriptionItem v-if="refund.invoice.students.length > 0">
      <template #dt>
        {{ __('Students') }}
      </template>
      <template #dd>
        <ul>
          <li v-for="student in refund.invoice.students">
            {{ student.full_name }} ({{ student.student_number }})
          </li>
        </ul>
      </template>
    </DescriptionItem>
    <DescriptionItem>
      <template #dt>
        {{ __('Refund amount') }}
      </template>
      <template #dd>
        {{ refund.amount_formatted }}
      </template>
    </DescriptionItem>
    <DescriptionItem>
      <template #dt>
        {{ __('Refund date') }}
      </template>
      <template #dd>
        {{ refund.refunded_at_formatted || refund.created_at }}
      </template>
    </DescriptionItem>
    <DescriptionItem v-if="refund.transaction_details">
      <template #dt>
        {{ __('Transaction details') }}
      </template>
      <template #dd>
        {{ refund.transaction_details }}
      </template>
    </DescriptionItem>
    <DescriptionItem>
      <template #dt>
        {{ __('Recorded by') }}
      </template>
      <template #dd>
        {{ refund.user.full_name }}
      </template>
    </DescriptionItem>
    <DescriptionItem>
      <template #dt>
        {{ __('Recorded on') }}
      </template>
      <template #dd>
        {{ refund.created_at }}
      </template>
    </DescriptionItem>
    <DescriptionItem>
      <template #dt>
        <strong class="text-gray-900 dark:text-gray-100">{{ __('Remaining balance') }}</strong>
      </template>
      <template #dd>
        <strong>{{ refund.invoice.remaining_balance_formatted }}</strong>
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
    refund: Object,
  }
})
</script>
