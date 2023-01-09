<template>
  <div class="p-1">
    <SonarMenuItem is="InertiaLink" :href="`/students/${student.id}`">
      {{ __('View') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('invoices.create')" is="InertiaLink" :href="`/students/${student.uuid}/invoices/create`">
      {{ __('New invoice') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('students.update')" is="InertiaLink" :href="`/students/${student.uuid}/balance`" preserve-scroll method="put">
      {{ __('Refresh account balance') }}
    </SonarMenuItem>
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import SonarMenuItem from '@/components/forms/SonarMenuItem.vue'
import checksPermissions from '@/composition/checksPermissions.js'

export default defineComponent({
  components: {
    SonarMenuItem,
  },
  props: {
    student: Object,
  },

  setup () {
    const { can } = checksPermissions()

    return {
      can,
    }
  }
})
</script>
