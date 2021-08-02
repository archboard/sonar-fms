<template>
  <Authenticated>
    <template v-slot:actions>
      <Dropdown
        size="sm"
        :menu-items="[
          {
            label: __('By hand'),
            route: '#',
          },
          {
            label: __('From import'),
            route: $route('invoices.imports.index'),
          },
        ]"
      >
        {{ __('New invoice') }}
      </Dropdown>
    </template>

    <div class="mb-6 flex space-x-4">
      <div class="relative w-full">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
          <SearchIcon class="h-5 w-5 text-gray-500" />
        </div>
        <Input v-model="searchTerm" class="pl-12" type="search" :placeholder="__('Search by number or title')" />
      </div>
<!--      <button @click.prevent="showFilters = true" class="w-auto bg-white border border-gray-300 dark:border-gray-900 dark:focus:border-primary-500 dark:bg-gray-700 rounded-md px-4 shadow focus:outline-none transition hover:ring hover:ring-primary-500 hover:ring-opacity-50 focus:ring focus:ring-offset-primary-500 focus:ring-primary-500" :title="__('Filters')">-->
<!--        <AdjustmentsIcon class="w-6 h-6" />-->
<!--      </button>-->
      <button @click.prevent="resetFilters" class="w-auto bg-white border border-gray-300 dark:border-gray-900 dark:focus:border-primary-500 dark:bg-gray-700 rounded-md px-4 shadow focus:outline-none transition hover:ring hover:ring-primary-500 hover:ring-opacity-50 focus:ring focus:ring-offset-primary-500 focus:ring-primary-500" :title="__('Reset filters')">
        <XCircleIcon class="w-6 h-6" />
      </button>
    </div>

    <Table>
      <Thead>
        <tr>
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
            <div class="flex items-center justify-end cursor-pointer" @click="sortColumn('amount_due')">
              <span>
                {{ __('Total due') }}
              </span>
              <span v-if="filters.orderBy === 'amount_due'" class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <Th>
            <div class="flex items-center justify-end cursor-pointer" @click="sortColumn('remaining_balance')">
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
        <tr
          v-for="invoice in invoices.data"
          :key="invoice.id"
        >
          <Td class="pr-0">
            <div class="flex items-center">
              <span class="mr-2">{{ invoice.id }}</span>
              <InvoiceStatusBadge :invoice="invoice" size="sm" />
            </div>
          </Td>
          <Td :lighter="false">
            <InertiaLink :href="$route('invoices.show', invoice)" class="hover:underline">
              {{ invoice.title }}
            </InertiaLink>
          </Td>
          <Td class="text-right">{{ invoice.amount_due_formatted }}</Td>
          <Td class="text-right">{{ invoice.remaining_balance_formatted }}</Td>
          <Td class="text-right space-x-2">
            <VerticalDotMenu>
              <InvoiceActionItems
                :invoice="invoice"
                :show-view="true"
                @edit-status="editInvoice = invoice"
                @convert-to-template="convertInvoice = invoice"
              />
            </VerticalDotMenu>
          </Td>
        </tr>
      </Tbody>
    </Table>

    <Pagination :meta="invoices.meta" :links="invoices.links" />

  </Authenticated>

  <InvoiceStatusModal
    v-if="can('invoices.update') && editInvoice.id"
    @close="editInvoice = {}"
    :invoice="editInvoice"
  />
  <ConvertInvoiceModal
    v-if="convertInvoice.id"
    @close="convertInvoice = {}"
    :invoice="convertInvoice"
    :endpoint="$route('invoices.convert', convertInvoice)"
  />
</template>

<script>
import { defineComponent, inject, ref } from 'vue'
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
import { SearchIcon, SortAscendingIcon, SortDescendingIcon, AdjustmentsIcon, XCircleIcon } from '@heroicons/vue/outline'
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

export default defineComponent({
  mixins: [PageProps],
  components: {
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
    AdjustmentsIcon,
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
    const $route = inject('$route')
    const showFilters = ref(false)
    const editing = ref(false)
    const selectedInvoice = ref({})
    const { can } = checksPermissions()
    const { filters, applyFilters, resetFilters, sortColumn } = handlesFilters({
      s: '',
      perPage: 25,
      page: 1,
      orderBy: 'title',
      orderDir: 'asc',
    }, $route('invoices.index'))
    const { searchTerm } = searchesItems(filters)
    const { displayCurrency } = displaysCurrency()

    const editInvoice = (invoice = {}) => {
      selectedInvoice.value = invoice
      editing.value = true
    }

    const statusInvoice = ref({})
    const convertInvoice = ref({})

    return {
      filters,
      sortColumn,
      showFilters,
      applyFilters,
      resetFilters,
      searchTerm,
      selectedInvoice,
      editing,
      displayCurrency,
      editInvoice,
      can,
      statusInvoice,
      convertInvoice,
    }
  }
})
</script>
