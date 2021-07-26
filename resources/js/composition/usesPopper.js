import { ref } from 'vue'
import { createPopper} from '@popperjs/core'

export default () => {
  const popperInstance = ref(null)

  const popPop = (reference, popper) => {
    popperInstance.value = createPopper(reference, popper, {
      placement: 'bottom-end',
      modifiers: [
        {
          name: 'offset',
          options: {
            offset: [0, 8],
          }
        }
      ]
    })
  }
  const destroyPop = () => {
    if (!popperInstance.value) {
      return
    }

    popperInstance.value.destroy()
    popperInstance.value = null
  }

  return {
    popPop,
    popperInstance,
    destroyPop,
  }
}
