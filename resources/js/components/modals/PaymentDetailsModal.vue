<template>
  <Modal
    @close="$emit('close')"
    :headline="__('Payment details')"
    :action-text="__('View receipt')"
    @action="viewReceipt"
    size="2xl"
  >
    <PaymentDetails v-if="localPayment.id" :payment="localPayment" />
    <Spinner v-else class="w-12 mx-auto" />
  </Modal>
</template>

<script>
import { defineComponent, inject, ref } from 'vue'
import Modal from '@/components/Modal.vue'
import Spinner from '@/components/icons/spinner.vue'
import Link from '@/components/Link.vue'
import PaymentDetails from '@/components/PaymentDetails.vue'

export default defineComponent({
  components: {
    PaymentDetails,
    Link,
    Spinner,
    Modal,
  },
  emits: ['close'],
  props: {
    payment: Object,
  },

  setup (props) {
    const $http = inject('$http')
    const localPayment = ref({})
    const viewReceipt = () => {
      const receipt = localPayment.value.receipts[localPayment.value.receipts.length - 1]

      if (receipt) {
        window.open(`/payments/${receipt.id}/receipt`, '_blank')
      }
    }

    $http.get(`/payments/${props.payment.id}`).then(({ data }) => {
      localPayment.value = data
    })

    return {
      localPayment,
      viewReceipt,
    }
  }
})
</script>
