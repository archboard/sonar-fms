<template>
  <Authenticated>
    <div>
      <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div class="px-4 py-5 bg-gradient-to-br from-primary-500 to-fuchsia-600 dark:from-primary-700 dark:to-fuchsia-600 shadow rounded-lg overflow-hidden sm:p-6">
          <dt class="text-sm font-medium text-primary-100 dark:text-gray-300 truncate flex items-center space-x-1">
            <span>{{ __('Account balance') }}</span>
          </dt>
          <dd class="mt-1 text-3xl font-semibold text-white">
            {{ student.account_balance_formatted }}
          </dd>
        </div>

        <div class="px-4 py-5 bg-gradient-to-br from-gray-500 to-gray-600 dark:from-gray-700 dark:to-gray-600 shadow rounded-lg overflow-hidden sm:p-6">
          <dt class="text-sm font-medium text-gray-100 dark:text-gray-300 truncate flex items-center space-x-1">
            <span>{{ __('Unpaid invoices') }}</span>
          </dt>
          <dd class="mt-1 text-3xl font-semibold text-white">
            {{ invoiceCount }}
          </dd>
        </div>
      </dl>
    </div>

    <!-- Invoices -->
    <section aria-labelledby="invoice-table" class="mt-8 xl:mt-10">
      <div>
        <div class="divide-y divide-gray-300 dark:divide-gray-600">
          <div class="pb-4 flex justify-between items-start">
            <h2 id="invoice-table" class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Invoices') }}</h2>
          </div>
          <div class="pt-6">
            <div class="max-w-none">
              <StudentInvoiceTable
                ref="studentTable"
                :student="student"
                :permissions="permissions"
              />
            </div>
          </div>
        </div>
        </div>
    </section>
  </Authenticated>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '@/layouts/Authenticated.vue'
import StudentInvoiceTable from '@/components/StudentInvoiceTable.vue'

export default defineComponent({
  components: {
    StudentInvoiceTable,
    Authenticated,
  },
  props: {
    student: Object,
    permissions: Object,
    invoiceCount: Number,
  },

  setup (props) {
  }
})
</script>
