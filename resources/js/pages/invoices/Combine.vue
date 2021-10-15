<template>
  <Authenticated>
    <form @submit.prevent="combine">
      <FormMultipartWrapper>
        <div class="text-gray-700 dark:text-gray-100 mb-8">
          <p class="mb-4">{{ __('Combining invoices allows you to join multiple invoices into a single central invoice, while maintaining all the data and student association of the subsidiary invoices. Payments will be distributed among the subsidiary invoices, which is configurable when recording the payment.') }}</p>
          <p class="mb-4">{{ __('') }}</p>
          <h2 class="pt-2 mb-4 font-bold text-lg">{{ __('When do I combine invoices?') }}</h2>
          <p class="mb-4">{{ __("Sometimes it's easier for guardians of multiple children to receive a single invoice rather than a single invoice for each child. If the invoice will be paid by another third party, they may prefer to receive a single invoice summary of all the invoices that they will pay.") }}</p>
        </div>

        <div class="pt-8 mb-6">
          <CardSectionHeader>{{ __('Invoices') }}</CardSectionHeader>
          <HelpText>{{ __('These are the invoices that will be combined. Invoices that are void will not be included.') }}</HelpText>

          <Table class="mt-8">
            <Thead>
              <tr>
                <Th>{{ __('Title') }}</Th>
                <Th>{{ __('Student') }}</Th>
                <Th class="text-right">{{ __('Total due') }}</Th>
                <Th class="text-right">{{ __('Remaining balance') }}</Th>
                <Th></Th>
              </tr>
            </Thead>
            <Tbody>
              <tr v-for="invoice in selection" :key="invoice.uuid">
                <Td>
                  <TableLink :href="`/invoices/${invoice.uuid}`">{{ invoice.title }}</TableLink> <SolidBadge v-if="invoice.is_void" color="red">{{ __('Void') }}</SolidBadge>
                </Td>
                <Td>
                  <TableLink :href="`/students/${invoice.student.id}`">{{ invoice.student.full_name }}</TableLink></Td>
                <Td class="text-right">{{ invoice.amount_due_formatted }}</Td>
                <Td class="text-right">{{ invoice.remaining_balance_formatted }}</Td>
                <Td class="text-right">
                  <BaseButton type="button" @click.prevent="removeInvoice(invoice)">
                    <TrashIcon class="w-4 h-4 text-red-500 dark:text-red-400" />
                  </BaseButton>
                </Td>
              </tr>
            </Tbody>
          </Table>
        </div>

        <div class="pt-8">
          <CardSectionHeader>{{ __('Assigned users') }}</CardSectionHeader>
          <HelpText>
            {{ __("When you combine invoices, the original users will not be able to view the individual invoices. Only the users selected below will be able to view and interact with the combined invoice.") }}
          </HelpText>

          <div class="mt-4 space-y-1">
            <div
              v-for="user in suggestedUsers"
              :key="user.id"
            >
              <CheckboxWrapper>
                <Checkbox v-model:checked="form.users" :value="user.id" />
                <CheckboxText>
                  {{ user.full_name }} <span class="text-gray-500">({{ user.email }})</span>
                </CheckboxText>
              </CheckboxWrapper>
            </div>
          </div>
        </div>

        <div class="pt-8">
          <InvoiceDetailsForm v-model="form" />
        </div>

        <div class="mt-8 p-4 border-t border-gray-400 bg-gray-200 dark:bg-gray-700 dark:border-gray-300 rounded-b-md space-x-3">
          <Button type="submit">
            {{ __('Combine invoices') }}
          </Button>
        </div>
      </FormMultipartWrapper>
    </form>
  </Authenticated>
</template>

<script>
import { defineComponent, inject, ref } from 'vue'
import Authenticated from '@/layouts/Authenticated'
import PageProps from '@/mixins/PageProps'
import { useForm } from '@inertiajs/inertia-vue3'
import FormMultipartWrapper from '@/components/forms/FormMultipartWrapper'
import CardSectionHeader from '@/components/CardSectionHeader'
import HelpText from '@/components/HelpText'
import Table from '@/components/tables/Table'
import Thead from '@/components/tables/Thead'
import Th from '@/components/tables/Th'
import Tbody from '@/components/tables/Tbody'
import Td from '@/components/tables/Td'
import SolidBadge from '@/components/SolidBadge'
import TableLink from '@/components/tables/TableLink'
import InvoiceDetailsForm from '@/components/forms/invoices/InvoiceDetailsForm'
import Button from '@/components/Button'
import { TrashIcon } from '@heroicons/vue/outline'
import BaseButton from '@/components/BaseButton'
import { Inertia } from '@inertiajs/inertia'
import CheckboxWrapper from '@/components/forms/CheckboxWrapper'
import Checkbox from '@/components/forms/Checkbox'
import CheckboxText from '@/components/forms/CheckboxText'

export default defineComponent({
  mixins: [PageProps],
  components: {
    CheckboxText,
    Checkbox,
    CheckboxWrapper,
    BaseButton,
    Button,
    InvoiceDetailsForm,
    TableLink,
    SolidBadge,
    Td,
    Tbody,
    Th,
    Thead,
    Table,
    HelpText,
    CardSectionHeader,
    FormMultipartWrapper,
    Authenticated,
    TrashIcon,
  },
  props: {
    selection: Array,
    suggestedUsers: Array,
  },

  setup (props) {
    const $route = inject('$route')
    const form = useForm({
      users: [],
      title: null,
      description: null,
      term_id: null,
      invoice_date: new Date(),
      available_at: null,
      due_at: null,
      notify: false,
    })
    const removeInvoice = invoice => {
      Inertia.delete($route('invoice-selection.update', invoice.uuid), {
        preserveScroll: true,
      })
    }
    const combine = () => {}

    return {
      form,
      combine,
      removeInvoice,
    }
  }
})
</script>
