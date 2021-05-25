<template>
  <Slideout
    @close="$emit('close')"
    @action="invoiceForm.saveInvoice"
    :auto-close="false"
    :processing="invoiceForm.form.processing"
  >
    <template v-slot:header>
      <div class="space-y-1">
        <CardHeader v-if="isNew">
          {{ __('New invoice for :name', { name: student.full_name }) }}
        </CardHeader>
        <CardHeader v-else>
          {{ __('Update invoice for :name', { name: student.full_name }) }}
        </CardHeader>
        <HelpText v-if="isNew">
          {{ __('Create a new invoice by providing the following details.') }}
        </HelpText>
        <HelpText v-else>
          {{ __('Modify invoice details.') }}
        </HelpText>
      </div>
    </template>

    <InvoiceForm
      ref="invoiceForm"
      :student="student"
      :invoice="invoice"
    />
  </Slideout>
</template>

<script>
import { ref } from 'vue'
import Slideout from '../Slideout'
import CardHeader from '../CardHeader'
import HelpText from '../HelpText'
import InvoiceForm from '../../pages/invoices/Form'

export default {
  components: {
    InvoiceForm,
    HelpText,
    CardHeader,
    Slideout,
  },
  props: {
    student: Object,
    invoice: {
      type: Object,
      default: () => ({})
    }
  },
  emits: ['close'],

  setup (props) {
    const invoiceForm = ref({ form: {} })
    const isNew = ref(!props.invoice.id)

    return {
      invoiceForm,
      isNew,
    }
  },
}
</script>
