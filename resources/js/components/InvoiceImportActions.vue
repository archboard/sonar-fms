<template>
  <div class="p-1">
    <SonarMenuItem v-if="showView && can('view')" is="inertia-link" :href="`/invoices/imports/${invoiceImport.id}`">
      {{ __('View') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('update')" is="inertia-link" :href="`/invoices/imports/${invoiceImport.id}/edit`">
      {{ __('Edit import file') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('view')" is="a" :href="`/invoices/imports/${invoiceImport.id}/download`">
      {{ __('Download file') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('update')" is="inertia-link" :href="`/invoices/imports/${invoiceImport.id}/map`">
      {{ __('Update mapping') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="can('create')" @click.prevent="$emit('template')">
      {{ __('Save mapping as template') }}
    </SonarMenuItem>
  </div>
  <div class="p-1" v-if="invoiceImport.imported_at || invoiceImport.mapping_valid">
    <SonarMenuItem v-if="invoiceImport.mapping_valid && !invoiceImport.imported_at && can('create')" is="inertia-link" :href="`/invoices/imports/${invoiceImport.id}/preview`">
      {{ __('Preview import') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="invoiceImport.mapping_valid && !invoiceImport.imported_at && can('create')" @click.prevent="$emit('import')">
      {{ __('Import') }}
    </SonarMenuItem>
    <SonarMenuItem v-if="invoiceImport.imported_at && can('roll back')" @click.prevent="$emit('rollback')">
      {{ __('Roll back') }}
    </SonarMenuItem>
  </div>
</template>

<script>
import { defineComponent, ref } from 'vue'
import checksPermissions from '@/composition/checksPermissions'
import SonarMenuItem from '@/components/forms/SonarMenuItem'

export default defineComponent({
  components: {
    SonarMenuItem,
  },
  props: {
    invoiceImport: Object,
    showView: {
      type: Boolean,
      default: false,
    }
  },
  emits: ['import', 'rollback', 'template'],

  setup () {
    const { can } = checksPermissions()

    return {
      can,
    }
  }
})
</script>
