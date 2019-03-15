<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import mutations from 'store/mutations.js'

const moduleName = 'Base.Home'
export default {
  name: moduleName,
  mounted() {
    this.$store.commit(mutations.App.Menu.addItem, {
      component: 'RoutePush',
      props: {
        path: '/base/home',
        icon: 'home',
        label: 'Home'
      }
    })
  }
}
</script>
