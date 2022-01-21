<template>
  <div class="p-1">
    <SonarMenuItem @click.prevent="$emit('details', refund)">
      {{ __('Details') }}
    </SonarMenuItem>
    <SonarMenuItem is="a" target="_blank" :href="`/refunds/${refund.id}/receipt`">
      {{ __('Receipt') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('invoices.viewAny') && refund.invoice" is="inertia-link" :href="`/invoices/${refund.invoice.uuid}`">
      {{ __('View invoice') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('students.viewAny') && refund.invoice" is="inertia-link" :href="`/students/${refund.invoice.student_uuid}`">
      {{ __('View student') }}
    </SonarMenuItem>
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import SonarMenuItem from '@/components/forms/SonarMenuItem'
import checksPermissions from '@/composition/checksPermissions'

export default defineComponent({
  components: {
    SonarMenuItem,
  },
  emits: ['details'],
  props: {
    refund: Object,
  },

  setup () {
    const { can } = checksPermissions()

    return {
      can,
    }
  }
})
</script>
