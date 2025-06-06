<template>
  <Authenticated>
    <template v-slot:actions>
      <Button size="sm" @click.prevent="showTemplates = true">
        {{ __('View templates') }}
      </Button>
    </template>

    <Alert v-if="duplicating" class="mb-6" level="warning">
      {{ __('This will create a new invoice.') }}
    </Alert>
    <Alert v-if="invoice.uuid" class="mb-6" level="warning">
      {{ __('You are editing a draft invoice.') }}
    </Alert>

    <InvoiceForm
      :invoice-template="invoiceTemplate"
      v-model:invoice-form="form"
      :method="method"
      :endpoint="endpoint"
      :allow-student-editing="allowStudentEditing"
      :invoice="invoice"
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
import Authenticated from '@/layouts/Authenticated.vue'
import InvoiceForm from '@/pages/invoices/Form.vue'
import InvoiceTemplatesModal from '@/components/modals/InvoiceTemplatesModal.vue'
import Button from '@/components/Button.vue'
import PageProps from '@/mixins/PageProps'
import Alert from '@/components/Alert.vue'

export default defineComponent({
  mixins: [PageProps],
  components: {
    Alert,
    Button,
    InvoiceTemplatesModal,
    Authenticated,
    InvoiceForm,
  },

  props: {
    endpoint: {
      type: String,
      required: true,
    },
    method: {
      type: String,
      required: true,
    },
    students: {
      type: Array,
      default: () => ([])
    },
    defaultTemplate: {
      type: Object,
      default: () => ({})
    },
    // Used when editing
    invoice: {
      type: Object,
      default: () => ({})
    },
    duplicating: {
      type: Boolean,
      default: false,
    },
    allowStudentEditing: {
      type: Boolean,
      default: true,
    },
  },

  setup (props) {
    const showTemplates = ref(false)
    const invoiceTemplate = ref({ ...props.defaultTemplate })
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
