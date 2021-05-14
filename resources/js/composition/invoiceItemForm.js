import { nanoid } from 'nanoid'
import fetchesFees from './fetchesFees'

export default (form) => {
  const { fees } = fetchesFees()

  const addInvoiceLineItem = () => {
    form.items.push({
      id: nanoid(),
      fee_id: null,
      sync_with_fee: false,
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
    item.sync_with_fee = false
  }
  const itemSyncChanged = item => {
    if (item.sync_with_fee) {
      syncItemWithFee(item)
    }
  }

  return {
    fees,
    addInvoiceLineItem,
    syncItemWithFee,
    feeSelected,
    itemSyncChanged,
  }
}
