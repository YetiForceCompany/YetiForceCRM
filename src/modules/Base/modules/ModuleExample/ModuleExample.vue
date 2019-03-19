<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import ModuleLoader from 'src/ModuleLoader.js'
import moduleStore from './store/index.js'
import mutations from 'store/mutations.js'

const moduleName = 'Base.ModuleExample'
export default {
  name: moduleName,
  created() {
    this.$store.registerModule(moduleName.split('.'), ModuleLoader.prepareStoreNames(moduleName, moduleStore))
  },
  beforeMount() {
    this.$store.commit(mutations.Core.Menu.addItem, {
      component: 'RoutePush',
      props: {
        path: '/base/module-example',
        icon: 'mdi-home-outline',
        label: 'Base Example'
      }
    })
  }
}
</script>
