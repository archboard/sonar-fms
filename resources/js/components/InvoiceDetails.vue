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
  </div>
</template>

<script>
import { computed, defineComponent, ref } from 'vue'
import Td from './tables/Td'
import Tbody from './tables/Tbody'
import Table from './tables/Table'
import displaysCurrency from '../composition/displaysCurrency'
import { XIcon } from '@heroicons/vue/solid'

export default defineComponent({
  components: {
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
    const subTotal = computed(() => {
      return props.invoice.items.reduce((total, item) => {
        return total + (item.amount_per_unit * item.quantity)
      }, 0)
    })

    return {
      subTotal,
      displayCurrency,
    }
  }
})
</script>
