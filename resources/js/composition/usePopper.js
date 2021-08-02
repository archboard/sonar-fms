import { ref, onMounted, watchEffect, nextTick } from 'vue'
import { createPopper } from '@popperjs/core'

export default (options) => {
  const trigger = ref(null)
  const container = ref(null)
  let updatePopper = null
  const update = () => {
    if (typeof updatePopper === 'function') {
      updatePopper()
    }
  }

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

      let { destroy, update } = createPopper(referenceEl, popperEl, options)
      updatePopper = update

      onInvalidate(destroy)
    })
  })

  return {
    trigger,
    container,
    update,
  }
}
