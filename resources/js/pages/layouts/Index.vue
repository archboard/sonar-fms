<template>
  <Authenticated>
    <template v-slot:actions>
      <Button component="inertia-link" href="/layouts/invoices/create" size="sm">
        {{ __('Add') }}
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
          v-for="layout in layouts.data"
          :key="layout.id"
        >
          <Td :lighter="false">
            <div class="flex items-center">
              {{ layout.name }}
              <SolidBadge v-if="layout.is_default" color="primary" class="ml-2">{{ __('Default') }}</SolidBadge>
            </div>
          </Td>
          <Td>
            {{ layout.paper_size }}
          </Td>
          <Td class="text-right space-x-3">
            <VerticalDotMenu>
              <div class="p-1">
                <SonarMenuItem v-if="!layout.is_default && can('layouts.update')" :href="`/layouts/invoices/${layout.id}/default`" method="post" as="button" is="InertiaLink">
                  {{ __('Make default') }}
                </SonarMenuItem>
                <SonarMenuItem :href="`/layouts/invoices/${layout.id}/preview`" is="a">
                  {{ __('Preview') }}
                </SonarMenuItem>
                <SonarMenuItem v-if="can('layouts.update')" :href="`/layouts/invoices/${layout.id}/edit`" is="InertiaLink">
                  {{ __('Edit') }}
                </SonarMenuItem>
                <SonarMenuItem v-if="can('layouts.delete')" @click.prevent="layoutToDelete = layout">
                  {{ __('Delete') }}
                </SonarMenuItem>
              </div>
            </VerticalDotMenu>
          </Td>
        </tr>
        <tr v-if="layouts.meta.total === 0">
          <Td class="text-center" colspan="3">
            {{ __('No layouts exist.') }} <Link href="/layouts/invoices/create">{{ __('Add one') }}</Link>.
          </Td>
        </tr>
      </Tbody>
    </Table>

    <Pagination :meta="layouts.meta" :links="layouts.links" />
  </Authenticated>

  <ConfirmationModal
    v-if="layoutToDelete.id"
    @close="layoutToDelete = {}"
    @confirmed="deleteLayout"
  />
</template>

<script>
import { defineComponent, ref } from 'vue'
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
import SolidBadge from '@/components/SolidBadge'
import VerticalDotMenu from '@/components/dropdown/VerticalDotMenu'
import SonarMenuItem from '@/components/forms/SonarMenuItem'
import checksPermissions from '@/composition/checksPermissions'
import ConfirmationModal from '@/components/modals/ConfirmationModal'
import { Inertia } from '@inertiajs/inertia'

export default defineComponent({
  mixins: [PageProps],
  components: {
    ConfirmationModal,
    SonarMenuItem,
    VerticalDotMenu,
    SolidBadge,
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
    permissions: Object,
  },

  setup () {
    const { filters, applyFilters, resetFilters, sortColumn } = handlesFilters({
      s: '',
      perPage: 15,
      page: 1,
      orderBy: 'name',
      orderDir: 'asc',
    }, '/layouts/invoices')
    const { searchTerm } = searchesItems(filters)
    const { can } = checksPermissions()
    const layoutToDelete = ref({})
    const deleteLayout = () => {
      Inertia.delete(`/layouts/invoices/${layoutToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
          layoutToDelete.value = {}
        }
      })
    }

    return {
      filters,
      applyFilters,
      resetFilters,
      sortColumn,
      searchTerm,
      can,
      layoutToDelete,
      deleteLayout,
    }
  }
})
</script>
