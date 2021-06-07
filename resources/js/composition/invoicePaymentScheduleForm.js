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
  const removePaymentTerm = (schedule, index) => {
    schedule.payments.splice(index, 1)

    if (schedule.payments.length < 2) {
      const scheduleIndex = form.payment_schedules.findIndex(s => s.id === schedule.id)
      form.payment_schedules.splice(scheduleIndex, 1)
    }
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
    removePaymentTerm,
  }
}
