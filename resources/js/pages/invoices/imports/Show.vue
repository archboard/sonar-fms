<template>
  <Authenticated>
    <template v-slot:actions>
      <Dropdown
        :menu-items="[
          {
            label: __('Edit'),
            route: $route('invoices.imports.edit', invoiceImport),
          },
          {
            label: __('Map'),
            route: $route('invoices.imports.map', invoiceImport),
          },
        ]"
      >
        {{ __('Actions') }}
      </Dropdown>
    </template>

    <Stats
      :stats="[
        {
          label: __('Total records'),
          value: invoiceImport.total_records,
        },
        {
          label: __('Imported records'),
          value: invoiceImport.imported_records,
        },
        {
          label: __('Failed records'),
          value: invoiceImport.failed_records,
        },
      ]"
    />

    <pre>{{ invoiceImport }}</pre>
  </Authenticated>
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '@/layouts/Authenticated'
import PageProps from '@/mixins/PageProps'
import Dropdown from '@/components/forms/Dropdown'
import Stats from '@/components/Stats'

export default defineComponent({
  mixins: [PageProps],
  components: {
    Stats,
    Authenticated,
    Dropdown,
  },
  props: {
    invoiceImport: Object,
  },

  setup () {

  }
})
</script>
