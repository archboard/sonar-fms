const colors = require('tailwindcss/colors')

module.exports = {
  purge: [],
  darkMode: 'class', // or 'media' or 'class'
  theme: {
    extend: {
    },
    colors: {
      transparent: 'transparent',
      current: 'currentColor',
      primary: colors.cyan,
      black: colors.black,
      white: colors.white,
      gray: colors.trueGray,
      red: colors.red,
      yellow: colors.yellow,
      blue: colors.blue,
      green: colors.green,
      orange: colors.orange,
    }
  },
  variants: {
    extend: {},
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/ui'),
  ],
}
