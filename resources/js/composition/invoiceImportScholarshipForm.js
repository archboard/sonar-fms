import { nanoid } from 'nanoid'
import fetchesScholarships from './fetchesScholarships'
import invoiceImportMapField from '@/composition/invoiceImportMapField'

export default (form) => {
  const { addMapFieldValue } = invoiceImportMapField()
  const { scholarships } = fetchesScholarships()
  const addScholarship = () => {
    form.scholarships.push({
      id: nanoid(),
      use_amount: true,
      scholarship_id: addMapFieldValue(),
      name: addMapFieldValue(),
      amount: addMapFieldValue(),
      percentage: addMapFieldValue(),
      resolution_strategy: addMapFieldValue('App\\ResolutionStrategies\\Least'),
      applies_to: [],
    })
  }
  const syncWithScholarship = item => {
    const scholarship = scholarships.value.find(s => s.id === item.scholarship_id)

    if (scholarship) {
      item.name.value = scholarship.name
      item.amount.value = scholarship.amount
      item.percentage.value = scholarship.percentage_converted
      item.resolution_strategy.value = scholarship.resolution_strategy
      item.use_amount = !!scholarship.amount
    }
  }
  const scholarshipSelected = item => {
    syncWithScholarship(item)
  }

  return {
    scholarships,
    addScholarship,
    scholarshipSelected,
  }
}
