<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import ModuleLoader from '/src/ModuleLoader.js'
import moduleStore from './store/index.js'
import mutations from '/src/store/mutations.js'

const moduleName = 'Core.Env'

export function initialize({ store, router }) {
  store.registerModule(moduleName.split('.'), ModuleLoader.prepareStoreNames(moduleName, moduleStore))
  store.commit(mutations.Core.Env.update, window.env.Core.Env)
}

export default {
  name: moduleName
}
</script>
<style></style>
