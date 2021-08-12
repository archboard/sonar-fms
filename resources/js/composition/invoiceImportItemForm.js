import { nanoid } from 'nanoid'
import fetchesFees from './fetchesFees'
import invoiceImportMapField from '@/composition/invoiceImportMapField'

export default () => {
  const { fees } = fetchesFees()
  const { addMapFieldValue } = invoiceImportMapField()

  const makeInvoiceLineItem = () => {
    return {
      id: nanoid(),
      fee_id: addMapFieldValue(),
      name: addMapFieldValue(),
      amount_per_unit: addMapFieldValue(),
      quantity: addMapFieldValue(1),
    }
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

  return {
    fees,
    makeInvoiceLineItem,
    syncItemWithFee,
    feeSelected,
  }
}
