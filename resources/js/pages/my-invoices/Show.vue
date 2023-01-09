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
          <GuardianInvoiceActions
            :invoice="invoice"
          />
        </template>
      </Dropdown>
    </template>

    <Alert v-if="invoice.is_void" level="error" class="mb-4">
      {{ __('This invoice is void.') }}
    </Alert>

    <Alert v-if="invoice.parent_uuid" level="warning" class="mb-4">
      {{ __('This invoice is part of a combined invoice.') }} <InertiaLink v-if="can('invoices.parent')" :href="`/invoices/${invoice.parent_uuid}`" class="font-medium hover:underline">{{ __('View invoice.') }}</InertiaLink>
    </Alert>

    <!-- Details for smaller screens -->
    <div class="xl:hidden grid grid-cols-4 gap-5 pb-6 mb-8 border-b border-gray-300 dark:border-gray-600">
      <div v-if="invoice.student">
        <SidebarHeader>
          {{ __('Student') }}
        </SidebarHeader>
        <div class="mt-2 leading-8">
          <component :is="can('students.view') ? 'Link' : 'span'" :href="`/my-students/${invoice.student.uuid}`">
            {{ invoice.student.full_name }} <span v-if="invoice.student.student_number">({{ invoice.student.student_number }})</span>
          </component>
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
            <GuardianInvoiceDetails
              v-for="child in invoice.children"
              :key="child.uuid"
              :invoice="child"
              show-student
            />
            <GuardianInvoiceDetails :invoice="invoice" />
          </div>
        </div>
      </div>

      <!-- Sidebar for desktop -->
      <div class="hidden xl:block pl-8 space-y-6 divide-y divide-gray-300 dark:divide-gray-600">
        <div v-if="invoice.student">
          <SidebarHeader>
            {{ __('Student') }}
          </SidebarHeader>
          <div class="mt-2 leading-8">
            <component :is="can('students.view') ? 'Link' : 'span'" :href="`/students/${invoice.student.uuid}`">
              {{ invoice.student.full_name }} <span v-if="invoice.student.student_number">({{ invoice.student.student_number }})</span>
            </component>
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
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '@/layouts/Authenticated.vue'
import displaysCurrency from '@/composition/displaysCurrency.js'
import InvoiceStatusBadge from '@/components/InvoiceStatusBadge.vue'
import InvoiceDetails from '@/components/InvoiceDetails.vue'
import Button from '@/components/Button.vue'
import checksPermissions from '@/composition/checksPermissions.js'
import SidebarHeader from '@/components/SidebarHeader.vue'
import displaysDate from '@/composition/displaysDate.js'
import HelpText from '@/components/HelpText.vue'
import Link from '@/components/Link.vue'
import PageProps from '@/mixins/PageProps'
import Dropdown from '@/components/forms/Dropdown.vue'
import SonarMenuItem from '@/components/forms/SonarMenuItem.vue'
import InvoiceStatusModal from '@/components/modals/InvoiceStatusModal.vue'
import ConvertInvoiceModal from '@/components/modals/ConvertInvoiceModal.vue'
import Alert from '@/components/Alert.vue'
import GuardianInvoiceDetails from '@/components/GuardianInvoiceDetails.vue'
import GuardianInvoiceActions from '@/components/GuardianInvoiceActions.vue'

export default defineComponent({
  mixins: [PageProps],
  components: {
    GuardianInvoiceActions,
    GuardianInvoiceDetails,
    Alert,
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
    student: {
      type: Object,
      default: () => ({})
    },
    invoice: Object,
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
