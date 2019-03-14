<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import mutations from 'src/store/mutations.js'
import getters from 'src/store/getters.js'
import { mapGetters } from 'vuex'

export default {
  name: 'Settings.Menu',
  computed: {
    ...mapGetters({
      menuItems: getters.Menu.items
    })
  },
  mounted() {
    const items = this.menuItems.map(item => item)
    items.push({
      component: 'RoutePush',
      props: {
        path: '/settings/menu',
        icon: 'list',
        label: 'Menu'
      }
    })
    this.$store.commit(mutations.Menu.updateItems, items)
  }
}
</script>
