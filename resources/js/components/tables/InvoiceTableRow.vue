<template>
  <tr>
    <slot name="prepend" />
    <Td class="">
      <span class="whitespace-nowrap">{{ invoice.invoice_number }}</span>
    </Td>
    <Td :lighter="false">
      <div class="flex items-center space-x-1">
        <InertiaLink :href="$route('invoices.show', invoice)" class="hover:underline">
          {{ invoice.title }}
        </InertiaLink>
        <InvoiceStatusBadge :invoice="invoice" size="sm" />
      </div>
    </Td>
    <Td :lighter="false" v-if="can('students.viewAny') && showStudent">
      <InertiaLink v-if="invoice.student" :href="$route('students.show', invoice.student)" class="hover:underline">
        {{ invoice.student.full_name }}
      </InertiaLink>
    </Td>
<!--    <Td class="text-right">{{ invoice.amount_due_formatted }}</Td>-->
    <Td class="text-right">{{ invoice.remaining_balance_formatted }}</Td>
    <Td class="text-right space-x-2 pl-0">
      <VerticalDotMenu>
        <InvoiceActionItems
          :invoice="invoice"
          :show-view="true"
          @edit-status="$emit('editStatus', invoice)"
          @convert-to-template="$emit('convertToTemplate', invoice)"
        />
      </VerticalDotMenu>
    </Td>
  </tr>
</template>

<script>
import { defineComponent } from 'vue'
import InvoiceStatusBadge from '@/components/InvoiceStatusBadge'
import Td from '@/components/tables/Td'
import VerticalDotMenu from '@/components/dropdown/VerticalDotMenu'
import InvoiceActionItems from '@/components/dropdown/InvoiceActionItems'
import checksPermissions from '@/composition/checksPermissions'

export default defineComponent({
  components: {
    InvoiceActionItems,
    VerticalDotMenu,
    Td,
    InvoiceStatusBadge,
  },
  props: {
    invoice: Object,
    showStudent: {
      type: Boolean,
      default: true,
    }
  },
  emits: ['editStatus', 'convertToTemplate'],

  setup () {
    const { can } = checksPermissions()

    return {
      can,
    }
  },
})
</script>
