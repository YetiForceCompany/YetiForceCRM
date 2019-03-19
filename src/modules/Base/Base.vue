<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div></div>
</template>
<script>
import ModuleLoader from 'src/ModuleLoader.js'
import store from './store/index.js'
const moduleName = 'Base'
export default {
  name: moduleName,
  priority: 1,
  created() {
    this.$store.registerModule(moduleName.split('.'), ModuleLoader.prepareStoreNames(moduleName, store))
  }
}
</script>
