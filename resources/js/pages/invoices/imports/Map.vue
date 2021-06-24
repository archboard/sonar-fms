<template>
  <Authenticated>
    <template v-slot:actions>
      <Button component="inertia-link" :href="$route('invoices.imports.edit', invoiceImport)">
        {{ __('Edit') }}
      </Button>
    </template>

    <pre>{{ headers }}</pre>
    <pre>{{ invoiceImport }}</pre>
  </Authenticated>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '@/layouts/Authenticated'
import Button from '@/components/Button'

export default defineComponent({
  components: {
    Button,
    Authenticated,
  },

  props: {
    invoiceImport: Object,
    headers: Array,
  },

  setup (props) {

  }
})
</script>
