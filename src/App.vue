<template>
  <div id="q-app">
    <router-view/>
    <div class="modules">
      <component v-for="module in modules" :is="module.component" :key="module.name"></component>
    </div>
  </div>
</template>

<script>
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

export default {
  components,
  data() {
    return {
      modules
    }
  },
  mounted() {
    this.$store.commit('Base/updateMenuPositions', [
      {
        component: 'RoutePush',
        props: {
          path: '/login',
          icon: 'input',
          label: 'Login'
        }
      }
    ])
  },
  preFetch({ store, redirect }) {
    store.dispatch('Login/tryAutoLogin')
    if (store.state.Login.idToken === null) {
      //redirect('/login')
    }
  },
  name: 'App'
}
</script>

<style></style>
