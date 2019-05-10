<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import ModuleLoader from '/src/ModuleLoader.js'
import moduleStore from './store/index.js'
const moduleStoreInstance = new moduleStore()
const fullModuleName = 'Base.' + moduleStoreInstance.state.moduleName

export function initialize({ store, router }) {
  ModuleLoader.prepareVuexClassStoreNames(fullModuleName, moduleStoreInstance, store)
}

export default {
  name: fullModuleName
}
</script>
