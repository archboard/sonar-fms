import { inject } from 'vue'

export default () => {
  const __ = inject('$translate')

  return {
    combined: __('Combined'),
    individual: __('Individual'),
  }
}
