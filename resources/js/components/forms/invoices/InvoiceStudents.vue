<template>
  <div class="mb-6">
    <CardSectionHeader>
      {{ __('Students') }}
    </CardSectionHeader>
    <HelpText class="text-sm mt-1">
      {{ __('Add or remove the students to which this invoice will be given.') }}
    </HelpText>
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import invoiceFormComponent from '@/composition/invoiceFormComponent'
import InvoiceFormCollection from '@/mixins/InvoiceFormCollection'

export default defineComponent({
  mixins: [InvoiceFormCollection],
  components: {
  },
  props: {
    total: Number,
  },

  setup (props, context) {
    const { localValue } = invoiceFormComponent(props, context)

    // If the value is not empty at this point,
    // fetch all the students by their id

    return {
      localValue,
    }
  }
})
</script>
