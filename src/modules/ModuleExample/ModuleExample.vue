<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
  <div class="ModuleExample"></div>
</template>
<script>
import mutations from 'src/store/mutations.js'
import getters from 'src/store/getters.js'
import { mapGetters } from 'vuex'

export default {
  name: 'ModuleExample',
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
        path: '/module-example',
        icon: 'home',
        label: 'Module Example'
      }
    })
    this.$store.commit(mutations.Menu.updateItems, items)
  }
}
</script>
