import { nanoid } from 'nanoid'
import invoiceImportMapField from '@/composition/invoiceImportMapField'

export default () => {
  const { addMapFieldValue } = invoiceImportMapField()

  const generateSchedule = () => ({
    id: nanoid(),
    terms: [],
  })
  const generateTerm = () => ({
    id: nanoid(),
    use_amount: true,
    amount: addMapFieldValue(),
    percentage: addMapFieldValue(),
    due_at: addMapFieldValue(),
  })
  const addPaymentTerm = schedule => {
    schedule.terms.push(generateTerm())
  }

  return {
    generateTerm,
    addPaymentTerm,
    generateSchedule,
  }
}
