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
    <DescriptionItem>
      <template #dt>
        {{ __('Amount') }}
      </template>
      <template #dd>
        {{ payment.amount_formatted }}
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

  setup () {
    document.documentElement.classList.remove('dark')
  }
})
</script>
