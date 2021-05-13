<template>
  <section>
    <Loader v-if="loading" />
    <div v-show="!loading">
      <Table>
        <Thead>
          <tr>
            <Th>#</Th>
            <Th>{{ __('Title') }}</Th>
            <Th>{{ __('Status') }}</Th>
            <Th class="text-right">{{ __('Total') }}</Th>
            <Th class="text-right">{{ __('Remaining') }}</Th>
            <th/>
          </tr>
        </Thead>
        <Tbody>
          <tr
            v-for="invoice in invoices.data"
            :key="invoice.id"
          >
            <Td>
              {{ invoice.id }}
            </Td>
            <Td :lighter="false" class="whitespace-nowrap">
              {{ invoice.title }}
            </Td>
            <Td>
              <InvoiceStatusBadge :invoice="invoice" />
            </Td>
            <Td class="text-right">
              {{ displayCurrency(invoice.amount_due) }}
            </Td>
            <Td class="text-right">
              {{ displayCurrency(invoice.remaining_balance) }}
            </Td>
            <Td>
              <div class="flex items-center justify-end space-x-2">
                <Link :href="$route('students.invoices.show', [student, invoice])" class="text-sm">
                  {{ __('View') }}
                </Link>
                <Link is="button" class="text-sm" @click.prevent="$emit('edit', invoice)">
                  {{ __('Edit') }}
                </Link>
  <!--              <VerticalDotMenu>-->
  <!--                <div class="px-1 py-1" v-if="canAny('viewAny', 'edit permissions')">-->
  <!--                  <SonarMenuItem v-if="can('viewAny')">-->
  <!--                    Edit-->
  <!--                  </SonarMenuItem>-->
  <!--                  <SonarMenuItem v-if="can('edit permissions')">-->
  <!--                    Permissions-->
  <!--                  </SonarMenuItem>-->
  <!--                </div>-->
  <!--                <div class="px-1 py-1">-->
  <!--                  <SonarMenuItem>-->
  <!--                    Archive-->
  <!--                  </SonarMenuItem>-->
  <!--                  <SonarMenuItem>-->
  <!--                    Move-->
  <!--                  </SonarMenuItem>-->
  <!--                </div>-->

  <!--                <div class="px-1 py-1" v-if="can('delete')">-->
  <!--                  <SonarMenuItem v-slot="{ active }">-->
  <!--                    <span :class="[active ? '' : 'text-red-500 dark:text-red-400']">Delete</span>-->
  <!--                  </SonarMenuItem>-->
  <!--                </div>-->
  <!--              </VerticalDotMenu>-->
              </div>
            </Td>
          </tr>
          <tr v-if="invoices.data.length === 0">
            <Td colspan="5" class="text-center">
              {{ __('No invoices exist for this student.') }}
            </Td>
          </tr>
        </Tbody>
      </Table>

      <div :class="{ 'py-6': invoices.data.length > 0 }">
        <Pagination
          :meta="invoices.meta"
          @paged="paged"
        />
      </div>
    </div>
  </section>
</template>

<script>
import { defineComponent, inject, reactive, ref, watch } from 'vue'
import TableComponents from './tables'
import displaysCurrency from '../composition/displaysCurrency'
import Pagination from './tables/AjaxPagination'
import InvoiceStatusBadge from './InvoiceStatusBadge'
import VerticalDotMenu from './dropdown/VerticalDotMenu'
import SonarMenuItem from './forms/SonarMenuItem'
import checksPermissions from '../composition/checksPermissions'
import Link from './Link'
import Loader from './Loader'

export default defineComponent({
  components: {
    Loader,
    VerticalDotMenu,
    InvoiceStatusBadge,
    ...TableComponents,
    Pagination,
    SonarMenuItem,
    Link,
  },
  emit: ['edit'],
  props: {
    student: Object,
    permissions: Object,
  },

  setup (props) {
    const $route = inject('$route')
    const $http = inject('$http')
    const loading = ref(true)
    const { can, canAny } = checksPermissions(props.permissions || {})
    const filters = reactive({
      page: 1,
      random: 'hello',
    })
    const { displayCurrency } = displaysCurrency()
    const invoices = ref({
      data: [],
      meta: {},
      links: {}
    })
    const fetchInvoices = () => {
      loading.value = true
      const params = {
        ...filters,
        student: props.student,
      }
      const route = $route('students.invoices.index', params)

      $http.get(route).then(({ data }) => {
        invoices.value = data
        loading.value = false
      })
    }
    const paged = page => {
      filters.page = page
    }

    watch(filters, () => {
      fetchInvoices()
    })

    fetchInvoices()

    return {
      can,
      canAny,
      invoices,
      displayCurrency,
      filters,
      paged,
      fetchInvoices,
      loading,
    }
  }
})
</script>
