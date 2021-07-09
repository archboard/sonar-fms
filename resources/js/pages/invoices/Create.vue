<template>
  <Authenticated>
    <template v-slot:actions>
      <Button size="sm" @click.prevent="showTemplates = true">
        {{ __('View templates') }}
      </Button>
    </template>

    <InvoiceForm
      :student="student"
      :invoice-template="invoiceTemplate"
      v-model:invoice-form="form"
    />
  </Authenticated>

  <InvoiceTemplatesModal
    v-if="showTemplates"
    @use="useTemplate"
    @close="showTemplates = false"
    :invoice="form"
  />
</template>

<script>
import { defineComponent, ref } from 'vue'
import Authenticated from '../../layouts/Authenticated'
import InvoiceForm from './Form'
import InvoiceTemplatesModal from '../../components/modals/InvoiceTemplatesModal'
import Button from '../../components/Button'

export default defineComponent({
  components: {
    Button,
    InvoiceTemplatesModal,
    Authenticated,
    InvoiceForm,
  },

  props: {
    student: {
      type: Object,
      default: () => ({})
    }
  },

  setup (props) {
    const showTemplates = ref(false)
    const invoiceTemplate = ref({})
    const useTemplate = template => {
      invoiceTemplate.value = template.template
    }
    const form = ref({})

    return {
      invoiceTemplate,
      showTemplates,
      useTemplate,
      form,
    }
  }
})
</script>
