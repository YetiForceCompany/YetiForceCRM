<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <hook-wrapper class="App">
    <component :is="templateLoader" v-if="templateLoader"></component>
  </hook-wrapper>
</template>

<script>
import getters from '/src/store/getters.js'

const moduleName = 'App'

const setLangModule = (vm, to) => {
  vm.$i18n.locale = to.meta.langModule || '_Base'
}
/**
 * @vue-computed {String}   template - layout main template name
 * @vue-computed {Function} templateLoader - dynamic import of the template
 * @vue-event {Object}      provide - global components methods
 * @vue-event {Function}    mounted - return template path function
 * @vue-event {Function}    beforeRouteEnter - set current module lanuage and update vuex with window.env
 * @vue-event {Function}    beforeRouteUpdate - setLangModule
 */
export default {
  name: moduleName,
  computed: {
    ...Vuex.mapGetters({
      template: getters.Core.Env.template
    }),
    templateLoader() {
      const template = this.template
      return () => import(`/src/layouts/${template || 'Basic'}.vue.js`)
    }
  },
  provide() {
    const provider = {}
    const self = this
    Object.defineProperty(provider, 'App', {
      enumerable: true,
      get: () => self
    })
    return provider
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
      setLangModule(vm, to)
      vm.$store.commit('Global/update', window.env)
      next()
    })
  },
  beforeRouteUpdate(to, from, next) {
    setLangModule(this, to)
    next()
  }
}
</script>

<style></style>
