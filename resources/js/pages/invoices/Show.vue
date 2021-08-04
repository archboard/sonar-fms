<template>
  <Authenticated>
    <template #inTitle>
      <InvoiceStatusBadge class="ml-3" :invoice="invoice" size="lg" />
    </template>

    <template #afterTitle>
      <HelpText>{{ __('Invoice #:invoice_number', { invoice_number: invoice.id }) }}</HelpText>
    </template>

    <template #actions>
      <Dropdown size="sm">
        {{ __('Actions') }}

        <template #dropdown>
          <InvoiceActionItems
            :invoice="invoice"
            @edit-status="editStatus = true"
            @convert-to-template="convert = true"
          />
        </template>
      </Dropdown>
    </template>

    <Alert v-if="invoice.is_void" level="error" class="mb-4">
      {{ __('This invoice is void.') }}
    </Alert>

    <!-- Details for smaller screens -->
    <div class="xl:hidden grid grid-cols-4 gap-5 pb-6 mb-8 border-b border-gray-300 dark:border-gray-600">
      <div>
        <SidebarHeader>
          {{ __('Student') }}
        </SidebarHeader>
        <div class="mt-2 leading-8">
          <Link :href="$route('students.show', student)">
            {{ student.full_name }} <span v-if="student.student_number">({{ student.student_number }})</span>
          </Link>
          <HelpText class="mt-0">{{ student.grade_level_formatted }}</HelpText>
        </div>
      </div>
      <div>
        <SidebarHeader>
          {{ __('Created') }}
        </SidebarHeader>
        <div class="mt-2 leading-8">
          {{ displayDate(invoice.created_at, 'MMMM D, YYYY H:mm') }}
        </div>
      </div>
      <div v-if="invoice.is_void">
        <SidebarHeader>
          {{ __('Voided') }}
        </SidebarHeader>
        <div class="mt-2 leading-8">
          {{ displayDate(invoice.voided_at, 'MMMM D, YYYY H:mm') }}
        </div>
      </div>
      <div v-if="invoice.available_at">
        <SidebarHeader>
          {{ __('Available') }}
        </SidebarHeader>
        <div class="mt-2 leading-8">
          {{ displayDate(invoice.available_at, 'MMMM D, YYYY H:mm') }}
        </div>
      </div>
      <div v-if="invoice.due_at">
        <SidebarHeader>
          {{ __('Due') }}
        </SidebarHeader>
        <div class="mt-2 leading-8">
          {{ displayDate(invoice.due_at, 'MMMM D, YYYY H:mm') }}
        </div>
      </div>
    </div>

    <div class="xl:grid xl:grid-cols-3">
      <div class="xl:col-span-2 xl:pr-8 xl:border-r xl:border-gray-300 xl:dark:border-gray-600">
        <div>
          <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="px-4 py-5 bg-gradient-to-br from-primary-500 to-primary-600 dark:from-primary-700 dark:to-primary-600 shadow rounded-lg overflow-hidden sm:p-6">
              <dt class="text-sm font-medium text-primary-100 dark:text-gray-300 truncate">
                {{ __('Invoice total') }}
              </dt>
              <dd class="mt-1 text-3xl font-semibold text-white">
                {{ invoice.amount_due_formatted }}
              </dd>
            </div>

            <div class="px-4 py-5 bg-gradient-to-br from-gray-500 to-gray-600 dark:from-gray-700 dark:to-gray-600 shadow rounded-lg overflow-hidden sm:p-6">
              <dt class="text-sm font-medium text-gray-100 dark:text-gray-300 truncate">
                {{ __('Remaining balance') }}
              </dt>
              <dd class="mt-1 text-3xl font-semibold text-white">
                {{ invoice.remaining_balance_formatted }}
              </dd>
            </div>
          </dl>

          <div class="py-5">
            <InvoiceDetails :invoice="invoice" />
          </div>
        </div>
      </div>

      <!-- Sidebar for desktop -->
      <div class="hidden xl:block pl-8 space-y-6 divide-y divide-gray-300 dark:divide-gray-600">
        <div>
          <SidebarHeader>
            {{ __('Student') }}
          </SidebarHeader>
          <div class="mt-2 leading-8">
            <Link :href="$route('students.show', student)">
              {{ student.full_name }} <span v-if="student.student_number">({{ student.student_number }})</span>
            </Link>
            <HelpText class="mt-0">{{ student.grade_level_formatted }}</HelpText>
          </div>
        </div>
        <div class="pt-6">
          <SidebarHeader>
            {{ __('Created') }}
          </SidebarHeader>
          <div class="mt-2 leading-8">
            {{ displayDate(invoice.created_at, 'MMMM D, YYYY H:mm') }}
          </div>
        </div>
        <div v-if="invoice.is_void" class="pt-6">
          <SidebarHeader>
            {{ __('Voided') }}
          </SidebarHeader>
          <div class="mt-2 leading-8">
            {{ displayDate(invoice.voided_at, 'MMMM D, YYYY H:mm') }}
          </div>
        </div>
        <div v-if="invoice.available_at" class="pt-6">
          <SidebarHeader>
            {{ __('Available') }}
          </SidebarHeader>
          <div class="mt-2 leading-8">
            {{ displayDate(invoice.available_at, 'MMMM D, YYYY H:mm') }}
          </div>
        </div>
        <div v-if="invoice.due_at" class="pt-6">
          <SidebarHeader>
            {{ __('Due') }}
          </SidebarHeader>
          <div class="mt-2 leading-8">
            {{ displayDate(invoice.due_at, 'MMMM D, YYYY H:mm') }}
          </div>
        </div>
      </div>
    </div>
  </Authenticated>

  <InvoiceStatusModal
    v-if="can('invoices.update') && editStatus"
    @close="editStatus = false"
    :invoice="invoice"
  />
  <ConvertInvoiceModal
    v-if="convert"
    @close="convert = false"
    :invoice="invoice"
    :endpoint="$route('invoices.convert', invoice)"
  />
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '@/layouts/Authenticated'
import displaysCurrency from '@/composition/displaysCurrency'
import InvoiceStatusBadge from '@/components/InvoiceStatusBadge'
import InvoiceDetails from '@/components/InvoiceDetails'
import Button from '@/components/Button'
import checksPermissions from '@/composition/checksPermissions'
import SidebarHeader from '@/components/SidebarHeader'
import displaysDate from '@/composition/displaysDate'
import HelpText from '@/components/HelpText'
import Link from '@/components/Link'
import PageProps from '@/mixins/PageProps'
import Dropdown from '@/components/forms/Dropdown'
import SonarMenuItem from '@/components/forms/SonarMenuItem'
import InvoiceStatusModal from '@/components/modals/InvoiceStatusModal'
import ConvertInvoiceModal from '@/components/modals/ConvertInvoiceModal'
import InvoiceActionItems from '@/components/dropdown/InvoiceActionItems'
import Alert from '@/components/Alert'

export default defineComponent({
  mixins: [PageProps],
  components: {
    Alert,
    InvoiceActionItems,
    ConvertInvoiceModal,
    InvoiceStatusModal,
    Dropdown,
    HelpText,
    SidebarHeader,
    Button,
    InvoiceDetails,
    InvoiceStatusBadge,
    Authenticated,
    Link,
    SonarMenuItem,
  },
  props: {
    user: Object,
    student: Object,
    invoice: Object,
    school: Object,
    permissions: Object,
  },

  setup (props) {
    const { displayCurrency } = displaysCurrency()
    const { displayDate } = displaysDate()
    const { can, canAny } = checksPermissions(props.permissions)
    const editStatus = ref(false)
    const convert = ref(false)

    return {
      displayCurrency,
      can,
      canAny,
      editStatus,
      displayDate,
      convert,
    }
  }
})
</script>
