import axios from 'axios'
import get from 'lodash/get'
import store from '@/stores/notifications'

const flashMessage = response => {
  const flash = get(response, 'data.props.flash')
  const level = get(response, 'data.level')
  const message = get(response, 'data.message')
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

  if (flash) {
    Object.keys(flash).forEach(level => {
      const text = flash[level]

      if (text) {
        log(`Flashing ${level}: ${flash[level]}`, style[level])
        store.addNotification({ level, text })
      }
    })
  }

  if (level && message) {
    store.addNotification({
      level,
      text: message,
    })
  }
}

axios.interceptors.response.use(response => {
  flashMessage(response)

  return response
}, async err => {
  const status = get(err, 'response.status')

  if (status === 419) {
    const config = err.response.config
    const { data } = await axios.get('/csrf-token')
    config.headers['X-XSRF-TOKEN'] = data.token

    return axios(config)
  }

  if (err.response) {
    store.addNotification({
      level: 'error',
      text: get(err, 'response.data.message', err.message),
    })
  }

  return Promise.reject(err)
})

export default {
  install (app) {
    app.config.globalProperties.$http = axios
    app.provide('$http', axios)
  }
}
