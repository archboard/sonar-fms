<template>
  <Modal
    @close="$emit('close')"
    :headline="__('Refund details')"
    :action-text="__('View receipt')"
    @action="viewReceipt"
    size="xl"
  >
    <RefundDetails v-if="localRefund.id" :refund="localRefund" />
    <Loader v-else class="w-12 mx-auto" />
  </Modal>
</template>

<script>
import { defineComponent, inject, ref } from 'vue'
import Modal from '@/components/Modal'
import Link from '@/components/Link'
import RefundDetails from '@/components/RefundDetails'
import Loader from '@/components/Loader'

export default defineComponent({
  components: {
    Loader,
    RefundDetails,
    Link,
    Modal,
  },
  emits: ['close'],
  props: {
    refund: Object,
  },

  setup ({ refund }) {
    const $http = inject('$http')
    const localRefund = ref({})
    const viewReceipt = () => {
      window.open(`/refunds/${refund}/receipt`, '_blank')
    }

    $http.get(`/invoices/${refund.invoice_uuid}/refunds/${refund.id}`).then(({ data }) => {
      localRefund.value = data
    })

    return {
      localRefund,
      viewReceipt,
    }
  }
})
</script>
