<template>
  <Authenticated>
    <template v-slot:actions>
      <Button class="text-sm" @click.prevent="displayModal({})">
        {{ __('Add scholarship') }}
      </Button>
    </template>

    <div class="mb-6 flex space-x-4">
      <div class="relative w-full">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
          <SearchIcon class="h-5 w-5 text-gray-500" />
        </div>
        <Input v-model="searchTerm" class="pl-12" type="search" :placeholder="__('Search by name or description')" />
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
            <div class="flex items-center justify-end cursor-pointer" @click="sortColumn('percentage')">
              <span>
                {{ __('Percentage') }}
              </span>
              <span v-if="filters.orderBy === 'percentage'" class="relative h-4 w-4 ml-2">
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
          v-for="(scholarship, index) in scholarships.data"
          :key="scholarship.id"
        >
          <Td :lighter="false">
            {{ scholarship.name }}
            <HelpText v-if="scholarship.description">
              {{ scholarship.description }}
            </HelpText>
          </Td>
          <Td class="text-right">{{ displayCurrency(scholarship.amount) }}</Td>
          <Td class="text-right">{{ scholarship.percentage_formatted }}</Td>
          <Td class="text-right space-x-2">
            <Link is="inertia-link" :href="$route('scholarships.show', scholarship)">{{ __('View') }}</Link>
            <Link is="a" href="#" @click.prevent="displayModal(scholarship)">{{ __('Edit') }}</Link>
          </Td>
        </tr>
        <tr v-if="scholarships.data.length === 0">
          <Td colspan="4" class="text-center">
            {{ __('No scholarships exist yet.') }} <Link @click.prevent="showModal = true" is="button">{{ __('Add one') }}</Link>.
          </Td>
        </tr>
      </Tbody>
    </Table>

    <Pagination :meta="scholarships.meta" :links="scholarships.links" />

    <ScholarshipFormModal
      v-if="showModal"
      :scholarship="selectedScholarship"
      @close="showModal = false"
    />
  </Authenticated>
</template>

<script>
import { defineComponent, inject, ref } from 'vue'
import handlesFilters from '@/composition/handlesFilters'
import searchesItems from '@/composition/searchesItems'
import Authenticated from '@/layouts/Authenticated'
import TableComponents from '@/components/tables'
import Checkbox from '@/components/forms/Checkbox'
import Input from '@/components/forms/Input'
import { SearchIcon, SortAscendingIcon, SortDescendingIcon, AdjustmentsIcon, XCircleIcon } from '@heroicons/vue/outline'
import Link from '@/components/Link'
import HelpText from '@/components/HelpText'
import Button from '@/components/Button'
import displaysCurrency from '@/composition/displaysCurrency'
import ScholarshipFormModal from '@/components/modals/ScholarshipFormModal'

export default defineComponent({
  components: {
    ScholarshipFormModal,
    ...TableComponents,
    Button,
    HelpText,
    XCircleIcon,
    AdjustmentsIcon,
    SearchIcon,
    Input,
    SortDescendingIcon,
    SortAscendingIcon,
    Checkbox,
    Authenticated,
    Link,
  },

  props: {
    scholarships: Object,
    user: Object,
    school: Object,
  },

  setup () {
    const $route = inject('$route')
    const showFilters = ref(false)
    const selectAll = ref(false)
    const showModal = ref(false)
    const selectedScholarship = ref({})
    const { filters, applyFilters, resetFilters, sortColumn } = handlesFilters({
      s: '',
      perPage: 15,
      page: 1,
      orderBy: 'name',
      orderDir: 'asc',
    }, $route('scholarships.index'))
    const { searchTerm } = searchesItems(filters)
    const { displayCurrency } = displaysCurrency()

    const displayModal = (fee = {}) => {
      selectedScholarship.value = fee
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
      selectedScholarship,
      showModal,
      displayCurrency,
      displayModal,
    }
  }
})
</script>
