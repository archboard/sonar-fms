<template>
  <Authenticated>
    <template v-slot:actions>
      <Button component="inertia-link" :href="$route('layouts.create')">
        {{ __('Add') }}
      </Button>
    </template>

    <div class="mb-6 flex space-x-4">
      <div class="relative w-full">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
          <SearchIcon class="h-5 w-5 text-gray-500" />
        </div>
        <Input v-model="searchTerm" class="pl-12" type="search" :placeholder="__('Search by name or description')" />
      </div>
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
            {{ __('Paper size') }}
          </Th>
          <th></th>
        </tr>
      </Thead>
      <Tbody>
        <tr
          v-for="(layout, index) in layouts.data"
          :key="layout.id"
        >
          <Td :lighter="false">
            {{ layout.name }}
          </Td>
          <Td>
            {{ layout.paper_size }}
          </Td>
          <Td class="text-right space-x-2">
            <Link :href="$route('layouts.edit', layout)">{{ __('Edit') }}</Link>
          </Td>
        </tr>
        <tr v-if="layouts.meta.total === 0">
          <Td class="text-center" colspan="2">
            {{ __('No invoice layouts exist.') }} <Link :href="$route('layouts.create')">{{ __('Add one') }}</Link>.
          </Td>
        </tr>
      </Tbody>
    </Table>

    <Pagination :meta="layouts.meta" :links="layouts.links" />
  </Authenticated>
</template>

<script>
import { defineComponent, inject } from 'vue'
import Authenticated from '@/layouts/Authenticated'
import Button from '@/components/Button'
import handlesFilters from '@/composition/handlesFilters'
import searchesItems from '@/composition/searchesItems'
import TableComponents from '@/components/tables'
import Checkbox from '@/components/forms/Checkbox'
import Input from '@/components/forms/Input'
import { SearchIcon, SortAscendingIcon, SortDescendingIcon, AdjustmentsIcon, XCircleIcon } from '@heroicons/vue/outline'
import Link from '@/components/Link'
import HelpText from '@/components/HelpText'
import ScholarshipFormModal from '@/components/modals/ScholarshipFormModal'
import PageProps from '@/mixins/PageProps'
import Pagination from '@/components/tables/Pagination'

export default defineComponent({
  mixins: [PageProps],
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
    Pagination,
  },
  props: {
    layouts: Object,
  },

  setup () {
    const $route = inject('$route')
    const { filters, applyFilters, resetFilters, sortColumn } = handlesFilters({
      s: '',
      perPage: 15,
      page: 1,
      orderBy: 'name',
      orderDir: 'asc',
    }, $route('layouts.index'))
    const { searchTerm } = searchesItems(filters)

    return {
      filters,
      applyFilters,
      resetFilters,
      sortColumn,
      searchTerm,
    }
  }
})
</script>
