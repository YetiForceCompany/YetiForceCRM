<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import ModuleLoader from 'src/ModuleLoader.js'
import moduleStore from './store/index.js'
import getters from 'src/store/getters.js'
import mutations from 'src/store/mutations.js'
import { mapGetters } from 'vuex'

export default {
  name: 'App.ModuleExample',

  created() {
    this.$store.registerModule(
      ['App', 'ModuleExample'],
      ModuleLoader.prepareStoreNames('App.ModuleExample', moduleStore)
    )
  },

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
        path: '/app/module-example',
        icon: 'home',
        label: 'App Example'
      }
    })
    this.$store.commit(mutations.Menu.updateItems, items)
  }
}
</script>
