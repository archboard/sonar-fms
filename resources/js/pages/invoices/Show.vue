<template>
  <Authenticated>
    <template #afterTitle>
      <HelpText>{{ __('Invoice #:invoice_number', { invoice_number: invoice.id }) }}</HelpText>
    </template>

    <template #actions>
      <Button @click.prevent="editing = true">
        {{ __('Edit') }}
      </Button>
    </template>

    <!-- Sidebar for desktop -->
    <div class="xl:hidden grid grid-cols-4 gap-5 pb-6 mb-8 border-b border-gray-300 dark:border-gray-600">
      <div>
        <SidebarHeader>
          {{ __('Status') }}
        </SidebarHeader>
        <ul class="mt-2 leading-8 space-x-1">
          <li class="inline">
            <InvoiceStatusBadge :invoice="invoice" size="lg" />
          </li>
        </ul>
      </div>
      <div>
        <SidebarHeader>
          {{ __('Created') }}
        </SidebarHeader>
        <div class="mt-2 leading-8">
          {{ displayDate(invoice.created_at, 'MMMM D, YYYY H:mm') }}
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
            {{ __('Status') }}
          </SidebarHeader>
          <ul class="mt-2 leading-8 space-x-1">
            <li class="inline">
              <InvoiceStatusBadge :invoice="invoice" size="lg" />
            </li>
          </ul>
        </div>
        <div class="pt-6">
          <SidebarHeader>
            {{ __('Created') }}
          </SidebarHeader>
          <div class="mt-2 leading-8">
            {{ displayDate(invoice.created_at, 'MMMM D, YYYY H:mm') }}
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

    <StudentInvoiceSlideout
      v-if="editing"
      :invoice="invoice"
      :student="student"
      @close="editing = false"
    />
  </Authenticated>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '../../layouts/Authenticated'
import displaysCurrency from '../../composition/displaysCurrency'
import InvoiceStatusBadge from '../../components/InvoiceStatusBadge'
import InvoiceDetails from '../../components/InvoiceDetails'
import Button from '../../components/Button'
import checksPermissions from '../../composition/checksPermissions'
import StudentInvoiceSlideout from '../../components/slideouts/StudentInvoiceSlideout'
import SidebarHeader from '../../components/SidebarHeader'
import displaysDate from '../../composition/displaysDate'
import HelpText from '../../components/HelpText'

export default defineComponent({
  components: {
    HelpText,
    SidebarHeader, StudentInvoiceSlideout, Button, InvoiceDetails, InvoiceStatusBadge, Authenticated},
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
    const editing = ref(false)

    return {
      displayCurrency,
      can,
      canAny,
      editing,
      displayDate,
    }
  }
})
</script>
