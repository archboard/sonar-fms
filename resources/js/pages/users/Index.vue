<template>
  <Authenticated>
    <template v-slot:actions>
      <Button class="text-sm" @click.prevent="showModal = true">
        {{ __('Add user') }}
      </Button>
    </template>

    <div class="mb-6 flex space-x-4">
      <div class="relative w-full">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
          <SearchIcon class="h-5 w-5 text-gray-500" />
        </div>
        <Input v-model="searchTerm" class="pl-12" type="search" :placeholder="__('Search by name or email')" />
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
            <div class="flex items-center cursor-pointer" @click="sortColumn('email')">
              <span>
                {{ __('Email') }}
              </span>
              <span class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderBy === 'email' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderBy === 'email' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <th></th>
        </tr>
      </Thead>
      <Tbody>
        <tr
          v-for="(user, index) in users.data"
          :key="user.id"
        >
          <Td :lighter="false">
            {{ user.full_name }}
          </Td>
          <Td>{{ user.email }}</Td>
          <Td class="text-right align-middle">
            <div class="flex items-center justify-end">
              <VerticalDotMenu>
                <div class="px-1 py-1" v-if="canAny('viewAny', 'edit permissions')">
                  <SonarMenuItem v-if="can('viewAny')">
                    Edit
                  </SonarMenuItem>
                  <SonarMenuItem @click="togglePermissions(user)" v-if="can('edit permissions')">
                    Permissions
                  </SonarMenuItem>
                </div>
                <div class="px-1 py-1">
                  <SonarMenuItem>
                    Archive
                  </SonarMenuItem>
                  <SonarMenuItem>
                    Move
                  </SonarMenuItem>
                </div>

                <div class="px-1 py-1" v-if="can('delete')">
                  <SonarMenuItem v-slot="{ active }">
                    <span :class="[active ? '' : 'text-red-500 dark:text-red-400']">Delete</span>
                  </SonarMenuItem>
                </div>
              </VerticalDotMenu>
            </div>
          </Td>
        </tr>
      </Tbody>
    </Table>

    <Pagination :meta="users.meta" :links="users.links" />

    <UserTableFiltersModal
      v-if="showFilters"
      @close="showFilters = false"
      @apply="applyFilters"
      :filters="filters"
      :school="school"
    />
    <CreateUserModal
      v-if="showModal"
      @close="showModal = false"
    />
    <UserPermissionsSlideout
      v-if="permissionsUser.id"
      :user="permissionsUser"
      @close="closePermissions"
    />
  </Authenticated>
</template>

<script>
import { MenuItem } from '@headlessui/vue'
import { defineComponent, inject, ref, watch } from 'vue'
import debounce from 'lodash/debounce'
import { Inertia } from '@inertiajs/inertia'
import handlesFilters from '../../composition/handlesFilters'
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
import UserTableFiltersModal from '../../components/modals/UserTableFiltersModal'
import Link from '@/components/Link'
import Button from '../../components/Button'
import CreateUserModal from '../../components/modals/CreateUserModal'
import { DotsVerticalIcon } from '@heroicons/vue/outline'
import VerticalDotMenu from '../../components/dropdown/VerticalDotMenu'
import SonarMenuItem from '../../components/forms/SonarMenuItem'
import UserPermissionsSlideout from '../../components/slideouts/UserPermissionsSlideout'
import checksPermissions from '../../composition/checksPermissions'

export default defineComponent({
  components: {
    MenuItem,
    UserPermissionsSlideout,
    SonarMenuItem,
    VerticalDotMenu,
    CreateUserModal,
    Button,
    XCircleIcon,
    UserTableFiltersModal,
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
    DotsVerticalIcon,
  },

  props: {
    users: Object,
    user: Object,
    school: Object,
    permissions: Object,
  },

  setup (props) {
    const $http = inject('$http')
    const $route = inject('$route')
    const showFilters = ref(false)
    const selectAll = ref(false)
    const showModal = ref(false)
    const { can, canAny } = checksPermissions(props.permissions)

    // Permissions
    const permissionsUser = ref({})
    const togglePermissions = (user) => {
      permissionsUser.value = user
    }
    const closePermissions = () => {
      togglePermissions({})
      Inertia.reload({ preserveScroll: true })
    }

    const { filters, applyFilters, resetFilters } = handlesFilters({
      s: '',
      perPage: 25,
      page: 1,
      orderBy: 'last_name',
      orderDir: 'asc',
    }, $route('users.index'))
    const searchTerm = ref(filters.s)
    const sortColumn = column => {
      if (column === filters.orderBy) {
        filters.orderDir = filters.orderDir === 'asc'
          ? 'desc'
          : 'asc'
      } else {
        filters.orderBy = column
        filters.orderDir = 'asc'
      }
    }
    watch(searchTerm, debounce(newVal => {
      filters.s = newVal
      filters.page = 1
    }, 500))

    return {
      filters,
      sortColumn,
      showFilters,
      applyFilters,
      resetFilters,
      selectAll,
      searchTerm,
      showModal,
      permissionsUser,
      togglePermissions,
      closePermissions,
      can,
      canAny,
    }
  }
})
</script>
