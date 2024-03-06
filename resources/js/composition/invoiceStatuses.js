import { inject } from 'vue'

export default (blockedStatuses = []) => {
  const __ = inject('$translate')
  const statuses = {
    unpaid: __('Unpaid'),
    paid: __('Paid'),
    partial: __('Partially paid'),
    past: __('Past due'),
    published: __('Published'),
    draft: __('Draft'),
    void: __('Void'),
    canceled: __('Canceled'),
  }

  return {
    statuses: Object.keys(statuses).reduce((carry, status) => {
      if (blockedStatuses.includes(status)) {
        return carry
      }

      carry[status] = statuses[status]
      return carry
    }, {}),
  }
}
