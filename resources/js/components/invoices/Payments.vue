<template>
  <section v-if="can('payments.viewAny')">
    <div class="divide-y divide-gray-300 dark:divide-gray-600">
      <div class="pb-4">
        <h2 class="text-lg font-medium">{{ __('Payments') }}</h2>
      </div>
      <div class="pt-6">
        <Loader v-if="loading" />
        <div v-else>
          <Table v-if="payments.length > 0">
            <Thead>
              <tr>
                <Th>{{ __('Date') }}</Th>
                <Th class="text-right">{{ __('Amount') }}</Th>
                <Th>{{ __('Recorded by') }}</Th>
                <Th></Th>
              </tr>
            </Thead>
            <Tbody>
              <tr v-for="payment in payments" :key="payment.id">
                <Td>{{ payment.paid_at_formatted }}</Td>
                <Td>
                  <div class="flex items-center justify-end space-x-1">
                    <CollectionIcon v-if="payment.parent_uuid" class="h-4 w-4" />
                    <span>{{ payment.amount_formatted }}</span>
                  </div>
                </Td>
                <Td>{{ payment.recorded_by.full_name }}</Td>
                <Td class="text-right">
                  <VerticalDotMenu>
                    <PaymentActionItems
                      :payment="payment"
                      @details="currentPayment = payment"
                    />
                  </VerticalDotMenu>
                </Td>
              </tr>
            </Tbody>
          </Table>
          <p v-else class="text-sm">{{ __('No payments have been recorded yet.') }} <Link :href="`/payments/create?invoice_uuid=${invoice.uuid}`">{{ __('Record a payment') }}</Link>.</p>
        </div>
      </div>
    </div>

    <div v-if="relatedPayments.length > 0" class="mt-8 xl:mt-10 divide-y divide-gray-300 dark:divide-gray-600">
      <div class="pb-4">
        <h2 class="text-lg font-medium">{{ __('Related payments') }}</h2>
        <HelpText>{{ __('These are payments made to the combined invoices individually.') }}</HelpText>
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
            <tr v-for="payment in relatedPayments" :key="payment.id">
              <Td lighter>
                <Copy :copy-value="payment.invoice.invoice_number">{{ payment.invoice.invoice_number }}</Copy>
              </Td>
              <Td>{{ payment.paid_at_formatted }}</Td>
              <Td>
                <div class="flex items-center justify-end space-x-1">
                  <CollectionIcon v-if="payment.parent_uuid" class="h-4 w-4" />
                  <span>{{ payment.amount_formatted }}</span>
                </div>
              </Td>
              <Td>{{ payment.recorded_by.full_name }}</Td>
              <Td class="text-right">
                <VerticalDotMenu>
                  <PaymentActionItems
                    :payment="payment"
                    @details="currentPayment = payment"
                  />
                </VerticalDotMenu>
              </Td>
            </tr>
          </Tbody>
        </Table>
      </div>
    </div>

    <PaymentDetailsModal
      v-if="currentPayment.id"
      @close="currentPayment = {}"
      :payment="currentPayment"
    />
  </section>
</template>

<script>
import { defineComponent, inject, ref } from 'vue'
import { CollectionIcon } from '@heroicons/vue/outline'
import VerticalDotMenu from '@/components/dropdown/VerticalDotMenu'
import PaymentActionItems from '@/components/PaymentActionItems'
import tables from '@/components/tables'
import Link from '@/components/Link'
import Loader from '@/components/Loader'
import Copy from '@/components/Copy'
import HelpText from '@/components/HelpText'
import PaymentDetailsModal from '@/components/modals/PaymentDetailsModal'
import checksPermissions from '@/composition/checksPermissions'

export default defineComponent({
  components: {
    PaymentDetailsModal,
    HelpText,
    Copy,
    Loader,
    PaymentActionItems,
    VerticalDotMenu,
    CollectionIcon,
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
    const currentPayment = ref({})
    const payments = ref([])
    const relatedPayments = ref([])
    const { can } = checksPermissions()

    if (can('payments.viewAny')) {
      $http.get(`/invoices/${invoice.uuid}/payments`)
        .then(({ data }) => {
          payments.value = data
          loading.value = false
        })

      if (invoice.is_parent) {
        $http.get(`/invoices/${invoice.uuid}/payments/related`)
          .then(({ data }) => {
            relatedPayments.value = data
          })
      }
    }

    return {
      currentPayment,
      payments,
      relatedPayments,
      loading,
      can,
    }
  }
})
</script>
