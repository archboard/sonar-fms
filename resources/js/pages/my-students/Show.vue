<template>
  <Authenticated>
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
import Authenticated from '@/layouts/Authenticated'
import StudentInvoiceTable from '@/components/StudentInvoiceTable'

export default defineComponent({
  components: {
    StudentInvoiceTable,
    Authenticated,
  },
  props: {
    student: Object,
    permissions: Object,
  },

  setup (props) {
  }
})
</script>
