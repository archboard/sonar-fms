<template>
  <section v-if="can('refunds.view')">
    <div class="divide-y divide-gray-300 dark:divide-gray-600">
      <div class="pb-4 flex justify-between">
        <h2 class="text-lg font-medium">{{ __('Refunds') }}</h2>
        <div v-if="can('refunds.create')">
          <Button :href="`/invoices/${invoice.uuid}/refunds/create`" component="InertiaLink" size="sm">{{ __('Record refund') }}</Button>
        </div>
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
          <p v-else class="text-sm">{{ __('No refunds have been recorded yet.') }} <span v-if="can('refunds.create')"><Link :href="`/invoices/${invoice.uuid}/refunds/create`">{{ __('Record a refund') }}</Link>.</span></p>
        </div>
      </div>
    </div>

    <div v-if="relatedRefunds.length > 0" class="mt-8 xl:mt-10 divide-y divide-gray-300 dark:divide-gray-600">
      <div class="pb-4">
        <h2 class="text-lg font-medium">{{ __('Related refunds') }}</h2>
        <HelpText>{{ __('These are refunds of payments made to the combined invoices individually.') }}</HelpText>
      </div>
      <div class="pt-6">
        <Table>
          <Thead>
            <tr>
              <Th>{{ __('Invoice') }}</Th>
              <Th>{{ __('Date') }}</Th>
              <Th class="text-right">{{ __('Amount') }}</Th>
              <Th>{{ __('Recorded by') }}</Th>
              <Th></Th>
            </tr>
          </Thead>
          <Tbody>
            <tr v-for="refund in relatedRefunds" :key="refund.id">
              <Td>
                <Link :href="`/invoices/${refund.invoice.uuid}`">{{ refund.invoice.invoice_number }}</Link>
              </Td>
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
import VerticalDotMenu from '@/components/dropdown/VerticalDotMenu.vue'
import RefundActionItems from '@/components/RefundActionItems.vue'
import tables from '@/components/tables.vue'
import RefundDetailsModal from '@/components/modals/RefundDetailsModal.vue'
import Loader from '@/components/Loader.vue'
import checksPermissions from '@/composition/checksPermissions.js'
import Link from '@/components/Link.vue'
import HelpText from '@/components/HelpText.vue'
import Button from '@/components/Button.vue'

export default defineComponent({
  components: {
    Button,
    HelpText,
    Loader,
    RefundDetailsModal,
    RefundActionItems,
    VerticalDotMenu,
    ...tables,
    Link,
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
    const relatedRefunds = ref([])
    const { can } = checksPermissions()

    if (can('refunds.view')) {
      $http.get(`/invoices/${invoice.uuid}/refunds`)
        .then(({ data }) => {
          refunds.value = data
          loading.value = false
        })

      if (invoice.is_parent) {
        $http.get(`/invoices/${invoice.uuid}/related-refunds`)
          .then(({ data }) => {
            relatedRefunds.value = data
          })
      }
    }

    return {
      currentRefund,
      refunds,
      relatedRefunds,
      loading,
      can,
    }
  }
})
</script>
