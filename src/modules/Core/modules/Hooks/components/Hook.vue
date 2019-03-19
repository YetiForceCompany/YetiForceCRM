<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="hooks">
    <component v-for="component in components" :key="component.name" :is="component" />
  </div>
</template>
<script>
import getters from 'store/getters.js'

const moduleName = 'Hook'
export default {
  name: moduleName,
  props: {
    name: { type: String, required: true },
    type: { type: String, required: false, default: 'hook', validator: value => ['hook', 'slot'].indexOf(value) !== -1 }
  },
  computed: {
    fullName() {
      return `${this.$parent.$options.name}.${this.name}`
    },
    components() {
      return this.$store.getters[getters.Core.Hooks.get](this.fullName)
    }
  }
}
</script>
<style></style>
