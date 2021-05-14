<template>
  <Authenticated>
    <InvoiceForm
      :student="student"
    />
  </Authenticated>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '../../layouts/Authenticated'
import InvoiceForm from './Form'

export default defineComponent({
  components: {
    Authenticated,
    InvoiceForm,
  },

  props: {
    student: {
      type: Object,
      default: () => ({})
    }
  },

  setup (props) {

  }
})
</script>
