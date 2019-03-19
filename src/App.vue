<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div id="q-app">
    <debug :levels="config.debug.levels" />
    <div class="modules" style="display:none">
      <component v-for="module of modules" :is="module.component" :key="module.fullName"></component>
    </div>
    <router-view />
  </div>
</template>

<script>
import ModuleLoader from 'src/ModuleLoader.js'
import coreStore from './modules/Core/store/index.js'
import Debug from './modules/Core/modules/Debug/Debug.vue'

let components = {}
let modules = []
if (typeof window.modules === 'object') {
  const flat = ModuleLoader.flattenModules(window.modules)
  components = flat.components
  modules = flat.modules
}
modules.forEach(module => module.component())

const moduleName = 'App'
/**
 * @vue-data {Array} modules - installed modules
 */
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
    this.$store.registerModule('Core', ModuleLoader.prepareStoreNames('Core', coreStore))
    if (typeof window !== 'undefined') {
      this.config.debug.levels = window.env.Debug.levels.map(level => level)
    }
  }
}
</script>

<style></style>
