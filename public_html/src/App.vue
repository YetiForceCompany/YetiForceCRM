<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <hook-wrapper class="App">
    <component v-for="module in modules" :key="module.fullName" :is="module.component"></component>
    <component :is="templateLoader" v-if="templateLoader"></component>
  </hook-wrapper>
</template>

<script>
import getters from '/src/store/getters.js'

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
  computed: {
    ...Vuex.mapGetters({
      template: getters.Core.Env.template
    }),
    templateLoader() {
      const template = this.template
      return () => import(`/src/layouts/${template || 'Basic'}.vue.js`)
    }
  },
  mounted() {
    this.templateLoader()
      .then(() => {
        this.templatePath = () => this.templateLoader()
      })
      .catch(() => {
        this.templatePath = () => import('/src/layouts/Basic.vue.js')
      })
  },
  beforeRouteEnter(to, from, next) {
    next(vm => {
      vm.$i18n.locale = to.meta.langModule || '_Base'
      vm.$store.commit('Global/update', window.env)
      next()
    })
  },
  beforeRouteUpdate(to, from, next) {
    next(vm => {
      vm.$i18n.locale = to.meta.langModule || '_Base'
      next()
    })
  }
}
</script>

<style></style>
