<template>
  <Authenticated>
    <template #actions>
      <Button size="sm" component="InertiaLink" href="/payments/imports" color="white">
        {{ __('Payment imports') }}
      </Button>
      <Dropdown
        size="sm"
        :menu-items="[
          {
            label: __('Create manually'),
            route: `/payments/create`,
          },
          {
            label: __('Import'),
            route: `/payments/imports/create`,
          },
        ]"
      >
        {{ __('Record payment') }}
      </Dropdown>
    </template>

    <div class="mb-2 flex flex-wrap lg:space-x-4">
      <div class="relative flex-1 w-full mb-4 lg:mb-0 lg:w-auto">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
          <SearchIcon class="h-5 w-5 text-gray-500" />
        </div>
        <Input v-model="searchTerm" class="pl-12" type="search" :placeholder="__('Search by invoice or student')" />
      </div>
      <div class="w-full lg:w-auto space-x-2 lg:space-x-4 flex">
        <FilterButton @click.prevent="showFilters = true" />
        <ClearFilterButton @click.prevent="resetFilters" />
        <ExportButton @click.prevent="promptExport = true" />
      </div>
    </div>

    <div class="space-x-2 pt-1 flex flex-wrap">
      <FadeInGroup>
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
<!--          <th class="w-8 text-left pl-6">-->
<!--            <Checkbox v-model:checked="selectAll" />-->
<!--          </th>-->
          <Th>
            {{ __('#') }}
          </Th>
          <Th>
            <div class="flex items-center cursor-pointer" @click="sortColumn('title')">
              <span>
                {{ __('Invoice title') }}
              </span>
              <span class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderBy === 'title' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderBy === 'title' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <Th>
            <div class="flex items-center cursor-pointer" @click="sortColumn('paid_at')">
              <span>
                {{ __('Date') }}
              </span>
              <span class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderBy === 'paid_at' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderBy === 'paid_at' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <Th>
            <div class="flex items-center justify-end cursor-pointer" @click="sortColumn('amount')">
              <span>
                {{ __('Amount') }}
              </span>
              <span v-if="filters.orderBy === 'amount'" class="relative h-4 w-4 ml-2">
                <SortAscendingIcon v-if="filters.orderBy === 'amount' && filters.orderDir === 'asc'" class="top-0 left-0 w-4 h-4 absolute" />
                <SortDescendingIcon v-if="filters.orderBy === 'amount' && filters.orderDir === 'desc'" class="top-0 left-0 w-4 h-4 absolute" />
              </span>
            </div>
          </Th>
          <th></th>
        </tr>
      </Thead>
      <Tbody>
        <tr
          v-for="payment in payments.data"
          :key="payment.id"
        >
<!--          <td class="pl-6 py-4 text-sm">-->
<!--            <div class="flex items-center justify-center">-->
<!--              <Checkbox-->
<!--                v-model:checked="user.student_selection"-->
<!--                @change="selectStudent(payment)"-->
<!--                :value="payment.id"-->
<!--                :id="`student_${payment.id}`"-->
<!--              />-->
<!--            </div>-->
<!--          </td>-->
          <Td>
            <span class="whitespace-nowrap flex items-center space-x-2">
              <span>{{ payment.invoice.invoice_number }}</span>
              <CollectionIcon v-if="payment.invoice.is_parent" class="w-4 h-4" />
            </span>
          </Td>
          <Td :lighter="false" class="space-x-2">
            <TableLink :href="`/invoices/${payment.invoice.uuid}`" class="whitespace-nowrap">
              {{ payment.invoice.title }}
            </TableLink>
            <InvoiceStatusBadge :invoice="payment.invoice" size="sm" />
          </Td>
          <Td>{{ payment.paid_at_formatted }}</Td>
          <Td class="text-right">{{ payment.amount_formatted }}</Td>
          <Td class="text-right">
            <VerticalDotMenu>
              <PaymentActionItems
                :payment="payment"
                @details="currentPayment = payment"
              />
            </VerticalDotMenu>
          </Td>
        </tr>

        <tr v-if="payments.data.length === 0">
          <Td colspan="7" class="text-center">
            {{ __('No results.') }}
          </Td>
        </tr>
      </Tbody>
    </Table>

    <Pagination :meta="payments.meta" :links="payments.links" />

    <PaymentTableFiltersModal
      v-if="showFilters"
      @close="showFilters = false"
      @apply="applyFilters"
      :filters="filters"
      :school="school"
    />
    <PaymentDetailsModal
      v-if="currentPayment.id"
      @close="currentPayment = {}"
      :payment="currentPayment"
    />
  </Authenticated>

  <ExportPromptModal
    v-if="promptExport"
    @close="promptExport = false"
    url="/export/payments"
    :filters="filters"
  />
