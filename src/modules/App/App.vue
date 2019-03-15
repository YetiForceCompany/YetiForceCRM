<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="App">
    <debug :levels="config.debug.levels" />
    <div class="modules" style="display:none">
      <component v-for="module of modules" :is="module.component" :key="module.fullName"></component>
    </div>
    <router-view />
  </div>
</template>
<script>
import ModuleLoader from 'src/ModuleLoader.js'
import store from './store/index.js'
import coreStore from './modules/Core/store/index.js'
import Debug from './modules/Core/modules/Debug/Debug.vue'
import mutations from 'store/mutations.js'
import Objects from 'utilities/Objects.js'

let components = {}
let modules = []
if (typeof window.modules === 'object') {
  const flat = ModuleLoader.flattenModules(window.modules)
  components = flat.components
  modules = flat.modules
}
modules.forEach(module => module.component())

const moduleName = 'App'
export default {
  name: moduleName,
  components,
  provide() {
    const provider = {}
    const self = this
    Object.defineProperty(provider, 'App', {
      enumerable: true,
      get: () => self
    })
    Object.defineProperty(provider, 'debug', {
      enumerable: true,
      get: () => self.$children[0]
    })
    return provider
  },
  data() {
    return {
      modules,
      config: {
        debug: {
          levels: ['log', 'info', 'notice', 'warning', 'error']
        }
      }
    }
  },
  created() {
    this.$store.registerModule(moduleName, ModuleLoader.prepareStoreNames(moduleName, store))
    this.$store.registerModule(['App', 'Core'], ModuleLoader.prepareStoreNames('App.Core', coreStore))
    if (typeof window !== 'undefined') {
      this.$store.commit(mutations.App.setModules, Objects.mergeDeepReactive({}, window.modules))
      this.config.debug.levels = window.env.Debug.levels.map(level => level)
    }
  }
}
</script>
