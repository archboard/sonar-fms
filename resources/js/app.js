import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/inertia-vue3'
import plugins from '@/plugins'
import components from '@/components'
import get from 'lodash/get'
import flashesNotifications from '@/plugins/flashesNotifications'
import './bootstrap'

createInertiaApp({
  title: title => title ? `${title} | Sonar FMS` : 'Sonar FMS',
  resolve: name => import(`./pages/${name}`),
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
