<template>
  <Authenticated>
    <template v-slot:actions>
      <Dropdown
        size="sm"
        :menu-items="[
          {
            label: __('By hand'),
            route: `/invoices/create`,
          },
          {
            label: __('From import'),
            route: `/invoices/imports/create`,
          },
        ]"
      >
        {{ __('New invoice') }}
      </Dropdown>
    </template>

    <div class="mb-2 flex space-x-4">
      <div class="relative w-full">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
          <SearchIcon class="h-5 w-5 text-gray-500" />
        </div>
        <Input v-model="searchTerm" class="pl-12" type="search" :placeholder="__('Search for invoice by title, number or student')" />
      </div>
      <FilterButton @click.prevent="showFilters = true" />
      <ClearFilterButton @click.prevent="resetFilters" />
    </div>

    <div class="space-x-2 pt-1 flex flex-wrap">
      <InvoiceDismissibleBadges :filters="filters" />
    </div>

    <FadeIn>
      <div v-if="user.invoice_selection.length > 0" class="text-gray-500 dark:text-gray-300 mt-4 -mb-2 flex text-sm">
        <span v-if="user.invoice_selection.length === 1">
          {{ __(':count invoice selected', { count: user.invoice_selection.length }) }}
        </span>
        <span v-else>
          {{ __(':count invoices selected', { count: user.invoice_selection.length }) }}
        </span>
        <div class="space-x-3 ml-3">
          <Link is="button" @click.prevent="selectAll = false">
            {{ __('Remove selection') }}
          </Link>
          <Link href="/combine">
            {{ __('Combine') }}
          </Link>
        </div>
      </div>
    </FadeIn>

    <Table class="mt-6">
      <Thead>
        <tr>
          <th class="w-8 text-left pl-6">
            <Checkbox v-model:checked="selectAll" />
          </th>
          <Th class="w-1">
            <div class="flex items-center cursor-pointer" @click="sortColumn('id')">
              <span>
                #
              </span>
              <span class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderBy === 'id' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderBy === 'id' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <Th>
            <div class="flex items-center cursor-pointer" @click="sortColumn('title')">
              <span>
                {{ __('Title') }}
              </span>
              <span class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderBy === 'title' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderBy === 'title' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <Th>
            <div class="flex items-center cursor-pointer" @click="sortColumn('student')">
              <span>
                {{ __('Student') }}
              </span>
              <span class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderBy === 'student' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderBy === 'student' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
<!--          <Th>-->
<!--            <div class="flex items-center justify-end text-right cursor-pointer" @click="sortColumn('amount_due')">-->
<!--              <span>-->
<!--                {{ __('Total due') }}-->
<!--              </span>-->
<!--              <span v-if="filters.orderBy === 'amount_due'" class="relative h-4 w-4 ml-2">-->
<!--                <SortAscendingIcon v-if="filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />-->
<!--                <SortDescendingIcon v-if="filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />-->
<!--              </span>-->
<!--            </div>-->
<!--          </Th>-->
          <Th>
            <div class="flex items-center justify-end text-right cursor-pointer" @click="sortColumn('remaining_balance')">
              <span>
                {{ __('Remaining balance') }}
              </span>
              <span v-if="filters.orderBy === 'remaining_balance'" class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <th></th>
        </tr>
      </Thead>
      <Tbody>
        <InvoiceTableRow
          v-for="invoice in invoices.data"
          :key="invoice.id"
          :invoice="invoice"
          @edit-status="editInvoice(invoice)"
          @convert-to-template="useAsTemplate(invoice)"
        >
          <template #prepend>
            <td class="pl-6 py-4 text-sm">
              <Checkbox
                v-model:checked="user.invoice_selection"
                @change="selectInvoice(invoice)"
                :value="invoice.uuid"
              />
            </td>
          </template>
        </InvoiceTableRow>

        <tr v-if="invoices.data.length === 0">
          <Td colspan="6" class="text-center">
            {{ __('No results.') }}
          </Td>
        </tr>
      </Tbody>
    </Table>

    <Pagination :meta="invoices.meta" :links="invoices.links" />

  </Authenticated>

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
  <InvoiceTableFilterModal
    v-if="showFilters"
    @close="showFilters = false"
    @apply="applyFilters"
    :filters="filters"
  />
</template>

