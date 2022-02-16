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
import Modal from '@/components/Modal'
import Spinner from '@/components/icons/spinner'
import Link from '@/components/Link'
import PaymentDetails from '@/components/PaymentDetails'

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
      window.open(`/payments/${props.payment.id}/receipt`, '_blank')
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
