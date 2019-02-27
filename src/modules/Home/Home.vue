<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
export default {
  name: 'Home',
  mounted() {
    const menuPosition = {
      component: 'RoutePush',
      props: {
        path: '/home',
        icon: 'home',
        label: 'Home'
      }
    }
    const positions = this.$store.state.Base.menu.positions.map(position => position)
    positions.push(menuPosition)
    this.$store.commit('Base/updateMenuPositions', positions)
  }
}
</script>
