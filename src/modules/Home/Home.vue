<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
export default {
  name: 'Home',
  mounted() {
    const menuItem = {
      component: 'RoutePush',
      props: {
        path: '/home',
        icon: 'home',
        label: 'Home'
      }
    }
    const items = this.$store.state.Menu.items.map(item => item)
    items.push(menuItem)
    this.$store.commit('Menu/updateItems', items)
  }
}
</script>
