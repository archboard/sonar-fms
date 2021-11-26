<template>
  <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-1 lg:grid-cols-2">
    <li v-for="invoice in invoices" :key="invoice.uuid" class="col-span-1 bg-white dark:bg-gray-900 rounded-lg shadow divide-y divide-gray-200 dark:divide-gray-500">
      <div class="w-full flex items-center justify-between px-6 py-5 space-x-6">
        <div class="flex-1 truncate">
          <div class="flex justify-between">
            <div>
              <span class="flex-shrink-0 block text-gray-500 dark:text-gray-400 text-xs font-medium">{{ invoice.invoice_number }}</span>
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
        <div class="-mt-px">
          <div class="flex">
            <InertiaLink :href="`/payments/create?invoice_uuid=${invoice.uuid}`" class="relative -mr-px w-0 flex-1 inline-flex items-center justify-center py-4 text-sm text-gray-700 dark:text-gray-300 font-medium border border-transparent rounded-bl-lg hover:text-gray-500 dark:hover:dark:text-gray-400">
              <CashIcon class="w-5 h-5 text-gray-400 dark:text-gray-300" aria-hidden="true" />
              <span class="ml-3">{{ __('Record payment') }}</span>
            </InertiaLink>
          </div>
        </div>
      </div>
    </li>
  </ul>
</template>

<script>
import { defineComponent } from 'vue'
import { CashIcon } from '@heroicons/vue/solid'
import displaysCurrency from '@/composition/displaysCurrency'

export default defineComponent({
  components: {
    CashIcon,
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
