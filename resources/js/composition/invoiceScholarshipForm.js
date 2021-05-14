import { nanoid } from 'nanoid'
import fetchesScholarships from './fetchesScholarships'

export default (form) => {
  const { scholarships } = fetchesScholarships()
  const addScholarship = () => {
    form.scholarships.push({
      id: nanoid(),
      scholarship_id: null,
      sync_with_scholarship: false,
      name: null,
      amount: null,
      percentage: null,
      resolution_strategy: 'App\\ResolutionStrategies\\Least',
    })
  }
  const syncWithScholarship = item => {
    const scholarship = scholarships.value.find(s => s.id === item.scholarship_id)

    if (scholarship) {
      item.name = scholarship.name
      item.amount = scholarship.amount
      item.percentage = scholarship.percentage
      item.resolution_strategy = scholarship.resolution_strategy
    }
  }
  const scholarshipSelected = item => {
    syncWithScholarship(item)
    item.sync_with_scholarship = false
  }
  const scholarshipSyncChanged = item => {
    if (item.sync_with_scholarship) {
      syncWithScholarship(item)
    }
  }

  return {
    scholarships,
    addScholarship,
    scholarshipSelected,
    scholarshipSyncChanged,
  }
}
