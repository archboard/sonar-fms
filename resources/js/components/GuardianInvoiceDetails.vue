<template>
  <div>
    <div v-if="showStudent" class="flex items-center justify-between">
      <h3 class="font-bold text-lg mb-2 pt-2"><InertiaLink :href="`/students/${invoice.student.uuid}`" class="hover:underline">{{ invoice.student.full_name }}</InertiaLink></h3>
      <div class="flex items-center space-x-2">
        <InvoiceStatusBadge :invoice="invoice" />
        <InertiaLink class="text-gray-500 dark:text-gray-400 hover:underline" :href="`/invoices/${invoice.uuid}`">{{ invoice.invoice_number }}</InertiaLink>
      </div>
    </div>
    <Table>
      <Tbody>
        <tr
          v-for="item in invoice.items"
          :key="item.id"
        >
          <Td :lighter="false">
            {{ item.name }} <span v-if="item.quantity > 1"><XIcon class="w-4 h-4 inline-flex text-gray-500 dark:text-gray-400 mx-1"/> {{ item.quantity }}</span>
          </Td>
          <Td class="text-right">
            {{ item.amount_formatted }}
          </Td>
        </tr>
        <tr v-if="invoice.scholarships.length > 0">
          <Td :lighter="false">
            {{ __('Subtotal') }}
          </Td>
          <Td :lighter="false" class="text-right">
            {{ displayCurrency(subTotal) }}
          </Td>
        </tr>
        <tr v-if="invoice.scholarships.length > 0">
          <Td colspan="2" :lighter="false">
            <strong>
              {{ __('Scholarships') }}
            </strong>
          </Td>
        </tr>
        <tr
          v-for="scholarship in invoice.scholarships"
          :key="scholarship.id"
        >
          <Td :lighter="false">
            {{ scholarship.name }} <span v-if="scholarship.percentage">({{ scholarship.percentage_formatted }})</span>
          </Td>
          <Td class="text-right">
            {{ scholarship.calculated_amount_formatted }}
          </Td>
        </tr>
        <tr v-if="invoice.school.collect_tax && invoice.apply_tax">
          <Td :lighter="false">
            {{ invoice.tax_label }} ({{ invoice.tax_rate_formatted }})
          </Td>
          <Td class="text-right">
            {{ invoice.tax_due_formatted }}
          </Td>
        </tr>
        <tr>
          <Td class="text-base font-bold" :lighter="false">
            {{ __('Total due') }}
          </Td>
          <Td class="text-base text-right" :lighter="false">
            <div class="flex justify-end items-center space-x-2">
              <InvoiceStatusBadge v-if="showStudent && invoice.is_void" :invoice="invoice" />
              <span class="font-bold">{{ invoice.amount_due_formatted }}</span>
            </div>
          </Td>
        </tr>
      </Tbody>
    </Table>

    <Payments v-if="!showStudent" class="mt-8 xl:mt-10 py-5" :invoice="invoice" />

    <Refunds v-if="!showStudent" class="mt-8 xl:mt-10 py-5" :invoice="invoice" />

    <section v-if="paymentSchedules.length > 0" class="mt-8 xl:mt-10 py-5">
      <div class="divide-y divide-gray-300 dark:divide-gray-600">
        <div class="pb-4">
          <h2 class="text-lg font-medium">{{ __('Payment schedules') }}</h2>
        </div>
        <div class="pt-6">
          <Alert v-if="invoice.parent" class="mb-4">
            {{ __("This is the combined invoice's payment schedule.") }}
          </Alert>

          <div class="space-y-6">
            <div v-for="schedule in paymentSchedules" :key="schedule.uuid">
              <h3 class="font-medium mb-2">
                {{ __(':number payments (:total_price)', { number: schedule.terms.length, total_price: displayCurrency(schedule.amount) }) }}
              </h3>

              <Table>
                <Thead>
                  <tr>
                    <Th>{{ __('Payment') }}</Th>
                    <Th>{{ __('Due date') }}</Th>
                    <Th class="text-right">{{ __('Amount due') }}</Th>
                    <Th class="text-right">{{ __('Remaining balance') }}</Th>
                    <Th v-if="can('payments.create')"></Th>
                  </tr>
                </Thead>
                <Tbody>
                  <tr v-for="(term, termIndex) in schedule.terms" :key="term.uuid">
                    <Td>{{ termIndex + 1}}/{{ schedule.terms.length }}</Td>
                    <Td>{{ term.due_at ? displayDate(term.due_at, 'abbr_date') : __('N/A') }}</Td>
                    <Td class="text-right">{{ displayCurrency(term.amount_due) }}</Td>
                    <Td :lighter="false" class="text-right">{{ displayCurrency(term.remaining_balance) }}</Td>
                    <Td v-if="can('payments.create')" class="text-right"><Link :href="`/payments/create?invoice_uuid=${invoice.uuid}&term=${term.uuid}`">{{ __('Add payment') }}</Link></Td>
                  </tr>
                </Tbody>
              </Table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { computed, defineComponent } from 'vue'
import Td from '@/components/tables/Td.vue'
import Tbody from '@/components/tables/Tbody.vue'
import Table from '@/components/tables/Table.vue'
import displaysCurrency from '@/composition/displaysCurrency.js'
import displaysDate from '@/composition/displaysDate.js'
import checksPermissions from '@/composition/checksPermissions.js'
import { XIcon } from '@heroicons/vue/solid'
import Alert from '@/components/Alert.vue'
import CardWrapper from '@/components/CardWrapper.vue'
import CardPadding from '@/components/CardPadding.vue'
import CardHeader from '@/components/CardHeader.vue'
import Thead from '@/components/tables/Thead.vue'
import Th from '@/components/tables/Th.vue'
import Link from '@/components/Link.vue'
import InvoiceStatusBadge from '@/components/InvoiceStatusBadge.vue'
import Payments from '@/components/invoices/Payments.vue'
import Refunds from '@/components/invoices/Refunds.vue'

export default defineComponent({
  components: {
    Refunds,
    Payments,
    InvoiceStatusBadge,
    Th,
    Thead,
    CardHeader,
    CardPadding,
    CardWrapper,
    Alert,
    Table,
    Tbody,
    Td,
    XIcon,
    Link,
  },

  props: {
    invoice: Object,
    showStudent: {
      type: Boolean,
      default: false
    }
  },

  setup (props) {
    const { displayCurrency } = displaysCurrency()
    const { displayDate } = displaysDate()
    const { can } = checksPermissions()
    const subTotal = computed(() => {
      return props.invoice.items.reduce((total, item) => {
        return total + (item.amount_per_unit * item.quantity)
      }, 0)
    })
    const paymentSchedules = computed(() => {
      return props.invoice.parent
        ? props.invoice.parent.payment_schedules
        : (props.invoice.payment_schedules || [])
    })

    return {
      subTotal,
      displayCurrency,
      displayDate,
      paymentSchedules,
      can,
    }
  }
})
</script>
