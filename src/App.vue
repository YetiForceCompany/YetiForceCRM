<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div id="q-app">
    <router-view />
    <div class="modules">
      <component v-for="module in modules" :is="module.component" :key="module.name"></component>
    </div>
  </div>
</template>

<script>
import mutations from './store/mutations.js'

const components = {}
const modules = []
if (typeof window.modules === 'object') {
  for (const moduleName in window.modules) {
    components[moduleName] = () => import(`./modules/${moduleName}/${moduleName}.vue`)
    modules.push({
      name: moduleName,
      component: components[moduleName]
    })
  }
}
/**
 * @vue-data {Array} modules - installed modules
 */
export default {
  components,
  data() {
    return {
      modules
    }
  },
  created() {
    this.$store.commit(mutations.Global.update, window.env)
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
  },
  name: 'App'
}
</script>

<style></style>
