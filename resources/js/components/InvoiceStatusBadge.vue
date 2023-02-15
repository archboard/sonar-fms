<template>
  <SolidBadge v-if="invoice.status_label" :color="invoice.status_color" :size="size">
    {{ invoice.status_label }}
  </SolidBadge>
</template>

<script setup>
import SolidBadge from '@/components/SolidBadge.vue'

defineProps({
  size: {
    type: String,
    default: 'base',
  },
  invoice: {
    type: Object,
    default: () => ({})
  }
})
</script>
