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
import Modal from '@/components/Modal.vue'
import Link from '@/components/Link.vue'
import RefundDetails from '@/components/RefundDetails.vue'
import Loader from '@/components/Loader.vue'

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
    admin: {
      type: Boolean,
      default: () => false,
    }
  },

  setup ({ refund, admin }) {
    const $http = inject('$http')
    const localRefund = ref({})
    const viewReceipt = () => {
      window.open(`/refunds/${refund}/receipt`, '_blank')
    }
    let endpoint = `/invoices/${refund.invoice_uuid}/refunds/${refund.id}`

    if (admin) {
      endpoint += `?admin=1`
    }

    $http.get(endpoint).then(({ data }) => {
      localRefund.value = data
    })

    return {
      localRefund,
      viewReceipt,
    }
  }
})
</script>
