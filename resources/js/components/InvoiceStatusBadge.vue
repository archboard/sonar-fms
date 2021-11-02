<template>
  <SolidBadge v-if="invoice.status_label" :color="invoice.status_color" :size="size">
    {{ invoice.status_label }}
  </SolidBadge>
</template>

<script>
import { computed, defineComponent, inject, ref } from 'vue'
import SolidBadge from './SolidBadge'

export default defineComponent({
  components: {
    SolidBadge
  },
  props: {
    size: {
      type: String,
      default: 'base',
    },
    invoice: {
      type: Object,
      default: () => ({})
    }
  },
})
</script>
