<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <hook-wrapper class="App">
    <component v-for="module in modules" :key="module.fullName" :is="module.component"></component>
    <component :is="template" v-if="template"></component>
  </hook-wrapper>
</template>

<script>
const moduleName = 'App'

export default {
  name: moduleName,
  data() {
    return {
      template: null
    }
  },
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
  computed: {
    templateLoader() {
      return () => import(`/src/layouts/${window.env.Core.Env.template || 'Basic'}.vue.js`)
    }
  },
  mounted() {
    this.templateLoader()
      .then(() => {
        this.template = () => this.templateLoader()
      })
      .catch(() => {
        this.template = () => import('/src/layouts/Basic.vue.js')
      })
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