</template>

<script>
import { defineComponent, inject, nextTick, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import handlesFilters from '@/composition/handlesFilters.js'
import searchesItems from '@/composition/searchesItems.js'
import Authenticated from '@/layouts/Authenticated.vue'
import Table from '@/components/tables/Table.vue'
import Thead from '@/components/tables/Thead.vue'
import Th from '@/components/tables/Th.vue'
import Tbody from '@/components/tables/Tbody.vue'
import Td from '@/components/tables/Td.vue'
import Checkbox from '@/components/forms/Checkbox.vue'
import Pagination from '@/components/tables/Pagination.vue'
import Input from '@/components/forms/Input.vue'
import { SearchIcon, SortAscendingIcon, SortDescendingIcon, XCircleIcon, CollectionIcon } from '@heroicons/vue/outline'
import Link from '@/components/Link.vue'
import checksPermissions from '@/composition/checksPermissions.js'
import PageProps from '@/mixins/PageProps'
import VerticalDotMenu from '@/components/dropdown/VerticalDotMenu.vue'
import SonarMenuItem from '@/components/forms/SonarMenuItem.vue'
import FadeIn from '@/components/transitions/FadeIn.vue'
import TableLink from '@/components/tables/TableLink.vue'
import DismissibleBadge from '@/components/DismissibleBadge.vue'
import FadeInGroup from '@/components/transitions/FadeInGroup.vue'
import displaysGrades from '@/composition/displaysGrades.js'
import FilterButton from '@/components/FilterButton.vue'
import ClearFilterButton from '@/components/ClearFilterButton.vue'
import InvoiceStatusBadge from '@/components/InvoiceStatusBadge.vue'
import Dropdown from '@/components/forms/Dropdown.vue'
import Button from '@/components/Button.vue'
import PaymentTableFiltersModal from '@/components/modals/PaymentTableFiltersModal.vue'
import PaymentDetailsModal from '@/components/modals/PaymentDetailsModal.vue'
import PaymentActionItems from '@/components/PaymentActionItems.vue'
import ExportButton from '@/components/ExportButton.vue'
import ExportPromptModal from '@/components/modals/ExportPromptModal.vue'

export default defineComponent({
  mixins: [PageProps],
  components: {
    ExportPromptModal,
    ExportButton,
    PaymentActionItems,
    PaymentDetailsModal,
    PaymentTableFiltersModal,
    Button,
    Dropdown,
    InvoiceStatusBadge,
    ClearFilterButton,
    FilterButton,
    FadeInGroup,
    DismissibleBadge,
    TableLink,
    FadeIn,
    SonarMenuItem,
    VerticalDotMenu,
    XCircleIcon,
    SearchIcon,
    Input,
    SortDescendingIcon,
    SortAscendingIcon,
    CollectionIcon,
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
    payments: Object,
    user: Object,
    school: Object,
  },

  setup (props) {
    const $http = inject('$http')
    const $route = inject('$route')
    const showFilters = ref(false)
    const selectAll = ref(false)
    const promptExport = ref(false)
    const currentPayment = ref({})
    const { displayLongGrade } = displaysGrades()
    const { can } = checksPermissions(props.permissions)
    const { filters, applyFilters, resetFilters, sortColumn } = handlesFilters(
      {
        s: '',
        perPage: 25,
        page: 1,
        orderBy: 'paid_at',
        orderDir: 'asc',
        start_amount: null,
        end_amount: null,
        start_date: null,
        end_date: null,
        grades: [],
      },
      '/payments',
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
        router.post($route('student-selection.store'), filters.value)
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
      currentPayment,
      promptExport,
    }
  }
})
</script>
