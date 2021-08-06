import { nanoid } from 'nanoid'

export default {
  beforeMount (el, binding) {
    const callback = binding.value
    const directiveClass = `clickaway-${nanoid()}`

    el.clickEvent = function (event) {
      const elementIsActive = event.target === el || el.contains(event.target)

      if (
        !elementIsActive &&
        el.classList.contains(directiveClass)
      ) {
        el.classList.remove(directiveClass)
        callback({ el, event })
      } else if (!el.classList.contains(directiveClass)) {
        el.classList.add(directiveClass)
      }
    }

    document.body.addEventListener('click', el.clickEvent)
  },

  unmounted (el) {
    document.body.removeEventListener('click', el.clickEvent)
  }
}
