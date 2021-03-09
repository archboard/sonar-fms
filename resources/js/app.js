import { createApp, h } from 'vue'
import { App } from '@inertiajs/inertia-vue3'
import plugins from '@/plugins'
import components from '@/components'
import './bootstrap'

const el = document.getElementById('app')

// Create the application instance
const app = createApp({
  render: () => h(App, {
    initialPage: JSON.parse(el.dataset.page),
    resolveComponent: name => import(`./pages/${name}`).then(module => module.default),
  })
})

// Register all the plugins
plugins.forEach(app.use)

// Register global components
Object.keys(components).forEach(componentName => {
  app.component(componentName, components[componentName])
})

// Mount the app
app.mount(el)
