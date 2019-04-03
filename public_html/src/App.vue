<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div id="app">
    <component v-for="module in modules" :key="module.fullName" :is="module.component" />
    <router-view />
  </div>
</template>

<script>
const moduleName = 'App'

export default {
  name: moduleName,
  props: ['modules'],
  provide() {
    const provider = {}
    const self = this
    Object.defineProperty(provider, 'App', {
      enumerable: true,
      get: () => self
    })
    return provider
  },
  beforeRouteEnter(to, from, next) {
    next(vm => {
      vm.$i18n.locale = to.meta.module || '_Base'
      vm.$store.commit('Global/update', window.env)
      next()
    })
  },
  beforeRouteUpdate(to, from, next) {
    next(vm => {
      vm.$i18n.locale = to.meta.module || '_Base'
      next()
    })
  }
}
</script>

<style></style>
