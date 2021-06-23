<template>
  <Authenticated>
    <template v-slot:actions>
      <Button component="inertia-link" :href="$route('invoices.imports.create')" size="sm">
        {{ __('Import') }}
      </Button>
    </template>

    <div class="mb-6 flex space-x-4">
      <div class="relative w-full">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
          <SearchIcon class="h-5 w-5 text-gray-500" />
        </div>
        <Input v-model="searchTerm" class="pl-12" type="search" :placeholder="__('Search by name, email or student number')" />
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
          <th class="w-8 text-left pl-6">
            <Checkbox v-model:checked="selectAll" />
          </th>
          <Th>
            <div class="flex items-center cursor-pointer" @click="sortColumn('last_name')">
              <span>
                {{ __('Name') }}
              </span>
              <span class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderBy === 'last_name' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderBy === 'last_name' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <Th>
            <div class="flex items-center cursor-pointer" @click="sortColumn('student_number')">
              <span>
                {{ __('Student Number') }}
              </span>
              <span class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderBy === 'student_number' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderBy === 'student_number' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <Th>
            <div class="flex items-center cursor-pointer" @click="sortColumn('grade_level')">
              <span>
                {{ __('Grade') }}
              </span>
              <span class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderBy === 'grade_level' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderBy === 'grade_level' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <th></th>
        </tr>
      </Thead>
      <Tbody>
        <tr
          v-for="(invoiceImport, index) in imports.data"
          :key="invoiceImport.id"
        >
          <td class="pl-6 py-4 text-sm">
<!--            <Checkbox-->
<!--              v-model:checked="user.student_selection"-->
<!--              @change="selectStudent(invoiceImport)"-->
<!--              :value="invoiceImport.id"-->
<!--              :id="`student_${invoiceImport.id}`"-->
<!--            />-->
          </td>
          <Td :lighter="false">
            <label :for="`student_${invoiceImport.id}`" class="cursor-pointer">{{ invoiceImport.full_name }}</label>
          </Td>
          <Td>{{ invoiceImport.student_number }}</Td>
          <Td>{{ invoiceImport.grade_level_short_formatted }}</Td>
          <Td class="text-right">
            <Link is="inertia-link" :href="$route('students.show', invoiceImport)">{{ __('View') }}</Link>
          </Td>
        </tr>
      </Tbody>
    </Table>

    <Pagination :meta="imports.meta" :links="imports.links" />
  </Authenticated>
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
import StudentTableFiltersModal from '@/components/modals/StudentTableFiltersModal'
import Link from '@/components/Link'
import Button from '@/components/Button'

export default defineComponent({
  components: {
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

  setup () {
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

    return {
      filters,
      applyFilters,
      resetFilters,
      sortColumn,
      searchTerm,
      selectAll,
      showFilters,
    }
  }
})
</script>
