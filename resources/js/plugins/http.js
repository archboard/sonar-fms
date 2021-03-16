import axios from 'axios'
import get from 'lodash/get'
import store from '@/stores/notifications'

axios.interceptors.response.use(response => {
  const flash = get(response, 'data.props.flash')

  if (flash) {
    Object.keys(flash).forEach(level => {
      const text = flash[level]

      if (text) {
        console.log(`${level}: ${flash[level]}`)
        store.addNotification({ level, text }, 100000)
      }
    })
  }

  return response
})

export default {
  install (app) {
    app.config.globalProperties.$http = axios
    app.provide('$http', axios)
  }
}
