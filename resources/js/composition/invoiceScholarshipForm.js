import { nanoid } from 'nanoid'
import fetchesScholarships from './fetchesScholarships'
import invoiceItemForm from './invoiceItemForm'
import { computed } from 'vue'

export default (form) => {
  const { subtotal, getItemsTotal } = invoiceItemForm(form)
  const { scholarships } = fetchesScholarships()
  const addScholarship = () => {
    form.scholarships.push({
      id: nanoid(),
      scholarship_id: null,
      name: null,
      amount: null,
      percentage: null,
      resolution_strategy: 'App\\ResolutionStrategies\\Least',
      applies_to: [],
    })
  }
  const getApplicableItems = item => form.items.filter(i => item.applies_to.includes(i.id))
  const syncWithScholarship = item => {
    const scholarship = scholarships.value.find(s => s.id === item.scholarship_id)

    if (scholarship) {
      item.name = scholarship.name
      item.amount = scholarship.amount
      item.percentage = scholarship.percentage_converted
      item.resolution_strategy = scholarship.resolution_strategy
    }
  }
  const scholarshipSelected = item => {
    syncWithScholarship(item)
  }
  const getItemDiscount = item => {
    let total = subtotal.value

    // If we're applying the total to none
    // or all, the total is the invoice subtotal
    if (
      item.applies_to.length !== 0 ||
      item.applies_to.length !== form.items.length
    ) {
      // Find the items for which this scholarship applies
      const items = getApplicableItems(item)
      total = getItemsTotal(items)
    }

    let discount = item.amount || 0
    const percentage = item.percentage || 0
    let percentageDiscount = total * (percentage / 100)

    if (discount && percentage) {
      discount = item.resolution_strategy.includes('Least')
        ? Math.min(discount, percentageDiscount)
        : Math.max(discount, percentageDiscount)
    }

    if (!discount && percentageDiscount) {
      discount = percentageDiscount
    }

    return parseFloat(discount)
  }
  const scholarshipSubtotal = computed(() => {
    return form.scholarships.reduce((total, i) => total + getItemDiscount(i), 0)
  })

  return {
    scholarships,
    scholarshipSubtotal,
    getItemDiscount,
    addScholarship,
    scholarshipSelected,
  }
}
