const path = require('path')
const fs = require('fs')
require('dotenv').config()

const config = {
  output: {
    chunkFilename: 'js/[name].js?id=[chunkhash]'
  },
  plugins: [],
  stats: {
    children: true
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
    },
  }
}

if (
  (process.env.APP_ENV && process.env.APP_ENV !== 'production') ||
  process.env.NODE_ENV !== 'production'
) {
  const url = new URL(process.env.APP_URL)

  config.devServer = {
    host: url.host,
    port: process.env.APP_PORT,
    https: {
      key: fs.readFileSync(process.env.APP_SSL_KEY),
      cert: fs.readFileSync(process.env.APP_SSL_CERT),
    },
    headers: {
      "Access-Control-Allow-Origin": "*",
      "Access-Control-Allow-Methods": "GET, POST, PUT, DELETE, PATCH, OPTIONS",
      "Access-Control-Allow-Headers": "X-Requested-With, content-type, Authorization"
    },
  }
}

module.exports = config
