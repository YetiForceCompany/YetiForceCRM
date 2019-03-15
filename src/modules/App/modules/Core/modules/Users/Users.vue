<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import ModuleLoader from 'src/ModuleLoader.js'
import store from './store/index.js'
import mutations from 'store/mutations.js'
import getters from 'store/getters.js'
import { mapGetters } from 'vuex'

const moduleName = 'App.Core.Users'
export default {
  name: moduleName,
  computed: {
    ...mapGetters({
      menuItems: getters.App.Core.Menu.items
    })
  },
  created() {
    this.$store.registerModule(moduleName.split('.'), ModuleLoader.prepareStoreNames(moduleName, store))
  },
  mounted() {
    this.$store.commit(mutations.App.Core.Users.isLoggedIn, window.env.isLoggedIn)
    this.$store.commit(mutations.App.Core.Menu.addItem, {
      component: 'RoutePush',
      props: {
        path: '/app/users/login/form',
        icon: 'input',
        label: 'Login'
      }
    })
  }
}
</script>
<style></style>
