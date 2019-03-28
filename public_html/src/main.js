import App from './App.js'
import createStore from '/src/store/index.js'
import createRouter from '/src/router/index.js'
import createI18n from '/src/i18n/index.js'

Quasar.iconSet.set(Quasar.iconSet.mdiV3)

let components = {}
let modules = []
let coreModules = []
let standardModules = []

if (typeof window.modules === 'object') {
  const flat = ModuleLoader.flattenModules(window.modules)
  components = flat.components
  modules = flat.modules
  coreModules = modules.filter(module => module.fullName.substring(0, 4) === 'Core')
  standardModules = modules.filter(module => module.fullName.substring(0, 4) !== 'Core')
}

async function start() {
  const store = createStore()
  const router = createRouter({ store })
  store.$router = router
  try {
    if (typeof window.env.Env.dev !== 'undefined') {
      console.info('initialize core modules')
    }
    for (let module of coreModules) {
      let component = await module.component()
      if (typeof component.initialize === 'function') {
        component.initialize({ store, router })
      }
      module.component = component.default
    }
    if (typeof window.env.Env.dev !== 'undefined') {
      console.info('initialize standard modules')
    }
    for (let module of standardModules) {
      let component = await module.component()
      if (typeof component.initialize === 'function') {
        component.initialize({ store, router })
      }
      module.component = component.default
      if (typeof window.env.Env.dev !== 'undefined') {
        console.log(module.component)
      }
    }
    if (typeof window.dev !== 'undefined') {
      console.info('initialize components')
    }
    for (let componentName in components) {
      const component = components[componentName]
      const resolved = await component.component()
      component.component = resolved.default
      if (typeof window.env.Env.dev !== 'undefined') {
        console.log(componentName, component)
      }
    }
  } catch (e) {
    console.error(e)
  }

  const app = {
    el: '#app',
    render: h => h(App),
    store,
    router
  }
  createI18n({ app })
  new Vue(app)
}

start()
