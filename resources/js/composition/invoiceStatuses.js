import { inject } from 'vue'

export default () => {
  const __ = inject('$translate')
  const statuses = {
    unpaid: __('Unpaid'),
    paid: __('Paid'),
    past: __('Past due'),
    published: __('Published'),
    draft: __('Draft'),
    void: __('Void'),
  }

  return {
    statuses,
  }
}
