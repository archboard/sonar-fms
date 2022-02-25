<template>
  <Authenticated>
    <template #inTitle>
    </template>

    <template #afterTitle>
      <InvoiceStatusBadge :invoice="invoice" size="lg" />
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

    <Alert v-if="invoice.parent_uuid" level="warning" class="mb-4">
      {{ __('This invoice is part of a combined invoice.') }} <InertiaLink :href="`/invoices/${invoice.parent_uuid}`" class="font-medium hover:underline">{{ __('View invoice.') }}</InertiaLink>
    </Alert>

    <!-- Details for smaller screens -->
    <div class="xl:hidden grid grid-cols-4 gap-5 pb-6 mb-8 border-b border-gray-300 dark:border-gray-600">
      <div v-if="invoice.student">
        <SidebarHeader>
          {{ __('Student') }}
        </SidebarHeader>
        <div class="mt-2 leading-8">
          <Link :href="`/students/${invoice.student.uuid}`">
            {{ invoice.student.full_name }} <span v-if="invoice.student.student_number">({{ invoice.student.student_number }})</span>
          </Link>
          <HelpText class="mt-0 leading-0">{{ invoice.student.grade_level_formatted }}</HelpText>
        </div>
      </div>
      <div>
        <SidebarHeader>
          {{ __('Created') }}
        </SidebarHeader>
        <div class="mt-2 leading-8">
          {{ displayDate(invoice.created_at, 'abbr') }}
        </div>
      </div>
      <div v-if="invoice.is_void">
        <SidebarHeader>
          {{ __('Voided') }}
        </SidebarHeader>
        <div class="mt-2 leading-8">
          {{ displayDate(invoice.voided_at, 'abbr') }}
        </div>
      </div>
      <div v-if="invoice.available_at">
        <SidebarHeader>
          {{ __('Available') }}
        </SidebarHeader>
        <div class="mt-2 leading-8">
          {{ displayDate(invoice.available_at, 'abbr') }}
        </div>
      </div>
      <div v-if="invoice.due_at">
        <SidebarHeader>
          {{ __('Due') }}
        </SidebarHeader>
        <div class="mt-2 leading-8">
          {{ displayDate(invoice.due_at, 'abbr') }}
        </div>
      </div>
    </div>

    <div class="xl:grid xl:grid-cols-3">
      <div class="xl:col-span-2 xl:pr-8 xl:border-r xl:border-gray-300 xl:dark:border-gray-600">
        <div>
          <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="px-4 py-5 bg-gradient-to-br from-primary-500 to-primary-600 dark:from-primary-700 dark:to-primary-600 shadow rounded-lg overflow-hidden sm:p-6">
              <dt class="text-sm font-medium text-primary-100 dark:text-gray-300 truncate">
                {{ __('Invoice total') }}{{ invoice.payment_schedule ? '*' : '' }}
              </dt>
              <dd class="mt-1 text-3xl font-semibold text-white">
                {{ displayCurrency(invoice.payment_schedule ? invoice.payment_schedule.amount : invoice.amount_due) }}
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
          <HelpText v-if="invoice.payment_schedule" class="mt-3">*{{ __('This invoice total is based on the payment schedule used when making payments. The original invoice total is :total.', { total: invoice.amount_due_formatted }) }}</HelpText>

          <div class="py-5 space-y-4">
            <InvoiceDetails
              v-for="child in invoice.children"
              :key="child.uuid"
              :invoice="child"
              :show-student="true"
            />
            <InvoiceDetails :invoice="invoice" />
          </div>
        </div>

        <ActivityFeed :activities="invoice.activities" />
      </div>

      <!-- Sidebar for desktop -->
      <div class="hidden xl:block pl-8 space-y-6 divide-y divide-gray-300 dark:divide-gray-600">
        <div v-if="invoice.student">
          <SidebarHeader>
            {{ __('Student') }}
          </SidebarHeader>
          <div class="mt-2 leading-8">
            <Link :href="`/students/${invoice.student.uuid}`">
              {{ invoice.student.full_name }} <span v-if="invoice.student.student_number">({{ invoice.student.student_number }})</span>
            </Link>
            <HelpText class="mt-0">{{ invoice.student.grade_level_formatted }}</HelpText>
          </div>
        </div>
        <div class="pt-6">
          <SidebarHeader>
            {{ __('Created') }}
          </SidebarHeader>
          <div class="mt-2 leading-8">
            {{ displayDate(invoice.created_at, 'abbr') }}
          </div>
        </div>
        <div v-if="invoice.is_void" class="pt-6">
          <SidebarHeader>
            {{ __('Voided') }}
          </SidebarHeader>
          <div class="mt-2 leading-8">
            {{ displayDate(invoice.voided_at, 'abbr') }}
          </div>
        </div>
        <div v-if="invoice.available_at" class="pt-6">
          <SidebarHeader>
            {{ __('Available') }}
          </SidebarHeader>
          <div class="mt-2 leading-8">
            {{ displayDate(invoice.available_at, 'abbr') }}
          </div>
        </div>
        <div v-if="invoice.due_at" class="pt-6">
          <SidebarHeader>
            {{ __('Due') }}
          </SidebarHeader>
          <div class="mt-2 leading-8">
            {{ displayDate(invoice.due_at, 'abbr') }}
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
    :endpoint="`/invoices/${invoice.uuid}/convert`"
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
import { BellIcon } from '@heroicons/vue/solid'
import ActivityFeed from '@/components/ActivityFeed'

export default defineComponent({
  mixins: [PageProps],
  components: {
    ActivityFeed,
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
    BellIcon,
  },
  props: {
    user: Object,
    student: {
      type: Object,
      default: () => ({})
    },
    invoice: Object,
    school: Object,
    permissions: Object,
  },

  setup (props) {
    const { displayCurrency } = displaysCurrency()
    const { displayDate, fromNow } = displaysDate()
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
      fromNow,
    }
  }
})
</script>
