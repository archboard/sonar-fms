<template>
  <section>
    <Loader v-if="loading" />
    <div v-show="!loading">
      <Table>
        <Thead>
          <tr>
            <Th>#</Th>
            <Th>{{ __('Title') }}</Th>
            <Th class="text-right">{{ __('Remaining balance') }}</Th>
            <Th/>
          </tr>
        </Thead>
        <Tbody>
          <InvoiceTableRow
            v-for="invoice in invoices.data"
            :key="invoice.id"
            :invoice="invoice"
            :show-student="false"
          />
          <tr v-if="invoices.data.length === 0">
            <Td colspan="5" class="text-center">
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
    </div>
  </section>
</template>

<script>
import { defineComponent, inject, reactive, ref, watch } from 'vue'
import TableComponents from './tables'
import displaysCurrency from '../composition/displaysCurrency'
import Pagination from './tables/AjaxPagination'
import InvoiceStatusBadge from './InvoiceStatusBadge'
import VerticalDotMenu from './dropdown/VerticalDotMenu'
import SonarMenuItem from './forms/SonarMenuItem'
import checksPermissions from '../composition/checksPermissions'
import Link from './Link'
import Loader from './Loader'
import InvoiceTableRow from '@/components/tables/InvoiceTableRow'

export default defineComponent({
  components: {
    InvoiceTableRow,
    Loader,
    VerticalDotMenu,
    InvoiceStatusBadge,
    ...TableComponents,
    Pagination,
    SonarMenuItem,
    Link,
  },
  emit: ['edit'],
  props: {
    student: Object,
    permissions: Object,
  },

  setup (props) {
    const $route = inject('$route')
    const $http = inject('$http')
    const loading = ref(true)
    const { can, canAny } = checksPermissions()
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
      loading.value = true
      const params = {
        ...filters,
        student: props.student,
      }
      const route = $route('students.invoices.index', params)

      $http.get(route).then(({ data }) => {
        invoices.value = data
        loading.value = false
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
      can,
      canAny,
      invoices,
      displayCurrency,
      filters,
      paged,
      fetchInvoices,
      loading,
    }
  }
})
</script>
