<template>
  <div class="mb-6">
    <CardSectionHeader>
      {{ __('Summary') }}
    </CardSectionHeader>
    <HelpText class="text-sm mt-1">
      {{ __('Below is the summary of the invoice, including all relevant details that will be displayed when viewing the invoice.') }}
    </HelpText>
  </div>

  <dl class="sm:divide-y sm:divide-gray-200 dark:sm:divide-gray-500">
    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
      <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
        {{ __('Title') }}
      </dt>
      <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
        {{ invoice.title }}
      </dd>
    </div>
    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
      <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
        {{ __('Description') }}
      </dt>
      <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
        {{ invoice.description }}
      </dd>
    </div>
    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
      <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
        {{ __('Availability date') }}
      </dt>
      <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
        <span v-if="invoice.available_at" class="flex items-end">
          <span>{{ displayDate(invoice.available_at, 'MMMM D, YYYY H:mm') }}</span>
          <span class="inline-flex ml-3">
            <button class="text-gray-500 dark:text-gray-300 hover:underline focus:outline-none" type="button" @click.prevent="invoice.available_at = null">
              {{ __('Remove') }}
            </button>
          </span>
        </span>
        <span v-else>
          {{ __('No due date.') }}
        </span>
      </dd>
    </div>
    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
      <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
        {{ __('Due date') }}
      </dt>
      <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
        <span v-if="invoice.due_at" class="flex items-end">
          <span>{{ displayDate(invoice.due_at, 'MMMM D, YYYY H:mm') }}</span>
          <span class="inline-flex ml-3">
            <button class="text-gray-500 dark:text-gray-300 hover:underline focus:outline-none" type="button" @click.prevent="invoice.due_at = null">
              {{ __('Remove') }}
            </button>
          </span>
        </span>
        <span v-else>
          {{ __('No due date.') }}
        </span>
      </dd>
    </div>
    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
      <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
        {{ __('Notification') }}
      </dt>
      <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
        <span v-if="invoice.notify">
          {{ __('Contacts will be notified in 15 minutes, unless cancelled.') }}
        </span>
        <span v-else>
          {{ __('Manually notify.') }}
        </span>
      </dd>
    </div>
    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
      <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
        {{ __('Line items') }}
      </dt>
      <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
        <div
          v-for="item in invoice.items"
          :key="item.id"
          class="flex justify-between py-1"
        >
          <div>
            {{ __(':name x :quantity', { ...item }) }}
          </div>
          <div>
            {{ displayCurrency(item.amount_per_unit * item.quantity) }}
          </div>
        </div>
        <div
          class="flex justify-between font-bold"
          :class="{
            'mt-2 pt-2 border-t border-gray-200 dark:border-gray-400': invoice.items.length > 0
          }"
        >
          <div>{{ __('Subtotal' )}}</div>
          <div>{{ displayCurrency(subtotal) }}</div>
        </div>
      </dd>
    </div>
    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
      <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
        {{ __('Scholarships') }}
      </dt>
      <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
        <div
          v-for="item in invoice.scholarships"
          :key="item.id"
          class="flex justify-between py-1"
        >
          <div>
            {{ item.name }}
          </div>
          <div>
            {{ displayCurrency(getItemDiscount(item)) }}
          </div>
        </div>
        <div
          class="font-bold flex justify-between"
          :class="{
            'mt-2 pt-2 border-t border-gray-200 dark:border-gray-400': invoice.scholarships.length > 0
          }"
        >
          <div>{{ __('Scholarship subtotal' )}}</div>
          <div>{{ displayCurrency(scholarshipSubtotal) }}</div>
        </div>
      </dd>
    </div>
    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
      <dt class="text-sm font-medium">
        <strong>{{ __('Invoice total') }}</strong>
      </dt>
      <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2 text-right">
        <strong>{{ totalDue }}</strong>
      </dd>
    </div>
  </dl>
</template>

<script>
import { computed } from 'vue'
import CardHeader from './CardHeader'
import HelpText from './HelpText'
import Label from './forms/Label'
import displaysCurrency from '../composition/displaysCurrency'
import Button from './Button'
import CardSectionHeader from './CardSectionHeader'
import displaysDate from '../composition/displaysDate'
import invoiceItemForm from '../composition/invoiceItemForm'
import invoiceScholarshipForm from '../composition/invoiceScholarshipForm'
import invoicePaymentScheduleForm from '../composition/invoicePaymentScheduleForm'
import CardWrapper from './CardWrapper'
import CardPadding from './CardPadding'

export default {
  components: {
    CardPadding,
    CardWrapper,
    CardSectionHeader,
    Button,
    HelpText,
    CardHeader,
    Label,
  },
  props: {
    invoice: {
      type: Object,
      required: true,
    }
  },
  emits: ['close'],

  setup (props) {
    const school = computed(() => page.props.value.school)

    const { timezone, displayDate } = displaysDate()
    const { displayCurrency } = displaysCurrency()
    const total = computed(() => {
      let total = subtotal.value - scholarshipSubtotal.value

      if (total < 0) {
        total = 0
      }

      return total
    })
    const totalDue = computed(() => displayCurrency(total.value))

    // Invoice line items
    const {
      subtotal,
    } = invoiceItemForm(props.invoice)

    // Scholarships
    const {
      scholarshipSubtotal,
      getItemDiscount,
    } = invoiceScholarshipForm(props.invoice)

    // Payment schedules
    const { } = invoicePaymentScheduleForm(props.invoice, total)

    return {
      school,
      subtotal,
      displayCurrency,
      displayDate,
      timezone,
      scholarshipSubtotal,
      total,
      totalDue,
      getItemDiscount,
    }
  },
}
</script>
