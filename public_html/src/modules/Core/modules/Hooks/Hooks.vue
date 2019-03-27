<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import ModuleLoader from '/src/ModuleLoader.js'
import moduleStore from './store/index.js'
import HookWrapper from './components/HookWrapper.js'

const moduleName = 'Core.Hooks'

export function initialize({ store, router }) {
  store.registerModule(moduleName.split('.'), ModuleLoader.prepareStoreNames(moduleName, moduleStore))
}

export default {
  name: moduleName,
  inject: ['debug'],
  created() {}
}

Vue.component('hook-wrapper', HookWrapper)
</script>
<style></style>
