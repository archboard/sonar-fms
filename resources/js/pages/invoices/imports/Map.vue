<template>
  <Authenticated>
    <template v-slot:actions>
      <Button component="inertia-link" :href="$route('invoices.imports.edit', invoiceImport)">
        {{ __('Edit') }}
      </Button>
    </template>

    <MapForm :headers="headers" v-model:invoice-form="form" />
    <pre>{{ form }}</pre>
  </Authenticated>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '@/layouts/Authenticated'
import Button from '@/components/Button'
import MapForm from '@/pages/invoices/imports/MapForm'

export default defineComponent({
  components: {
    MapForm,
    Button,
    Authenticated,
  },

  props: {
    invoiceImport: Object,
    headers: Array,
  },

  setup (props) {
    const form = ref({})

    return {
      form
    }
  }
})
</script>
