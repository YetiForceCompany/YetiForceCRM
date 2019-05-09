<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import ModuleLoader from '/src/ModuleLoader.js'
import moduleStore from './store/index.js'
import mutations from '/store/mutations.js'
const moduleStoreInstance = new moduleStore()
const moduleName = moduleStoreInstance.state.moduleName
const fullModuleName = 'Base.' + moduleStoreInstance.state.moduleName

export function initialize({ store, router }) {
  let submodules = moduleStoreInstance.modules
  delete moduleStoreInstance.modules
  store.registerModule(fullModuleName.split('.'), ModuleLoader.prepareStoreNames(fullModuleName, moduleStoreInstance))
  Object.keys(submodules).forEach(key => {
    let submoduleName = fullModuleName.split('.')
    submoduleName.push(key)
    store.registerModule(submoduleName, ModuleLoader.prepareStoreNames(fullModuleName + '.' + key, submodules[key]))
  })
  if (moduleStoreInstance.state.menu) {
    store.commit(mutations.Core.Menu.addItem, {
      path: `/${moduleName}`,
      icon: 'mdi-cube',
      label: moduleName,
      children: []
    })
  }
}

export default {
  name: fullModuleName
}
</script>
