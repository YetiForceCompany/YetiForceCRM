<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div id="q-app">
    <router-view/>
    <div class="modules" style="display:none">
      <component v-for="module of modules" :is="module.component" :key="module.fullName"></component>
    </div>
  </div>
</template>

<script>
import mutations from 'src/store/mutations.js'
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
  },
  created() {
    this.$store.commit('Global/update', window.env)
  },
  mounted() {
    this.$store.commit(mutations.Menu.updateItems, [
      {
        component: 'RoutePush',
        props: {
          path: '/users/login/form',
          icon: 'input',
          label: 'Login'
        }
      }
    ])
  }
}
</script>

<style></style>
