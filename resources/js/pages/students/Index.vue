<template>
  <Authenticated>
    <div class="mb-6 flex space-x-4">
      <div class="relative w-full">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
          <MagnifyingGlass class="h-5 w-5 text-gray-500" />
        </div>
        <Input v-model="searchTerm" class="pl-12" type="search" :placeholder="__('Search by name, email or student number')" />
      </div>
      <button @click.prevent="showFilters = true" class="w-auto bg-white border border-gray-300 dark:border-gray-900 dark:focus:border-primary-500 dark:bg-gray-700 rounded-md px-4 shadow focus:outline-none transition hover:ring hover:ring-primary-500 hover:ring-opacity-50 focus:ring focus:ring-offset-primary-500 focus:ring-primary-500" :title="__('Filters')">
        <Adjustments class="w-6 h-6" />
      </button>
      <button @click.prevent="resetFilters" class="w-auto bg-white border border-gray-300 dark:border-gray-900 dark:focus:border-primary-500 dark:bg-gray-700 rounded-md px-4 shadow focus:outline-none transition hover:ring hover:ring-primary-500 hover:ring-opacity-50 focus:ring focus:ring-offset-primary-500 focus:ring-primary-500" :title="__('Reset filters')">
        <CircleX class="w-6 h-6" />
      </button>
    </div>

    <div v-if="user.student_selection.length > 0" class="text-gray-500 dark:text-gray-300 mb-4">
      {{ __(':count students selected', { count: user.student_selection.length }) }} <a href="#" class="ml-3 font-medium hover:underline" @click.prevent="clearSelection">Remove selection</a>
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
                <SortAsc v-if="filters.orderBy === 'last_name' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDesc v-if="filters.orderBy === 'last_name' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <Th>
            <div class="flex items-center cursor-pointer" @click="sortColumn('student_number')">
              <span>
                {{ __('Student Number') }}
              </span>
              <span class="relative h-4 w-4 ml-2">
                <SortAsc v-if="filters.orderBy === 'student_number' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDesc v-if="filters.orderBy === 'student_number' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <Th>
            <div class="flex items-center cursor-pointer" @click="sortColumn('grade_level')">
              <span>
                {{ __('Grade') }}
              </span>
              <span class="relative h-4 w-4 ml-2">
                <SortAsc v-if="filters.orderBy === 'grade_level' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDesc v-if="filters.orderBy === 'grade_level' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
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
            <label :for="`student_${student.id}`" class="cursor-pointer">{{ student.full_name }}</label>
          </Td>
          <Td>{{ student.student_number }}</Td>
          <Td>{{ student.grade_level }}</Td>
          <Td class="text-right">
            <Link is="inertia-link" :href="$route('students.show', student)">{{ __('View') }}</Link>
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
import debounce from 'lodash/debounce'
import handlesFilters from '../../composition/handlesFilters'
import Authenticated from '../../layouts/Authenticated'
import Table from '../../components/tables/Table'
import Thead from '../../components/tables/Thead'
import Th from '../../components/tables/Th'
import Tbody from '../../components/tables/Tbody'
import Td from '../../components/tables/Td'
import Checkbox from '../../components/forms/Checkbox'
import Pagination from '../../components/tables/Pagination'
import SortAsc from '../../components/icons/sort-asc'
import SortDesc from '../../components/icons/sort-desc'
import Input from '../../components/forms/Input'
import MagnifyingGlass from '../../components/icons/magnifying-glass'
import Adjustments from '../../components/icons/adjustments'
import StudentTableFiltersModal from '../../components/modals/StudentTableFiltersModal'
import CircleX from '../../components/icons/circle-x'
import Link from '@/components/Link'

export default defineComponent({
  components: {
    CircleX,
    StudentTableFiltersModal,
    Adjustments,
    MagnifyingGlass,
    Input,
    SortDesc,
    SortAsc,
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
    const { filters, applyFilters, resetFilters } = handlesFilters({
      s: '',
      perPage: 25,
      page: 1,
      orderBy: 'last_name',
      orderDir: 'asc',
      grades: [],
      enrolled: true,
    }, $route('students.index'))
    const searchTerm = ref(filters.s)
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
    watch(selectAll, (newVal) => {
      if (newVal) {
        Inertia.post($route('student-selection.store'), filters)
      } else {
        clearSelection()
      }
    })
    watch(searchTerm, debounce(newVal => {
      filters.s = newVal
      filters.page = 1
    }, 500))

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
    }
  }
})
</script>
