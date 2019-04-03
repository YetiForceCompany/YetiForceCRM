<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import moduleStore from './store/index.js'
import ModuleLoader from '/src/ModuleLoader.js'
import mutations from '/store/mutations.js'

const moduleName = 'Core.Users'

export function initialize({ store, router }) {
  store.registerModule(moduleName.split('.'), ModuleLoader.prepareStoreNames(moduleName, moduleStore))
  store.commit(mutations.Core.Users.isLoggedIn, window.env.Core.Users.isLoggedIn)
}

export default {
  name: moduleName
}
</script>
<style></style>
