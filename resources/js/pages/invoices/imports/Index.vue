<template>
  <Authenticated>
    <template v-slot:actions>
      <Button v-if="can('create')" component="inertia-link" :href="$route('invoices.imports.create')" size="sm">
        {{ __('New import') }}
      </Button>
    </template>

    <div class="mb-6 flex space-x-4">
      <div class="relative w-full">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
          <SearchIcon class="h-5 w-5 text-gray-500" />
        </div>
        <Input v-model="searchTerm" class="pl-12" type="search" :placeholder="__('Search by name')" />
      </div>
      <button @click.prevent="resetFilters" class="w-auto bg-white border border-gray-300 dark:border-gray-900 dark:focus:border-primary-500 dark:bg-gray-700 rounded-md px-4 shadow focus:outline-none transition hover:ring hover:ring-primary-500 hover:ring-opacity-50 focus:ring focus:ring-offset-primary-500 focus:ring-primary-500" :title="__('Reset filters')">
        <XCircleIcon class="w-6 h-6" />
      </button>
    </div>

    <Table>
      <Thead>
        <tr>
          <Th>
            <div class="flex items-center cursor-pointer" @click="sortColumn('file_name')">
              <span>
                {{ __('Name') }}
              </span>
              <span class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderBy === 'file_name' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderBy === 'file_name' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <Th>
            <div class="flex items-center cursor-pointer" @click="sortColumn('total_records')">
              <span>
                {{ __('Total records') }}
              </span>
              <span class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderBy === 'total_records' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderBy === 'total_records' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <Th>
            <div class="flex items-center cursor-pointer" @click="sortColumn('import_records')">
              <span>
                {{ __('Imported') }}
              </span>
              <span class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderBy === 'import_records' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderBy === 'import_records' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <Th>
            <div class="flex items-center cursor-pointer" @click="sortColumn('failed_records')">
              <span>
                {{ __('Failed') }}
              </span>
              <span class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderBy === 'failed_records' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderBy === 'failed_records' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <th></th>
        </tr>
      </Thead>
      <Tbody>
        <tr
          v-for="(invoiceImport) in imports.data"
          :key="invoiceImport.id"
        >
          <Td :lighter="false">
            <div class="flex items-center">
              <InertiaLink class="hover:underline" :href="$route('invoices.imports.show', invoiceImport)">{{ invoiceImport.file_name }}</InertiaLink>
              <SolidBadge v-if="!invoiceImport.mapping_valid" class="ml-2" color="red">{{ __('Fix mapping') }}</SolidBadge>
              <SolidBadge v-if="invoiceImport.imported_at" class="ml-2" color="green">{{ __('Imported') }}</SolidBadge>
              <SolidBadge v-if="invoiceImport.rolled_back_at" class="ml-2" color="yellow">{{ __('Rolled back') }}</SolidBadge>
            </div>
          </Td>
          <Td>{{ invoiceImport.total_records }}</Td>
          <Td>{{ invoiceImport.imported_records }}</Td>
          <Td>{{ invoiceImport.failed_records }}</Td>
          <Td class="text-right">
            <VerticalDotMenu>
              <InvoiceImportActions
                :invoice-import="invoiceImport"
                @import="importingImport = invoiceImport"
                @rollback="rollingBackImport = invoiceImport"
                @template="convertingImport = invoiceImport"
                show-view
              />
            </VerticalDotMenu>
          </Td>
        </tr>

        <tr v-if="imports.data.length === 0">
          <Td colspan="5" class="text-center">
            {{ __('No imports exist.') }} <Link :href="$route('invoices.imports.create')">{{ __('Add one') }}</Link>.
          </Td>
        </tr>
      </Tbody>
    </Table>

    <Pagination :meta="imports.meta" :links="imports.links" />
  </Authenticated>

  <ConfirmationModal
    v-if="rollingBackImport.id"
    @close="rollingBackImport = {}"
    @confirmed="rollBack(`/invoices/imports/${rollingBackImport.id}/reverse`)"
  />
  <ConfirmationModal
    v-if="importingImport.id"
    @close="importingImport = {}"
    @confirmed="importImport(`/invoices/imports/${importingImport.id}/start`)"
  >
    <template v-slot:content>
      {{ __('This will begin importing invoices.') }}
    </template>
  </ConfirmationModal>
  <ConvertImportMappingToTemplateModal
    v-if="convertingImport.id"
    @close="convertingImport = {}"
    :endpoint="`/invoices/imports/${convertingImport.id}/template`"
  />
</template>

<script>
import { defineComponent, inject, ref } from 'vue'
import handlesFilters from '@/composition/handlesFilters.js'
import searchesItems from '@/composition/searchesItems.js'
import Authenticated from '@/layouts/Authenticated.vue'
import Table from '@/components/tables/Table.vue'
import Thead from '@/components/tables/Thead.vue'
import Th from '@/components/tables/Th.vue'
import Tbody from '@/components/tables/Tbody.vue'
import Td from '@/components/tables/Td.vue'
import Checkbox from '@/components/forms/Checkbox.vue'
import Pagination from '@/components/tables/Pagination.vue'
import Input from '@/components/forms/Input.vue'
import { SearchIcon, SortAscendingIcon, SortDescendingIcon, AdjustmentsIcon, XCircleIcon } from '@heroicons/vue/outline'
import StudentTableFiltersModal from '@/components/modals/StudentTableFiltersModal.vue'
import Link from '@/components/Link.vue'
import Button from '@/components/Button.vue'
import SolidBadge from '@/components/SolidBadge.vue'
import VerticalDotMenu from '@/components/dropdown/VerticalDotMenu.vue'
import SonarMenuItem from '@/components/forms/SonarMenuItem.vue'
import ConfirmationModal from '@/components/modals/ConfirmationModal.vue'
import PageProps from '@/mixins/PageProps'
import checksPermissions from '@/composition/checksPermissions.js'
import rollsBackImport from '@/composition/rollsBackImport.js'
import importFileImport from '@/composition/importFileImport.js'
import ConvertImportMappingToTemplateModal from '@/components/modals/ConvertImportMappingToTemplateModal.vue'
import InvoiceImportActions from '@/components/InvoiceImportActions.vue'

export default defineComponent({
  mixins: [PageProps],
  components: {
    InvoiceImportActions,
    ConvertImportMappingToTemplateModal,
    ConfirmationModal,
    SonarMenuItem,
    VerticalDotMenu,
    SolidBadge,
    Button,
    XCircleIcon,
    StudentTableFiltersModal,
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
    imports: Object,
  },

  setup (props) {
    const $route = inject('$route')
    const { filters, applyFilters, resetFilters, sortColumn } = handlesFilters({
      s: '',
      perPage: 15,
      page: 1,
      orderBy: 'created_at',
      orderDir: 'desc',
      imported: true,
    }, $route('students.index'))
    const { searchTerm } = searchesItems(filters)
    const selectAll = ref(false)
    const showFilters = ref(false)
    const { can } = checksPermissions(props.permissions)
    const { rollBack, rollingBackImport } = rollsBackImport()
    const { importingImport, importImport } = importFileImport()
    const convertingImport = ref({})

    return {
      filters,
      applyFilters,
      resetFilters,
      sortColumn,
      searchTerm,
      selectAll,
      showFilters,
      rollingBackImport,
      rollBack,
      can,
      importingImport,
      importImport,
      convertingImport,
    }
  }
})
</script>
