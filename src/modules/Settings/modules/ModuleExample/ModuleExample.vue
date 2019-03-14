<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="ModuleExample"></div>
</template>
<script>
import mutations from 'src/store/mutations.js'

export default {
  name: 'Settings.ModuleExample',
  mounted() {
    this.$store.commit(mutations.Base.Menu.addItem, {
      component: 'RoutePush',
      props: {
        path: '/settings/module-example',
        icon: 'home',
        label: 'Settings Example'
      }
    })
  }
}
</script>
