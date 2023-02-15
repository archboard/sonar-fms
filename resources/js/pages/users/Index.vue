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
          <Th>{{ __('Roles') }}</Th>
          <th></th>
        </tr>
      </Thead>
      <Tbody>
        <tr
          v-for="schoolUser in users.data"
          :key="schoolUser.id"
        >
          <Td :lighter="false">
            {{ schoolUser.full_name }}
          </Td>
          <Td>{{ schoolUser.email }}</Td>
          <Td>
            <SolidBadge v-if="schoolUser.manages_tenancy" color="primary" class="mr-1">{{ __('Manages tenancy') }}</SolidBadge>
            <SolidBadge
              v-for="role in schoolUser.roles"
              :key="role"
              class="mr-1"
            >
              {{ role }}
            </SolidBadge>
          </Td>
          <Td class="text-right align-middle">
            <div class="flex items-center justify-end">
              <VerticalDotMenu>
                <div class="px-1 py-1" v-if="canAny('view', 'edit permissions')">
<!--                  <SonarMenuItem v-if="can('view')">-->
<!--                    {{ __('Edit') }}-->
<!--                  </SonarMenuItem>-->
                  <SonarMenuItem @click="togglePermissions(schoolUser)" v-if="can('edit permissions')">
                    {{ __('Permissions') }}
                  </SonarMenuItem>
                  <SonarMenuItem @click="schoolsUser = schoolUser" v-if="user.manages_tenancy">
                    {{ __('School access') }}
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
      :auth-user-manages-school="isSchoolAdmin"
      @close="closePermissions"
    />
    <SchoolAccessSlideOut
      v-if="schoolsUser.id"
      :user="schoolsUser"
      @close="schoolsUser = {}"
    />
  </Authenticated>
</template>

<script>
import { MenuItem } from '@headlessui/vue'
import { defineComponent, inject, ref } from 'vue'
import searchesItems from '@/composition/searchesItems'
import { Inertia } from '@inertiajs/inertia'
import handlesFilters from '@/composition/handlesFilters'
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
import UserTableFiltersModal from '@/components/modals/UserTableFiltersModal.vue'
import Link from '@/components/Link.vue'
import Button from '@/components/Button.vue'
import CreateUserModal from '@/components/modals/CreateUserModal.vue'
import { DotsVerticalIcon } from '@heroicons/vue/outline'
import VerticalDotMenu from '@/components/dropdown/VerticalDotMenu.vue'
import SonarMenuItem from '@/components/forms/SonarMenuItem.vue'
import UserPermissionsSlideout from '@/components/slideouts/UserPermissionsSlideout.vue'
import checksPermissions from '@/composition/checksPermissions'
import SchoolAccessSlideOut from '@/components/slideouts/SchoolAccessSlideOut.vue'
import SolidBadge from '@/components/SolidBadge.vue'

export default defineComponent({
  components: {
    SolidBadge,
    SchoolAccessSlideOut,
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
    isSchoolAdmin: Boolean,
  },

  setup (props) {
    const $route = inject('$route')
    const showFilters = ref(false)
    const selectAll = ref(false)
    const showModal = ref(false)
    const { can, canAny } = checksPermissions(props.permissions)

    // Permissions
    const permissionsUser = ref({})
    const schoolsUser = ref({})
    const togglePermissions = (user) => {
      permissionsUser.value = user
    }
    const closePermissions = () => {
      togglePermissions({})
      Inertia.reload({ preserveScroll: true })
    }

    const { filters, applyFilters, resetFilters, sortColumn } = handlesFilters({
      s: '',
      perPage: 25,
      page: 1,
      orderBy: 'last_name',
      orderDir: 'asc',
    }, $route('users.index'))
    const { searchTerm } = searchesItems(filters)

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
      schoolsUser,
    }
  }
})
</script>
