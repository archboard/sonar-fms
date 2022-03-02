import { ref, onMounted, watchEffect } from 'vue'
import { createPopper } from '@popperjs/core'

export default (options) => {
  const trigger = ref(null)
  const container = ref(null)
  let updatePopper = null
  let updatePopperOptions = null
  const update = () => {
    if (typeof updatePopper === 'function') {
      updatePopper()
    }
  }
  const toggleEventListener = (enabled) => {
    if (typeof updatePopperOptions === 'function') {
      updatePopperOptions(options => ({
        ...options,
        modifiers: [
          ...options.modifiers,
          { name: 'eventListeners', enabled }
        ]
      }))
    }
  }

  onMounted(() => {
    watchEffect(onInvalidate => {
      if (!container.value || !trigger.value) {
        return
      }

      let popperEl = container.value.el || container.value.$el || container.value
      let referenceEl = trigger.value.el || trigger.value.$el || trigger.value

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
    toggleEventListener,
  }
}
