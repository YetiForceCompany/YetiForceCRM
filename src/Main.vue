<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div id="q-app">
    <div class="modules" style="display:none">
      <component v-for="module of modules" :is="module.component" :key="module.fullName"></component>
    </div>
    <router-view/>
  </div>
</template>

<script>
import ModuleLoader from 'src/ModuleLoader.js'
let components = {}
let modules = []
if (typeof window.modules === 'object') {
  const flat = ModuleLoader.flattenModules(window.modules)
  components = flat.components
  modules = flat.modules
}

/**
 * @vue-data {Array} modules - installed modules
 */
export default {
  name: 'Main',
  components,
  data() {
    return {
      modules
    }
  }
}
</script>

<style></style>
