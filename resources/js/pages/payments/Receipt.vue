<template>
  <Empty class="max-w-xl mx-auto py-10">
    <ModalHeadline>{{ title }}</ModalHeadline>
    <PaymentDetails :payment="payment" />
  </Empty>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Empty from '@/layouts/Empty.vue'
import PageProps from '@/mixins/PageProps'
import ModalHeadline from '@/components/modals/ModalHeadline.vue'
import PaymentDetails from '@/components/PaymentDetails.vue'

export default defineComponent({
  mixins: [PageProps],
  components: {
    PaymentDetails,
    ModalHeadline,
    Empty,
  },
  props: {
    payment: Object,
  },

  setup () {
    document.documentElement.classList.remove('dark')
  }
})
</script>
