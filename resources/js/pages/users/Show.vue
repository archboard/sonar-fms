<template>
  <Authenticated>
    <pre>{{ permissions }}</pre>
  </Authenticated>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '../../layouts/Authenticated'

export default defineComponent({
  components: {
    Authenticated
  },

  props: {
    user: Object,
    permissions: Array,
  },

  setup () {

  }
})
</script>
