<template>
  <Authenticated>
    <template v-slot:actions>
      <Dropdown
        v-if="can('invoices.create')"
        size="sm"
        :menu-items="[
          {
            label: __('Create manually'),
            route: `/invoices/create`,
          },
          {
            label: __('Import'),
            route: `/invoices/imports/create`,
          },
        ]"
      >
        {{ __('New invoice') }}
      </Dropdown>
    </template>

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
          <Link v-if="!selectionPublished && can('invoices.update')" href="/invoice-selection/publish" method="put" as="button">
            {{ __('Publish') }}
          </Link>
        </div>
      </div>
    </FadeIn>

    <Table class="mt-6">
      <Thead>
        <tr>
          <th v-if="canSelect" class="w-8 text-left pl-6">
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
            <td v-if="canSelect" class="pl-6 py-4 text-sm">
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
  <ExportPromptModal
    v-if="promptExport"
    @close="promptExport = false"
    url="/export/invoices"
    :filters="filters"
  />
</template>

<script>
import { defineComponent, inject, nextTick, ref, watch } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import handlesFilters from '@/composition/handlesFilters.js'
import searchesItems from '@/composition/searchesItems.js'
import Authenticated from '@/layouts/Authenticated.vue'
import tables from '@/components/tables'
import Checkbox from '@/components/forms/Checkbox.vue'
import Input from '@/components/forms/Input.vue'
import { SearchIcon, SortAscendingIcon, SortDescendingIcon } from '@heroicons/vue/outline'
import Link from '@/components/Link.vue'
import HelpText from '@/components/HelpText.vue'
import Button from '@/components/Button.vue'
import displaysCurrency from '@/composition/displaysCurrency.js'
import Dropdown from '@/components/forms/Dropdown.vue'
import PageProps from '@/mixins/PageProps'
import checksPermissions from '@/composition/checksPermissions.js'
import InvoiceActionItems from '@/components/dropdown/InvoiceActionItems.vue'
import InvoiceStatusModal from '@/components/modals/InvoiceStatusModal.vue'
import ConvertInvoiceModal from '@/components/modals/ConvertInvoiceModal.vue'
import InvoiceTableRow from '@/components/tables/InvoiceTableRow.vue'
import FadeIn from '@/components/transitions/FadeIn.vue'
import InvoiceTableFilterModal from '@/components/modals/InvoiceTableFilterModal.vue'
import DismissibleBadge from '@/components/DismissibleBadge.vue'
import InvoiceDismissibleBadges from '@/components/InvoiceDismissibleBadges.vue'
import FilterButton from '@/components/FilterButton.vue'
import ClearFilterButton from '@/components/ClearFilterButton.vue'
import ExportPromptModal from '@/components/modals/ExportPromptModal.vue'
import ExportButton from '@/components/ExportButton.vue'

export default defineComponent({
  mixins: [PageProps],
  components: {
    ExportButton,
    ExportPromptModal,
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
    Dropdown,
    Button,
    HelpText,
    SearchIcon,
    Input,
    SortDescendingIcon,
    SortAscendingIcon,
    Checkbox,
    ...tables,
    Authenticated,
    Link,
  },

  props: {
    invoices: Object,
    user: Object,
    school: Object,
    endpoint: {
      type: String,
      default: '/invoices',
    },
    canSelect: {
      type: Boolean,
      default: () => true,
    }
  },

  setup (props) {
    const $http = inject('$http')
    const showFilters = ref(false)
    const promptExport = ref(false)
    const selectionPublished = ref(true)
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
    }, props.endpoint)
    const { searchTerm } = searchesItems(filters)
    const { displayCurrency } = displaysCurrency()

    // Selection
    const selectInvoice = invoice => {
      nextTick(async () => {
        const add = props.user.invoice_selection.includes(invoice.uuid)
        const method = add ? 'put' : 'delete'

        await $http[method](`/invoice-selection/${invoice.uuid}`)
        checkPublicationStatus()
      })
    }
    const clearSelection = async () => {
      await $http.delete('/invoice-selection')
      props.user.invoice_selection = []
    }
    const checkPublicationStatus = async () => {
      const { data } = await $http.get('/invoice-selection/published')
      selectionPublished.value = data.published
    }
    watch(selectAll, (newVal) => {
      if (newVal) {
        Inertia.post(`/invoice-selection`, filters, {
          preserveState: true,
          onSuccess () {
            checkPublicationStatus()
          }
        })
      } else {
        clearSelection()
        selectionPublished.value = true
      }
    })

    const editInvoice = (invoice = {}) => {
      selectedInvoice.value = invoice
    }

    const convertInvoice = ref({})
    const useAsTemplate = invoice => {
      convertInvoice.value = invoice
    }

    checkPublicationStatus()

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
      promptExport,
      selectionPublished,
    }
  }
})
</script>
