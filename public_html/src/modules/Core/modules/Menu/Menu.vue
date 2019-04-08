<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import ModuleLoader from '/src/ModuleLoader.js'
import moduleStore from './store/index.js'
import mutations from '/store/mutations.js'
import actions from '/store/actions.js'

const moduleName = 'Core.Menu'

export function initialize({ store }) {
  store.registerModule(moduleName.split('.'), ModuleLoader.prepareStoreNames(moduleName, moduleStore))
}

export default {
  name: moduleName,
  created() {
    this.$store.commit(mutations.Core.Url.addUrl, {
      path: 'Core.Menu.getData',
      url: 'api.php'
    })
    this.$store.dispatch(actions.Core.Menu.fetchData)
  }
}
</script>
<style></style>
