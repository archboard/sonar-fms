const path = require('path')
const fs = require('fs')
const CKEditorWebpackPlugin = require('@ckeditor/ckeditor5-dev-webpack-plugin')
require('dotenv').config()

const config = {
  output: {
    chunkFilename: 'js/[name].js?id=[chunkhash]'
  },
  plugins: [
    new CKEditorWebpackPlugin({
      language: 'en',
      additionalLanguages: ['ko', 'ar', 'zh'],
    })
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
    },
  },
  module: {
    rules: [
      {
        test: /\.(postcss)$/,
        use: [
          'vue-style-loader',
          { loader: 'css-loader', options: { importLoaders: 1 } },
          'postcss-loader'
        ]
      }
    ],
  },
}

if (
  (process.env.APP_ENV && process.env.APP_ENV !== 'production') ||
  process.env.NODE_ENV !== 'production'
) {
  const url = new URL(process.env.APP_URL)

  // config.stats = {
  //   children: true
  // }

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
