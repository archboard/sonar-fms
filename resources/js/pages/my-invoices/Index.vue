<template>
  <Authenticated>
    <div class="mb-2 flex flex-wrap lg:space-x-4">
      <div class="relative flex-1 w-full mb-4 lg:mb-0 lg:w-auto">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
          <SearchIcon class="h-5 w-5 text-gray-500" />
        </div>
        <Input v-model="searchTerm" class="pl-12" type="search" :placeholder="__('Search for invoice by title, number or student')" />
      </div>
      <div class="w-full lg:w-auto space-x-2 lg:space-x-4 flex">
        <FilterButton @click.prevent="showFilters = true" />
        <ClearFilterButton @click.prevent="resetFilters" />
        <ExportButton @click.prevent="promptExport = true" />
      </div>
    </div>

    <div class="space-x-2 pt-1 flex flex-wrap">
      <InvoiceDismissibleBadges :filters="filters" />
    </div>

    <Table class="mt-6">
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
        <tr
          v-for="invoice in invoices.data"
          :key="invoice.id"
        >
          <Td>
            <span class="whitespace-nowrap flex items-center space-x-2">
              <Copy :copy-value="invoice.invoice_number">{{ invoice.invoice_number }}</Copy>
              <CollectionIcon v-if="invoice.is_parent" class="w-4 h-4" />
              <TableLink v-if="invoice.parent" :href="`/my-invoices/${invoice.parent_uuid}`">{{ invoice.parent.invoice_number }}</TableLink>
            </span>
          </Td>
          <Td :lighter="false">
            <div class="flex items-center space-x-1.5">
              <TableLink :href="`/my-invoices/${invoice.uuid}`" class="whitespace-nowrap">
                {{ invoice.title }}
              </TableLink>
              <InvoiceStatusBadge :invoice="invoice" size="sm" />
            </div>
          </Td>
          <Td :lighter="false" class="truncate">
            <InertiaLink v-if="invoice.student" :href="`/my-students/${invoice.student_uuid}`" class="hover:underline">
              {{ invoice.student.full_name }}
            </InertiaLink>

            <template
              v-for="(student, index) in invoice.students"
              :key="student.uuid"
            >
              <TableLink :href="`/my-students/${student.uuid}`" class="whitespace-nowrap">
                {{ student.full_name }}
              </TableLink><span v-if="index !== invoice.students.length - 1">, </span>
            </template>
          </Td>
          <Td class="text-right">{{ invoice.remaining_balance_formatted }}</Td>
          <Td class="text-right space-x-2 pl-0">
            <VerticalDotMenu>
              <GuardianInvoiceActions />
            </VerticalDotMenu>
          </Td>
        </tr>

        <tr v-if="invoices.data.length === 0">
          <Td colspan="5" class="text-center">
            {{ __('No results.') }}
          </Td>
        </tr>
      </Tbody>
    </Table>

    <Pagination :meta="invoices.meta" :links="invoices.links" />

  </Authenticated>

  <InvoiceTableFilterModal
    v-if="showFilters"
    @close="showFilters = false"
    @apply="applyFilters"
    :filters="filters"
    :blocked-statuses="['published', 'draft']"
  />
  <ExportPromptModal
    v-if="promptExport"
    @close="promptExport = false"
    url="/export/invoices"
    :filters="filters"
  />
</template>

<script>
import { defineComponent, ref } from 'vue'
import handlesFilters from '@/composition/handlesFilters.js'
import searchesItems from '@/composition/searchesItems.js'
import Authenticated from '@/layouts/Authenticated.vue'
import tables from '@/components/tables'
import Input from '@/components/forms/Input.vue'
import { SearchIcon, SortAscendingIcon, SortDescendingIcon, CollectionIcon } from '@heroicons/vue/outline'
import Link from '@/components/Link.vue'
import HelpText from '@/components/HelpText.vue'
import Button from '@/components/Button.vue'
import FeeFormModal from '@/components/modals/FeeFormModal.vue'
import InvoiceStatusBadge from '@/components/InvoiceStatusBadge.vue'
import Dropdown from '@/components/forms/Dropdown.vue'
import PageProps from '@/mixins/PageProps'
import VerticalDotMenu from '@/components/dropdown/VerticalDotMenu.vue'
import InvoiceTableFilterModal from '@/components/modals/InvoiceTableFilterModal.vue'
import DismissibleBadge from '@/components/DismissibleBadge.vue'
import InvoiceDismissibleBadges from '@/components/InvoiceDismissibleBadges.vue'
import FilterButton from '@/components/FilterButton.vue'
import ClearFilterButton from '@/components/ClearFilterButton.vue'
import ExportPromptModal from '@/components/modals/ExportPromptModal.vue'
import ExportButton from '@/components/ExportButton.vue'
import Copy from '@/components/Copy.vue'
import TableLink from '@/components/tables/TableLink.vue'
import SonarMenuItem from '@/components/forms/SonarMenuItem.vue'
import copiesToClipboard from '@/composition/copiesToClipboard.js'
import GuardianInvoiceActions from '@/components/GuardianInvoiceActions.vue'

export default defineComponent({
  mixins: [PageProps],
  components: {
    GuardianInvoiceActions,
    SonarMenuItem,
    TableLink,
    Copy,
    ExportButton,
    ExportPromptModal,
    ClearFilterButton,
    FilterButton,
    InvoiceDismissibleBadges,
    DismissibleBadge,
    InvoiceTableFilterModal,
    VerticalDotMenu,
    Dropdown,
    InvoiceStatusBadge,
    FeeFormModal,
    Button,
    HelpText,
    Input,
    SearchIcon,
    SortDescendingIcon,
    SortAscendingIcon,
    CollectionIcon,
    ...tables,
    Authenticated,
    Link,
  },

  props: {
    invoices: Object,
    endpoint: {
      type: String,
      default: '/invoices',
    },
  },

  setup (props) {
    const showFilters = ref(false)
    const promptExport = ref(false)
    const { copy } = copiesToClipboard()
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
    }, props.endpoint)
    const { searchTerm } = searchesItems(filters)

    return {
      filters,
      sortColumn,
      showFilters,
      applyFilters,
      resetFilters,
      searchTerm,
      promptExport,
      copy,
    }
  }
})
</script>
