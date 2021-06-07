import { nanoid } from 'nanoid'
import fetchesFees from './fetchesFees'
import { computed } from 'vue'

export default (form) => {
  const { fees } = fetchesFees()

  const addInvoiceLineItem = () => {
    form.items.push({
      id: nanoid(),
      fee_id: null,
      name: null,
      amount_per_unit: null,
      quantity: 1,
    })
  }
  const syncItemWithFee = item => {
    const fee = fees.value.find(f => f.id === item.fee_id)

    if (fee) {
      item.name = fee.name
      item.amount_per_unit = fee.amount
    }
  }
  const feeSelected = item => {
    syncItemWithFee(item)
  }
  const getItemTotal = item => Number(item.amount_per_unit) * Number(item.quantity)
  const getItemsTotal = items => {
    return items.reduce((total, item) => {
      return total + getItemTotal(item)
    }, 0)
  }
  const subtotal = computed(() => getItemsTotal(form.items))

  return {
    fees,
    subtotal,
    getItemsTotal,
    getItemTotal,
    addInvoiceLineItem,
    syncItemWithFee,
    feeSelected,
  }
}
