import { reactive } from 'vue'
import { nanoid } from 'nanoid'

const style = {
  base: [
    "color: #fff",
    "background-color: #444",
    "padding: 2px 4px",
    "border-radius: 2px"
  ],
  error: [
    "background-color: red"
  ],
  success: [
    "background-color: green"
  ]
}
const log = (text, extra = []) => {
  let styles = style.base.join(';') + ';'
  styles += extra.join(';')
  console.log(`%c${text}`, styles)
}

export default {
  state: reactive({
    notifications: [],
    timeouts: {}
  }),

  addNotification (notification, delay = 4000) {
    const id = `id_${nanoid()}`
    log(`Flashing ${notification.level}: ${notification.text}`, style[notification.level])

    this.state.notifications.splice(0, 0, {
      ...notification,
      id,
    })

    this.state.timeouts[id] = setTimeout(() => this.removeNotification(id), delay)
  },

  removeNotification (id) {
    clearTimeout(this.state.timeouts[id])
    const index = this.state.notifications.findIndex(n => n.id === id)

    this.state.notifications.splice(index, 1)
  }
}
