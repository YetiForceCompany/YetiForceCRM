<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import ModuleLoader from 'src/ModuleLoader.js'
import store from './store/index.js'
const moduleName = 'App'
export default {
  name: moduleName,
  priority: 0,
  created() {
    this.$store.registerModule(moduleName, ModuleLoader.prepareStoreNames(moduleName, store))
  }
}
</script>
