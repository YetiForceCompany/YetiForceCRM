<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import mutations from 'store/mutations.js'
export default {
  name: 'App.Home',
  mounted() {
    this.$store.commit(mutations.Base.Menu.addItem, {
      component: 'RoutePush',
      props: {
        path: '/app/home',
        icon: 'home',
        label: 'Home'
      }
    })
  }
}
</script>