<script>
import { defineComponent, inject, nextTick, ref, watch } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import handlesFilters from '@/composition/handlesFilters'
import searchesItems from '@/composition/searchesItems'
import Authenticated from '@/layouts/Authenticated'
import Table from '@/components/tables/Table'
import Thead from '@/components/tables/Thead'
import Th from '@/components/tables/Th'
import Tbody from '@/components/tables/Tbody'
import Td from '@/components/tables/Td'
import Checkbox from '@/components/forms/Checkbox'
import Pagination from '@/components/tables/Pagination'
import Input from '@/components/forms/Input'
import { SearchIcon, SortAscendingIcon, SortDescendingIcon, XCircleIcon } from '@heroicons/vue/outline'
import Link from '@/components/Link'
import HelpText from '@/components/HelpText'
import Button from '@/components/Button'
import FeeFormModal from '@/components/modals/FeeFormModal'
import displaysCurrency from '@/composition/displaysCurrency'
import InvoiceStatusBadge from '@/components/InvoiceStatusBadge'
import Dropdown from '@/components/forms/Dropdown'
import PageProps from '@/mixins/PageProps'
import checksPermissions from '@/composition/checksPermissions'
import VerticalDotMenu from '@/components/dropdown/VerticalDotMenu'
import SonarMenuItem from '@/components/forms/SonarMenuItem'
import InvoiceActionItems from '@/components/dropdown/InvoiceActionItems'
import InvoiceStatusModal from '@/components/modals/InvoiceStatusModal'
import ConvertInvoiceModal from '@/components/modals/ConvertInvoiceModal'
import InvoiceTableRow from '@/components/tables/InvoiceTableRow'
import FadeIn from '@/components/transitions/FadeIn'
import InvoiceTableFilterModal from '@/components/modals/InvoiceTableFilterModal'
import DismissibleBadge from '@/components/DismissibleBadge'
import InvoiceDismissibleBadges from '@/components/InvoiceDismissibleBadges'
import FilterButton from '@/components/FilterButton'
import ClearFilterButton from '@/components/ClearFilterButton'

export default defineComponent({
  mixins: [PageProps],
  components: {
    ClearFilterButton,
    FilterButton,
    InvoiceDismissibleBadges,
    DismissibleBadge,
    InvoiceTableFilterModal,
    FadeIn,
    InvoiceTableRow,
    ConvertInvoiceModal,
    InvoiceStatusModal,
    InvoiceActionItems,
    SonarMenuItem,
    VerticalDotMenu,
    Dropdown,
    InvoiceStatusBadge,
    FeeFormModal,
    Button,
    HelpText,
    XCircleIcon,
    SearchIcon,
    Input,
    SortDescendingIcon,
    SortAscendingIcon,
    Pagination,
    Checkbox,
    Td,
    Tbody,
    Th,
    Thead,
    Table,
    Authenticated,
    Link,
  },

  props: {
    invoices: Object,
    user: Object,
    school: Object,
  },

  setup (props) {
    const $http = inject('$http')
    const showFilters = ref(false)
    const selectedInvoice = ref({})
    const selectAll = ref(props.user.invoice_selection.length > 0)
    const { can } = checksPermissions()
    const { filters, applyFilters, resetFilters, sortColumn } = handlesFilters({
      s: '',
      perPage: 25,
      page: 1,
      orderBy: '',
      orderDir: '',
      status: [],
      grades: [],
      date_start: null,
      date_end: null,
      due_start: null,
      due_end: null,
      types: [],
    }, `/invoices`)
    const { searchTerm } = searchesItems(filters)
    const { displayCurrency } = displaysCurrency()

    // Selection
    const selectInvoice = invoice => {
      nextTick(() => {
        const add = props.user.invoice_selection.includes(invoice.uuid)
        const method = add ? 'put' : 'delete'

        $http[method](`/invoice-selection/${invoice.uuid}`)
      })
    }
    const clearSelection = async () => {
      await $http.delete('/invoice-selection')
      props.user.invoice_selection = []
    }
    watch(selectAll, (newVal) => {
      if (newVal) {
        Inertia.post(`/invoice-selection`, filters, {
          preserveState: true
        })
      } else {
        clearSelection()
      }
    })

    const editInvoice = (invoice = {}) => {
      selectedInvoice.value = invoice
    }

    const convertInvoice = ref({})
    const useAsTemplate = invoice => {
      convertInvoice.value = invoice
    }

    return {
      filters,
      sortColumn,
      showFilters,
      applyFilters,
      resetFilters,
      searchTerm,
      selectedInvoice,
      displayCurrency,
      editInvoice,
      can,
      convertInvoice,
      useAsTemplate,
      selectAll,
      selectInvoice,
      clearSelection,
    }
  }
})
</script>
