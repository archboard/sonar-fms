<template>
  <Authenticated>
    <div class="mb-6 flex space-x-4">
      <div class="relative w-full">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
          <SearchIcon class="h-5 w-5 text-gray-500" />
        </div>
        <Input v-model="searchTerm" class="pl-12" type="search" :placeholder="__('Search by name, email or student number')" />
      </div>
      <button @click.prevent="showFilters = true" class="w-auto bg-white border border-gray-300 dark:border-gray-900 dark:focus:border-primary-500 dark:bg-gray-700 rounded-md px-4 shadow focus:outline-none transition hover:ring hover:ring-primary-500 hover:ring-opacity-50 focus:ring focus:ring-offset-primary-500 focus:ring-primary-500" :title="__('Filters')">
        <AdjustmentsIcon class="w-6 h-6" />
      </button>
      <button @click.prevent="resetFilters" class="w-auto bg-white border border-gray-300 dark:border-gray-900 dark:focus:border-primary-500 dark:bg-gray-700 rounded-md px-4 shadow focus:outline-none transition hover:ring hover:ring-primary-500 hover:ring-opacity-50 focus:ring focus:ring-offset-primary-500 focus:ring-primary-500" :title="__('Reset filters')">
        <XCircleIcon class="w-6 h-6" />
      </button>
    </div>

    <FadeIn>
      <div v-if="user.student_selection.length > 0" class="text-gray-500 dark:text-gray-300 mb-4 flex text-sm">
        <span v-if="user.student_selection.length === 1">
          {{ __(':count student selected', { count: user.student_selection.length }) }}
        </span>
        <span v-else>
          {{ __(':count students selected', { count: user.student_selection.length }) }}
        </span>
        <div class="space-x-3 ml-3">
          <Link is="a" href="#" @click.prevent="clearSelection">
            {{ __('Remove selection') }}
          </Link>
          <Link :href="$route('selection.invoices.create')">
            {{ __('Create invoice') }}
          </Link>
        </div>
      </div>
    </FadeIn>

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
                {{ __('Student number') }}
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
          v-for="(student, index) in students.data"
          :key="student.id"
        >
          <td class="pl-6 py-4 text-sm">
            <Checkbox
              v-model:checked="user.student_selection"
              @change="selectStudent(student)"
              :value="student.id"
              :id="`student_${student.id}`"
            />
          </td>
          <Td :lighter="false">
            <TableLink :href="`/students/${student.id}`">{{ student.full_name }}</TableLink>
          </Td>
          <Td>{{ student.student_number }}</Td>
          <Td>{{ student.grade_level_short_formatted }}</Td>
          <Td class="text-right">
            <VerticalDotMenu>
              <div class="p-1">
                <SonarMenuItem v-if="can('students.viewAny')" is="inertia-link" :href="`/students/${student.id}`">
                  {{ __('View') }}
                </SonarMenuItem>
                <SonarMenuItem v-if="can('invoices.create')" is="inertia-link" :href="$route('students.invoices.create', student)">
                  {{ __('New invoice') }}
                </SonarMenuItem>
              </div>
            </VerticalDotMenu>
          </Td>
        </tr>
      </Tbody>
    </Table>

    <Pagination :meta="students.meta" :links="students.links" />

    <StudentTableFiltersModal
      v-if="showFilters"
      @close="showFilters = false"
      @apply="applyFilters"
      :filters="filters"
      :school="school"
    />
  </Authenticated>
</template>

<script>
import { defineComponent, inject, nextTick, ref, watch } from 'vue'
import { Inertia } from '@inertiajs/inertia'
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
import checksPermissions from '@/composition/checksPermissions'
import PageProps from '@/mixins/PageProps'
import VerticalDotMenu from '@/components/dropdown/VerticalDotMenu'
import SonarMenuItem from '@/components/forms/SonarMenuItem'
import FadeIn from '@/components/transitions/FadeIn'
import TableLink from '@/components/tables/TableLink'

export default defineComponent({
  mixins: [PageProps],
  components: {
    TableLink,
    FadeIn,
    SonarMenuItem,
    VerticalDotMenu,
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
    students: Object,
    user: Object,
    school: Object,
  },

  setup (props) {
    const $http = inject('$http')
    const $route = inject('$route')
    const showFilters = ref(false)
    const selectAll = ref(false)
    const { can } = checksPermissions(props.permissions)
    const { filters, applyFilters, resetFilters, sortColumn } = handlesFilters({
      s: '',
      perPage: 25,
      page: 1,
      orderBy: 'last_name',
      orderDir: 'asc',
      grades: [],
      enrolled: true,
    }, $route('students.index'))
    const { searchTerm } = searchesItems(filters)
    const selectStudent = student => {
      nextTick(() => {
        const add = props.user.student_selection.includes(student.id)
        const method = add ? 'put' : 'delete'

        $http[method]($route('student-selection.update', student.id))
      })
    }
    const clearSelection = async () => {
      await $http.delete($route('student-selection.remove'))
      props.user.student_selection = []
    }
    watch(selectAll, (newVal) => {
      if (newVal) {
        Inertia.post($route('student-selection.store'), filters.value)
      } else {
        clearSelection()
      }
    })

    return {
      filters,
      selectStudent,
      sortColumn,
      showFilters,
      clearSelection,
      applyFilters,
      resetFilters,
      selectAll,
      searchTerm,
      can,
    }
  }
})
</script>
