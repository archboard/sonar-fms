<template>
  <Authenticated>
    <div class="flex mb-2 space-x-4">
      <div class="relative w-full">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
          <SearchIcon class="h-5 w-5 text-gray-500" />
        </div>
        <Input v-model="searchTerm" class="pl-12" type="search" :placeholder="__('Search by name, email or student number')" />
      </div>

      <FilterButton @click.prevent="showFilters = true" />
      <ClearFilterButton @click.prevent="resetFilters" />
    </div>

    <div class="space-x-2 pt-1 flex flex-wrap">
      <FadeInGroup>
        <DismissibleBadge v-if="filters.status !== 'enrolled'" @dismiss="filters.status = 'enrolled'">
          <span v-if="filters.status === 'withdrawn'">{{ __('Not enrolled') }}</span>
          <span v-else>{{ __('Enrolled and not enrolled') }}</span>
        </DismissibleBadge>

        <DismissibleBadge
          v-for="(grade, index) in filters.grades"
          :key="grade"
          @dismiss="filters.grades.splice(index, 1)"
        >
          {{ displayLongGrade(grade) }}
        </DismissibleBadge>
      </FadeInGroup>
    </div>

    <FadeIn>
      <div v-if="user.student_selection.length > 0" class="text-gray-500 dark:text-gray-300 mt-4 -mb-2 flex text-sm">
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

    <Table class="mt-6">
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
            <div class="flex items-center justify-center">
              <Checkbox
                v-model:checked="user.student_selection"
                @change="selectStudent(student)"
                :value="student.id"
                :id="`student_${student.id}`"
              />
            </div>
          </td>
          <Td :lighter="false">
            <div class="flex items-center space-x-2">
              <TableLink :href="`/students/${student.id}`">{{ student.full_name }}</TableLink>
              <XCircleIcon v-if="!student.enrolled" class="h-5 w-5 text-yellow-500" :title="__('Not enrolled')" />
            </div>
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
import { SearchIcon, SortAscendingIcon, SortDescendingIcon, XCircleIcon } from '@heroicons/vue/outline'
import StudentTableFiltersModal from '@/components/modals/StudentTableFiltersModal'
import Link from '@/components/Link'
import checksPermissions from '@/composition/checksPermissions'
import PageProps from '@/mixins/PageProps'
import VerticalDotMenu from '@/components/dropdown/VerticalDotMenu'
import SonarMenuItem from '@/components/forms/SonarMenuItem'
import FadeIn from '@/components/transitions/FadeIn'
import TableLink from '@/components/tables/TableLink'
import DismissibleBadge from '@/components/DismissibleBadge'
import FadeInGroup from '@/components/transitions/FadeInGroup'
import displaysGrades from '@/composition/displaysGrades'
import FilterButton from '@/components/FilterButton'
import ClearFilterButton from '@/components/ClearFilterButton'

export default defineComponent({
  mixins: [PageProps],
  components: {
    ClearFilterButton,
    FilterButton,
    FadeInGroup,
    DismissibleBadge,
    TableLink,
    FadeIn,
    SonarMenuItem,
    VerticalDotMenu,
    XCircleIcon,
    StudentTableFiltersModal,
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
    const { displayLongGrade } = displaysGrades()
    const { can } = checksPermissions(props.permissions)
    const { filters, applyFilters, resetFilters, sortColumn } = handlesFilters(
      {
        s: '',
        perPage: 25,
        page: 1,
        orderBy: 'last_name',
        orderDir: 'asc',
        grades: [],
        status: 'enrolled',
      },
      $route('students.index'),
      {}
    )
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
      displayLongGrade,
    }
  }
})
</script>
