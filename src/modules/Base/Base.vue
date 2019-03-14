<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import ModuleLoader from 'src/ModuleLoader.js'
import store from './store/index.js'

export default {
  name: 'Base',
  priority: 0,
  created() {
    this.$store.registerModule('Base', ModuleLoader.prepareStoreNames('Base', store))
  }
}
</script>
