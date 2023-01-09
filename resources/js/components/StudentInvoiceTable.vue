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
            @editStatus="selectedInvoice = invoice"
            @convertToTemplate="convertInvoice = invoice"
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

    <InvoiceStatusModal
      v-if="can('invoices.update') && selectedInvoice.uuid"
      @close="selectedInvoice = {}"
      :invoice="selectedInvoice"
    />
    <ConvertInvoiceModal
      v-if="convertInvoice.uuid"
      @close="convertInvoice = {}"
      :endpoint="`/invoices/${convertInvoice.uuid}/convert`"
    />
  </section>
</template>

<script>
import { defineComponent, inject, reactive, ref, watch } from 'vue'
import TableComponents from '@/components/tables'
import displaysCurrency from '@/composition/displaysCurrency.js'
import Pagination from '@/components/tables/AjaxPagination.vue'
import InvoiceStatusBadge from '@/components/InvoiceStatusBadge.vue'
import VerticalDotMenu from '@/components/dropdown/VerticalDotMenu.vue'
import SonarMenuItem from '@/components/forms/SonarMenuItem.vue'
import checksPermissions from '@/composition/checksPermissions.js'
import Link from '@/components/Link.vue'
import Loader from '@/components/Loader.vue'
import InvoiceTableRow from '@/components/tables/InvoiceTableRow.vue'
import qs from 'qs'
import InvoiceStatusModal from '@/components/modals/InvoiceStatusModal.vue'
import ConvertInvoiceModal from '@/components/modals/ConvertInvoiceModal.vue'

export default defineComponent({
  components: {
    ConvertInvoiceModal,
    InvoiceStatusModal,
    InvoiceTableRow,
    Loader,
    VerticalDotMenu,
    InvoiceStatusBadge,
    ...TableComponents,
    Pagination,
    SonarMenuItem,
    Link,
  },
  props: {
    student: Object,
    permissions: Object,
  },

  setup (props) {
    const $http = inject('$http')
    const loading = ref(true)
    const selectedInvoice = ref({})
    const convertInvoice = ref({})
    const { can, canAny } = checksPermissions()
    const filters = reactive({
      page: 1,
    })
    const { displayCurrency } = displaysCurrency()
    const invoices = ref({
      data: [],
      meta: {},
      links: {}
    })
    const fetchInvoices = async () => {
      loading.value = true

      try {
        const { data } = await $http.get(`/students/${props.student.uuid}/invoices?${qs.stringify(filters)}`)
        invoices.value = data
      } catch (e) {
        //
      }

      loading.value = false
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
      selectedInvoice,
      convertInvoice,
    }
  }
})
</script>
