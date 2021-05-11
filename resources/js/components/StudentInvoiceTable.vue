<template>
  <section>
    <Table>
      <Thead>
        <tr>
          <Th>{{ __('Title') }}</Th>
          <Th>{{ __('Status') }}</Th>
          <Th class="text-right">{{ __('Total') }}</Th>
          <Th class="text-right">{{ __('Remaining') }}</Th>
        </tr>
      </Thead>
      <Tbody>
        <tr
          v-for="invoice in invoices.data"
          :key="invoice.id"
        >
          <Td :lighter="false">
            {{ invoice.title }}
          </Td>
          <Td>
            <InvoiceStatusBadge :invoice="invoice" />
          </Td>
          <Td class="text-right">
            {{ displayCurrency(invoice.amount_due) }}
          </Td>
          <Td class="text-right">
            {{ displayCurrency(invoice.remaining_balance) }}
          </Td>
        </tr>
        <tr v-if="invoices.data.length === 0">
          <Td colspan="4" class="text-center">
            {{ __('No invoices exist for this student.') }}
          </Td>
        </tr>
      </Tbody>
    </Table>

    <div :class="{ 'py-6': invoices.data.length > 0 }">
      <Pagination
        :meta="invoices.meta"
        @paged="paged"
      />
    </div>
  </section>
</template>

<script>
import { defineComponent, inject, reactive, ref, watch } from 'vue'
import TableComponents from './tables'
import displaysCurrency from '../composition/displaysCurrency'
import Pagination from './tables/AjaxPagination'
import InvoiceStatusBadge from './InvoiceStatusBadge'

export default defineComponent({
  components: {
    InvoiceStatusBadge,
    ...TableComponents,
    Pagination,
  },
  props: {
    student: Object,
  },

  setup (props) {
    const $route = inject('$route')
    const $http = inject('$http')
    const filters = reactive({
      page: 1,
      random: 'hello',
    })
    const { displayCurrency } = displaysCurrency()
    const invoices = ref({
      data: [],
      meta: {},
      links: {}
    })
    const fetchInvoices = () => {
      const params = {
        ...filters,
        student: props.student,
      }
      const route = $route('students.invoices.index', params)

      $http.get(route).then(({ data }) => {
        invoices.value = data
      })
    }
    const paged = page => {
      filters.page = page
    }

    watch(filters, () => {
      fetchInvoices()
    })

    fetchInvoices()

    return {
      invoices,
      displayCurrency,
      filters,
      paged,
      fetchInvoices,
    }
  }
})
</script>
