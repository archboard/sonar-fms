import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/inertia-vue3'
import plugins from '@/plugins'
import components from '@/components'
import get from 'lodash/get'
import flashesNotifications from '@/plugins/flashesNotifications'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import './bootstrap'

createInertiaApp({
  title: title => title ? `${title} | ${process.env.APP_NAME}` : process.env.APP_NAME,
  resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)

    // Register all the plugins
    plugins.forEach(app.use)

    // Register global components
    Object.keys(components).forEach(componentName => {
      app.component(componentName, components[componentName])
    })

    // Mount the app
    app.mount(el)

    el.removeAttribute('data-page')
    flashesNotifications(get(props, 'initialPage.props.flash'))
  },
})
