<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import ModuleLoader from 'src/ModuleLoader.js'
import store from './store/index.js'
import Vue from 'vue'
import HookWrapper from './components/HookWrapper.vue'

const moduleName = 'Core.Hooks'
export default {
  name: moduleName,
  inject: ['debug'],
  created() {
    this.$store.registerModule(moduleName.split('.'), ModuleLoader.prepareStoreNames(moduleName, store))
  }
}
// register this component globally
Vue.component('hook-wrapper', HookWrapper)
</script>
<style></style>
