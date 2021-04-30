<template>
  <Authenticated>
    <template v-slot:actions>
      <Button class="text-sm" @click.prevent="displayModal({})">
        {{ __('Add fee') }}
      </Button>
    </template>

    <div class="mb-6 flex space-x-4">
      <div class="relative w-full">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
          <SearchIcon class="h-5 w-5 text-gray-500" />
        </div>
        <Input v-model="searchTerm" class="pl-12" type="search" :placeholder="__('Search by name, code or description')" />
      </div>
      <button @click.prevent="showFilters = true" class="w-auto bg-white border border-gray-300 dark:border-gray-900 dark:focus:border-primary-500 dark:bg-gray-700 rounded-md px-4 shadow focus:outline-none transition hover:ring hover:ring-primary-500 hover:ring-opacity-50 focus:ring focus:ring-offset-primary-500 focus:ring-primary-500" :title="__('Filters')">
        <AdjustmentsIcon class="w-6 h-6" />
      </button>
      <button @click.prevent="resetFilters" class="w-auto bg-white border border-gray-300 dark:border-gray-900 dark:focus:border-primary-500 dark:bg-gray-700 rounded-md px-4 shadow focus:outline-none transition hover:ring hover:ring-primary-500 hover:ring-opacity-50 focus:ring focus:ring-offset-primary-500 focus:ring-primary-500" :title="__('Reset filters')">
        <XCircleIcon class="w-6 h-6" />
      </button>
    </div>

    <Table>
      <Thead>
        <tr>
          <Th>
            <div class="flex items-center cursor-pointer" @click="sortColumn('name')">
              <span>
                {{ __('Name') }}
              </span>
              <span class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderBy === 'name' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderBy === 'name' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <Th>
            <div class="flex items-center justify-end cursor-pointer" @click="sortColumn('amount')">
              <span>
                {{ __('Amount') }}
              </span>
              <span v-if="filters.orderBy === 'amount'" class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <Th>
            <div class="flex items-center cursor-pointer" @click="sortColumn('fee_categories.name')">
              <span>
                {{ __('Category') }}
              </span>
              <span class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderBy === 'fee_categories.name' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderBy === 'fee_categories.name' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <Th>
            <div class="flex items-center cursor-pointer" @click="sortColumn('departments.name')">
              <span>
                {{ __('Department') }}
              </span>
              <span class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderBy === 'departments.name' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderBy === 'departments.name' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <th></th>
        </tr>
      </Thead>
      <Tbody>
        <tr
          v-for="(fee, index) in fees.data"
          :key="fee.id"
        >
          <Td :lighter="false">
            {{ fee.name }} <span v-if="fee.code" class="text-gray-400 dark:text-gray-500">({{ fee.code }})</span>
            <HelpText v-if="fee.description">
              {{ fee.description }}
            </HelpText>
          </Td>
          <Td class="text-right">{{ displayCurrency(fee.amount) }}</Td>
          <Td>{{ fee.fee_category?.name }}</Td>
          <Td>{{ fee.department?.name }}</Td>
          <Td class="text-right space-x-2">
            <Link is="inertia-link" :href="$route('fees.show', fee)">{{ __('View') }}</Link>
            <Link is="a" href="#" @click.prevent="displayModal(fee)">{{ __('Edit') }}</Link>
          </Td>
        </tr>
      </Tbody>
    </Table>

    <Pagination :meta="fees.meta" :links="fees.links" />

    <FeeFormModal
      v-if="showModal"
      :fee="selectedFee"
      @close="showModal = false"
    />
  </Authenticated>
</template>

<script>
import { defineComponent, inject, ref, watch } from 'vue'
import debounce from 'lodash/debounce'
import handlesFilters from '../../composition/handlesFilters'
import searchesItems from '../../composition/searchesItems'
import Authenticated from '../../layouts/Authenticated'
import Table from '../../components/tables/Table'
import Thead from '../../components/tables/Thead'
import Th from '../../components/tables/Th'
import Tbody from '../../components/tables/Tbody'
import Td from '../../components/tables/Td'
import Checkbox from '../../components/forms/Checkbox'
import Pagination from '../../components/tables/Pagination'
import Input from '../../components/forms/Input'
import { SearchIcon, SortAscendingIcon, SortDescendingIcon, AdjustmentsIcon, XCircleIcon } from '@heroicons/vue/outline'
import Link from '@/components/Link'
import HelpText from '../../components/HelpText'
import Button from '../../components/Button'
import FeeFormModal from '../../components/modals/FeeFormModal'
import displaysCurrency from '../../composition/displaysCurrency'

export default defineComponent({
  components: {
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
    fees: Object,
    user: Object,
    school: Object,
  },

  setup () {
    const $route = inject('$route')
    const showFilters = ref(false)
    const selectAll = ref(false)
    const showModal = ref(false)
    const selectedFee = ref({})
    const { filters, applyFilters, resetFilters, sortColumn } = handlesFilters({
      s: '',
      perPage: 15,
      page: 1,
      orderBy: 'name',
      orderDir: 'asc',
    }, $route('fees.index'))
    const { searchTerm } = searchesItems(filters)
    const { displayCurrency } = displaysCurrency()

    const displayModal = (fee = {}) => {
      selectedFee.value = fee
      showModal.value = true
    }

    return {
      filters,
      sortColumn,
      showFilters,
      applyFilters,
      resetFilters,
      selectAll,
      searchTerm,
      selectedFee,
      showModal,
      displayCurrency,
      displayModal,
    }
  }
})
</script>
