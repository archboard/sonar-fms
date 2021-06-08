import { reactive } from 'vue'

const state = reactive ({
  isDark: window.isDark,
})

export default {
  state,

  toggle () {
    state.isDark = !state.isDark
    window.changeTheme(state.isDark)
  },
}
