<template>
  <div>
    <div v-if="showStudent" class="flex items-center justify-between">
      <h3 class="font-bold text-lg mb-2 pt-2">{{ invoice.student.full_name }}</h3>
      <InertiaLink class="text-gray-500 dark:text-gray-400 hover:underline" :href="`/invoices/${invoice.uuid}`">{{ invoice.invoice_number }}</InertiaLink>
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
          <Td class="text-base font-bold text-right" :lighter="false">
            {{ invoice.remaining_balance_formatted }}
          </Td>
        </tr>
      </Tbody>
    </Table>

    <section v-if="paymentSchedules.length > 0" class="mt-6">
      <div class="divide-y divide-gray-300 dark:divide-gray-600">
        <div class="pb-4">
          <h2 id="activity-title" class="text-lg font-medium">{{ __('Payment schedules') }}</h2>
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

              <div class="flex items-start space-x-4">
                <CardWrapper v-for="(term, termIndex) in schedule.terms" :key="term.uuid" class="flex-0">
                  <CardPadding>
                    <h4 class="font-medium">{{ __('Payment :number', { number: termIndex + 1 }) }}</h4>
                    <span v-if="term.due_at">{{ __(':amount due by :date', { amount: displayCurrency(term.amount), date: displayDate(term.due_at, 'abbr_date') }) }}</span>
                    <span v-else>{{ displayCurrency(term.amount) }}</span>
                  </CardPadding>
                </CardWrapper>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { computed, defineComponent } from 'vue'
import Td from '@/components/tables/Td'
import Tbody from '@/components/tables/Tbody'
import Table from '@/components/tables/Table'
import displaysCurrency from '@/composition/displaysCurrency'
import displaysDate from '@/composition/displaysDate'
import { XIcon } from '@heroicons/vue/solid'
import Alert from '@/components/Alert'
import CardWrapper from '@/components/CardWrapper'
import CardPadding from '@/components/CardPadding'
import CardHeader from '@/components/CardHeader'

export default defineComponent({
  components: {
    CardHeader,
    CardPadding,
    CardWrapper,
    Alert,
    Table,
    Tbody,
    Td,
    XIcon,
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
    }
  }
})
</script>
