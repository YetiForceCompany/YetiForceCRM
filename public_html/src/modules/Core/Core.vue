<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import moduleStore from './store/index.js'

const moduleName = 'Core'

export function initialize({ store }) {
  store.registerModule(moduleName, ModuleLoader.prepareStoreNames(moduleName, moduleStore))
}

export default {
  name: moduleName,
  data() {
    return {
      config: {
        debug: window.env.Core.Debug.levels.map(level => level)
      }
    }
  }
}
</script>
