<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import ModuleLoader from 'src/ModuleLoader.js'
import store from './store/index.js'
import Objects from 'utilities/Objects.js'
import mutations from 'store/mutations.js'

const moduleName = 'Core.Menu'
export default {
  name: moduleName,
  created() {
    this.$store.registerModule(moduleName.split('.'), ModuleLoader.prepareStoreNames(moduleName, store))
  }
}
</script>
<style></style>
