import { nanoid } from 'nanoid'
import invoiceImportMapField from '@/composition/invoiceImportMapField'

export default (form) => {
  const { addMapFieldValue } = invoiceImportMapField()

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
  // const removePaymentTerm = (schedule, index) => {
  //   schedule.terms.splice(index, 1)
  //
  //   if (schedule.terms.length < 2) {
  //     const scheduleIndex = form.payment_schedules.findIndex(s => s.id === schedule.id)
  //     form.payment_schedules.splice(scheduleIndex, 1)
  //   }
  // }
  const addPaymentSchedule = () => {
    const schedule = {
      id: nanoid(),
      terms: [],
    }
    let terms = 2 + form.payment_schedules.length

    for (let i = 0; i < terms; i++) {
      schedule.terms.push(generateTerm())
    }

    form.payment_schedules.push(schedule)
  }

  return {
    addPaymentSchedule,
    addPaymentTerm,
  }
}
