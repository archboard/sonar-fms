import { nanoid } from 'nanoid'

export default (form, total) => {
  const generateTerm = () => ({
    id: nanoid(),
    amount: null,
    percentage: null,
    due_at: null,
  })
  const addPaymentTerm = schedule => {
    schedule.payments.push(generateTerm())
  }
  const addPaymentSchedule = () => {
    const schedule = {
      id: nanoid(),
      payments: [],
    }
    let payments = 2 + form.payment_schedules.length
    const amount = Math.round(total.value / payments)

    for (let i = 0; i < payments; i++) {
      const term = generateTerm()
      term.amount = amount
      schedule.payments.push(term)
    }

    form.payment_schedules.push(schedule)
  }
  const getScheduleTotal = schedule => {
    return schedule.payments.reduce((t, p) => t + Number(p.amount), 0)
  }

  return {
    addPaymentSchedule,
    addPaymentTerm,
    getScheduleTotal,
  }
}
