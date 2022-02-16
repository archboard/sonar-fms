import ultralightCopy from 'copy-to-clipboard-ultralight'
import { inject } from 'vue'

export default () => {
  const $success = inject('$success')
  const __ = inject('$translate')
  const copy = text => {
    if (ultralightCopy(text)) {
      $success(__('Copied to clipboard.'));
    }
  }

  return {
    copy,
  }
}
