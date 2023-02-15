<template>
  <Authenticated>
    <pre>{{ permissions }}</pre>
  </Authenticated>
</template>

<script>
import { defineComponent } from 'vue'
import Authenticated from '@/layouts/Authenticated.vue'

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
