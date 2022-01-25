import { inject } from 'vue'

export default () => {
  const __ = inject('$translate')
  const statuses = {
    unpaid: __('Unpaid'),
    paid: __('Paid'),
    partial: __('Partially paid'),
    past: __('Past due'),
    published: __('Published'),
    draft: __('Draft'),
    void: __('Void'),
  }

  return {
    statuses,
  }
}
