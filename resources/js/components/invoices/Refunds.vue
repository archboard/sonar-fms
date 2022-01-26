<template>
  <section v-if="can('refunds.viewAny')">
    <div class="divide-y divide-gray-300 dark:divide-gray-600">
      <div class="pb-4">
        <h2 class="text-lg font-medium">{{ __('Refunds') }}</h2>
      </div>
      <div class="pt-6">
        <Loader v-if="loading" />
        <div v-else>
          <Table v-if="refunds.length > 0">
            <Thead>
              <tr>
                <Th>{{ __('Date') }}</Th>
                <Th class="text-right">{{ __('Amount') }}</Th>
                <Th>{{ __('Recorded by') }}</Th>
                <Th></Th>
              </tr>
            </Thead>
            <Tbody>
              <tr v-for="refund in refunds" :key="refund.id">
                <Td>{{ refund.refunded_at_formatted || refund.created_at }}</Td>
                <Td class="text-right">
                  {{ refund.amount_formatted }}
                </Td>
                <Td>{{ refund.user.full_name }}</Td>
                <Td class="text-right">
                  <VerticalDotMenu>
                    <RefundActionItems
                      :refund="refund"
                      @details="currentRefund = refund"
                    />
                  </VerticalDotMenu>
                </Td>
              </tr>
            </Tbody>
          </Table>
          <p v-else class="text-sm">{{ __('No refunds have been recorded yet.') }} <Link :href="`/invoices/${invoice.uuid}/refunds/create`">{{ __('Record a refund') }}</Link>.</p>
        </div>
      </div>
    </div>

    <RefundDetailsModal
      v-if="currentRefund.id"
      @close="currentRefund = {}"
      :refund="currentRefund"
      admin
    />
  </section>
</template>

<script>
import { defineComponent, ref, inject } from 'vue'
import VerticalDotMenu from '@/components/dropdown/VerticalDotMenu'
import RefundActionItems from '@/components/RefundActionItems'
import tables from '@/components/tables'
import RefundDetailsModal from '@/components/modals/RefundDetailsModal'
import Loader from '@/components/Loader'
import checksPermissions from '@/composition/checksPermissions'

export default defineComponent({
  components: {
    Loader,
    RefundDetailsModal,
    RefundActionItems,
    VerticalDotMenu,
    ...tables,
  },
  props: {
    invoice: {
      type: Object,
      required: true,
    }
  },

  setup ({ invoice }) {
    const $http = inject('$http')
    const loading = ref(true)
    const currentRefund = ref({})
    const refunds = ref([])
    const relatedPayments = ref([])
    const { can } = checksPermissions()

    if (can('refunds.viewAny')) {
      $http.get(`/invoices/${invoice.uuid}/refunds`)
        .then(({ data }) => {
          refunds.value = data
          loading.value = false
        })

      // if (invoice.is_parent) {
      //   $http.get(`/invoices/${invoice.uuid}/refunds/related`)
      //     .then(({ data }) => {
      //       relatedPayments.value = data
      //     })
      // }
    }

    return {
      currentRefund,
      refunds,
      relatedPayments,
      loading,
      can,
    }
  }
})
</script>
