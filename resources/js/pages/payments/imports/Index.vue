<template>
  <Authenticated>
    <template v-slot:actions>
      <Button v-if="can('create')" component="inertia-link" href="/payments/imports/create">
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
          v-for="paymentImport in imports.data"
          :key="paymentImport.id"
        >
          <Td :lighter="false">
            <div class="flex items-center">
              <InertiaLink class="hover:underline" :href="`/payments/imports/${paymentImport.id}`">{{ paymentImport.file_name }}</InertiaLink>
              <SolidBadge v-if="!paymentImport.mapping_valid" class="ml-2" color="red">{{ __('Fix mapping') }}</SolidBadge>
              <SolidBadge v-if="paymentImport.imported_at" class="ml-2" color="green">{{ __('Imported') }}</SolidBadge>
              <SolidBadge v-if="paymentImport.rolled_back_at" class="ml-2" color="yellow">{{ __('Rolled back') }}</SolidBadge>
            </div>
          </Td>
          <Td>{{ paymentImport.total_records }}</Td>
          <Td>{{ paymentImport.imported_records }}</Td>
          <Td>{{ paymentImport.failed_records }}</Td>
          <Td class="text-right">
            <VerticalDotMenu>
              <div class="p-1">
                <SonarMenuItem v-if="can('viewAny')" is="inertia-link" :href="`/payments/imports/${paymentImport.id}`">
                  {{ __('View') }}
                </SonarMenuItem>
                <SonarMenuItem v-if="can('update')" is="inertia-link" :href="`/payments/imports/${paymentImport.id}/edit`">
                  {{ __('Edit import file') }}
                </SonarMenuItem>
                <SonarMenuItem v-if="can('create')" is="a" :href="`/payments/imports/${paymentImport.id}/download`">
                  {{ __('Download file') }}
                </SonarMenuItem>
                <SonarMenuItem v-if="can('update')" is="inertia-link" :href="`/payments/imports/${paymentImport.id}/map`">
                  {{ __('Update mapping') }}
                </SonarMenuItem>
              </div>
              <div class="p-1" v-if="paymentImport.imported_at || paymentImport.mapping_valid">
                <SonarMenuItem v-if="paymentImport.mapping_valid && !paymentImport.imported_at && can('create')" is="inertia-link" :href="`/payments/imports/${paymentImport.id}/preview`">
                  {{ __('Preview import') }}
                </SonarMenuItem>
                <SonarMenuItem v-if="paymentImport.mapping_valid && !paymentImport.imported_at && can('create')" @click.prevent="importingImport = paymentImport">
                  {{ __('Import') }}
                </SonarMenuItem>
                <SonarMenuItem v-if="paymentImport.imported_at && can('roll back')" @click.prevent="rollingBackImport = paymentImport">
                  {{ __('Roll back') }}
                </SonarMenuItem>
              </div>
            </VerticalDotMenu>
          </Td>
        </tr>

        <tr v-if="imports.data.length === 0">
          <Td colspan="5" class="text-center">
            {{ __('No imports exist.') }} <Link href="/payments/imports/create">{{ __('Add one') }}</Link>.
          </Td>
        </tr>
      </Tbody>
    </Table>

    <Pagination :meta="imports.meta" :links="imports.links" />
  </Authenticated>

  <ConfirmationModal
    v-if="rollingBackImport.id"
    @close="rollingBackImport = {}"
    @confirmed="rollBack(`/payments/imports/${rollingBackImport.id}/reverse`)"
  />
  <ConfirmationModal
    v-if="importingImport.id"
    @close="importingImport = {}"
    @confirmed="importImport(`/payments/imports/${importingImport.id}/start`)"
  >
    <template v-slot:content>
      {{ __('This will begin importing payments.') }}
    </template>
  </ConfirmationModal>
</template>

<script>
import { defineComponent, ref } from 'vue'
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
import StudentTableFiltersModal from '@/components/modals/StudentTableFiltersModal'
import Link from '@/components/Link'
import Button from '@/components/Button'
import SolidBadge from '@/components/SolidBadge'
import VerticalDotMenu from '@/components/dropdown/VerticalDotMenu'
import SonarMenuItem from '@/components/forms/SonarMenuItem'
import ConfirmationModal from '@/components/modals/ConfirmationModal'
import PageProps from '@/mixins/PageProps'
import checksPermissions from '@/composition/checksPermissions'
import rollsBackImport from '@/composition/rollsBackImport'
import importFileImport from '@/composition/importFileImport'

export default defineComponent({
  mixins: [PageProps],
  components: {
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
    const { filters, applyFilters, resetFilters, sortColumn } = handlesFilters({
      s: '',
      perPage: 15,
      page: 1,
      orderBy: 'created_at',
      orderDir: 'desc',
      imported: true,
    }, `/payments/imports`)
    const { searchTerm } = searchesItems(filters)
    const showFilters = ref(false)
    const { can } = checksPermissions(props.permissions)
    const { rollBack, rollingBackImport } = rollsBackImport()
    const { importingImport, importImport } = importFileImport()

    return {
      filters,
      applyFilters,
      resetFilters,
      sortColumn,
      searchTerm,
      showFilters,
      rollingBackImport,
      rollBack,
      can,
      importingImport,
      importImport,
    }
  }
})
</script>
