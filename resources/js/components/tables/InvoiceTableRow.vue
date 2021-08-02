<template>
  <tr>
    <Td class="pr-0">
      <div class="flex items-center">
        <span class="mr-2">{{ invoice.id }}</span>
        <InvoiceStatusBadge :invoice="invoice" size="sm" />
      </div>
    </Td>
    <Td :lighter="false">
      <InertiaLink :href="$route('invoices.show', invoice)" class="hover:underline">
        {{ invoice.title }}
      </InertiaLink>
    </Td>
    <Td class="text-right">{{ invoice.amount_due_formatted }}</Td>
    <Td class="text-right">{{ invoice.remaining_balance_formatted }}</Td>
    <Td class="text-right space-x-2">
      <VerticalDotMenu>
        <InvoiceActionItems
          :invoice="invoice"
          :show-view="true"
          @edit-status="editInvoice = invoice"
          @convert-to-template="convertInvoice = invoice"
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

export default defineComponent({
  components: {
    InvoiceActionItems,
    VerticalDotMenu,
    Td,
    InvoiceStatusBadge,
  },
  props: {
    invoice: Object,
  },
})
</script>
