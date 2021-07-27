import { ref, onMounted, watchEffect } from 'vue'
import { createPopper } from '@popperjs/core'

export default (options) => {
  let trigger = ref(null)
  let container = ref(null)

  onMounted(() => {
    watchEffect(onInvalidate => {
      if (!container.value || !trigger.value) {
        return
      }

      let popperEl = container.value.el || container.value
      let referenceEl = trigger.value.el || trigger.value

      if (
        !(referenceEl instanceof HTMLElement) ||
        !(popperEl instanceof HTMLElement)
      ) {
        return
      }

      let { destroy } = createPopper(referenceEl, popperEl, options)

      onInvalidate(destroy)
    })
  })

  return {
    trigger,
    container,
  }
}
