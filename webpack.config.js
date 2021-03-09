const path = require('path')

module.exports = {
  output: {
    chunkFilename: 'js/[name].js?id=[chunkhash]'
  },
  plugins: [],
  resolve: {
    alias: {
      '@': path.resolve('resources/js'),
    },
  },
}
