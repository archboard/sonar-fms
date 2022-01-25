<template>
  <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2">
    <li v-for="invoice in invoices" :key="invoice.uuid" class="col-span-1 bg-gray-100 dark:bg-gray-900 rounded-lg shadow divide-y divide-gray-200 dark:divide-gray-500">
      <div class="w-full flex items-center justify-between px-6 py-5 space-x-6">
        <div class="flex-1 truncate">
          <div class="flex justify-between">
            <div>
              <span class="flex-shrink-0 flex space-x-1.5 items-center text-gray-500 dark:text-gray-400 text-xs font-medium">
                <span>{{ invoice.invoice_number }}</span>
                <InvoiceStatusBadge v-if="invoice.voided_at" :invoice="invoice" />
              </span>
              <h3 class="text-gray-900 dark:text-gray-100 text-sm font-medium truncate">{{ invoice.title }}</h3>
            </div>
            <div class="text-right">
              <div class="text-gray-500 dark:text-gray-400 text-xs">{{ __('Remaining balance') }}</div>
              <div class="text-gray-900 dark:text-gray-100 text-sm font-medium">{{ displayCurrency(invoice.remaining_balance) }}</div>
            </div>
          </div>
          <p class="mt-1 text-gray-500 dark:text-gray-400 text-sm truncate">{{ invoice.student.full_name }}</p>
        </div>
      </div>
      <div>
        <div class="-mt-px flex divide-x divide-gray-200 dark:divide-gray-500">
          <div class="w-0 flex-1 flex">
            <InertiaLink :href="`/payments/create?invoice_uuid=${invoice.uuid}`" class="relative -mr-px w-0 flex-1 inline-flex items-center justify-center py-4 text-sm text-gray-700 dark:text-gray-300 font-medium border border-transparent rounded-bl-lg hover:text-gray-500 dark:hover:dark:text-gray-400">
              <CashIcon class="w-5 h-5 text-gray-400 dark:text-gray-300" aria-hidden="true" />
              <span class="ml-3">{{ __('Record payment') }}</span>
            </InertiaLink>
          </div>
          <div class="-ml-px w-0 flex-1 flex">
            <a :href="`/invoices/${invoice.uuid}`" target="_blank" class="relative -mr-px w-0 flex-1 inline-flex items-center justify-center py-4 text-sm text-gray-700 dark:text-gray-300 font-medium border border-transparent rounded-bl-lg hover:text-gray-500 dark:hover:dark:text-gray-400">
              <ExternalLinkIcon class="w-5 h-5 text-gray-400" aria-hidden="true" />
              <span class="ml-3">{{ __('View invoice') }}</span>
            </a>
          </div>
        </div>
      </div>
    </li>
  </ul>
</template>

<script>
import { defineComponent } from 'vue'
import { CashIcon, ExternalLinkIcon } from '@heroicons/vue/solid'
import displaysCurrency from '@/composition/displaysCurrency'
import InvoiceStatusBadge from '@/components/InvoiceStatusBadge'

export default defineComponent({
  components: {
    InvoiceStatusBadge,
    CashIcon,
    ExternalLinkIcon,
  },
  props: {
    invoices: Array,
  },

  setup () {
    const { displayCurrency } = displaysCurrency()

    return {
      displayCurrency,
    }
  },
})
</script>
