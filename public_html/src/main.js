/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import AppComponent from './Main.vue.js'
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
  if (window.env.Core.Env.dev) {
    console.groupCollapsed('Loader logs')
  }
  const store = createStore()
  const router = createRouter({ store })
  store.$router = router
  try {
    if (window.env.Core.Env.dev) {
      console.groupCollapsed('Core modules')
    }
    for (let module of coreModules) {
      let component = await module.component()
      if (typeof component.initialize === 'function') {
        component.initialize({ store, router })
      }
      module.component = component.default
      if (window.env.Core.Env.dev) {
        console.log(module.component)
      }
    }
    if (window.env.Core.Env.dev) {
      console.groupEnd()
      console.groupCollapsed('Standard modules')
    }
    for (let module of standardModules) {
      let component = await module.component()
      if (typeof component.initialize === 'function') {
        component.initialize({ store, router })
      }
      module.component = component.default
      if (window.env.Core.Env.dev) {
        console.log(module.component)
      }
    }
    if (window.env.Core.Env.dev) {
      console.groupEnd()
      console.groupCollapsed('Components')
    }
    for (let componentName in components) {
      const component = components[componentName]
      const resolved = await component.component()
      component.component = resolved.default
      if (window.env.Core.Env.dev) {
        console.log(componentName, component)
      }
    }
    if (window.env.Core.Env.dev) {
      console.groupEnd()
    }
  } catch (e) {
    console.error(e)
  }

  const app = {
    el: '#app',
    render: h => h(AppComponent, { props: { modules } }),
    store,
    router
  }
  createI18n({ app })
  const App = new Vue(app)
  if (window.env.Core.Env.dev) {
    console.groupEnd()
  }
  window.App = App
  return App
}

export default start()
