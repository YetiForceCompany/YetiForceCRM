<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="Core-Hooks-HookWrapper">
    <component v-for="component in componentsBefore" :key="component.name" :is="component" />
    <slot />
    <component v-for="component in componentsAfter" :key="component.name" :is="component" />
  </div>
</template>
<script>
import getters from 'store/getters.js'

const moduleName = 'Core.Hooks.HookWrapper'
export default {
  props: { name: { type: String, required: false, default: 'outside' } },
  computed: {
    fullName() {
      return `${this.$parent.$options.name}.${this.name}`
    },
    componentsBefore() {
      return this.$store.getters[getters.Core.Hooks.get](this.fullName + '.before')
    },
    componentsAfter() {
      return this.$store.getters[getters.Core.Hooks.get](this.fullName + '.after')
    }
  }
}
</script>
<style></style>
