import { nanoid } from 'nanoid'
import fetchesFees from './fetchesFees'
// import { computed } from 'vue'
import invoiceImportMapField from '@/composition/invoiceImportMapField'

export default (form) => {
  const { fees } = fetchesFees()
  const { addMapFieldValue } = invoiceImportMapField()

  const addInvoiceLineItem = () => {
    form.items.push({
      id: nanoid(),
      fee_id: addMapFieldValue(),
      name: addMapFieldValue(),
      amount_per_unit: addMapFieldValue(),
      quantity: addMapFieldValue(1),
    })
  }
  const syncItemWithFee = item => {
    const fee = fees.value.find(f => f.id === item.fee_id)

    if (fee) {
      item.name.value = fee.name
      item.amount_per_unit.value = fee.amount
    }
  }
  const feeSelected = item => {
    syncItemWithFee(item)
  }
  // const getItemTotal = item => Number(item.amount_per_unit) * Number(item.quantity)
  // const getItemsTotal = items => {
  //   return items.reduce((total, item) => {
  //     return total + getItemTotal(item)
  //   }, 0)
  // }
  // const subtotal = computed(() => getItemsTotal(form.items))

  return {
    fees,
    // subtotal,
    // getItemsTotal,
    // getItemTotal,
    addInvoiceLineItem,
    syncItemWithFee,
    feeSelected,
  }
}
