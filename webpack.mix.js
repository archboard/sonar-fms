const mix = require('laravel-mix')
const webpackConfig = require('./webpack.config')
const host = process.env.APP_URL
  ? process.env.APP_URL.split('//')[1]
  : 'localhost'

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
  .vue({ version: 3 })
  .disableSuccessNotifications()
  .version()
  .sourceMaps(true)
  .webpackConfig(webpackConfig)
  .postCss('resources/css/app.css', 'public/css', [
    require('postcss-import'),
    require('tailwindcss/nesting'),
    require('tailwindcss'),
    require('autoprefixer'),
  ])
  .postCss('resources/css/pdf.css', 'public/css', [
    require('postcss-import'),
    require('tailwindcss/nesting'),
    require('tailwindcss')('./tailwind-pdf.config.js'),
    require('autoprefixer'),
  ])
  .postCss('resources/css/ckeditor.css', 'public/css', [
    require('postcss-import'),
    require('tailwindcss/nesting'),
    require('tailwindcss'),
    require('autoprefixer'),
  ])
  .babelConfig({
    plugins: ['@babel/plugin-syntax-dynamic-import'],
  })
  .options({
    hmrOptions: {
      https: true,
      host: host,
      port: '8080'
    },
    autoprefixer: { remove: false }
  })
