<!-- /* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template></template>
<script>
import moduleStore from './store/index.js'
import ModuleLoader from '/src/ModuleLoader.js'
import mutations from '/store/mutations.js'
import getters from '/store/getters.js'

const moduleName = 'Core.Users'

function setLoginRouteGuard(store, router) {
  router.beforeEach((routeTo, routeFrom, next) => {
    let isLoggedIn = store.getters[getters.Core.Users.isLoggedIn]
    if (isLoggedIn === undefined) {
      isLoggedIn = window.env.Core.Users.isLoggedIn
    }
    if (isLoggedIn || routeTo.path.startsWith('/users/login') || routeTo.path.startsWith('/error404')) {
      next()
    } else if (routeFrom.path.startsWith('/users/login')) {
      Quasar.plugins.Loading.hide()
      next(false)
    } else {
      next({ name: 'Core.Users.Login' })
    }
  })
}

export function initialize({ store, router }) {
  store.registerModule(moduleName.split('.'), ModuleLoader.prepareStoreNames(moduleName, moduleStore))
  store.commit(mutations.Core.Users.isLoggedIn, window.env.Core.Users.isLoggedIn)
  setLoginRouteGuard(store, router)
}

export default {
  name: moduleName,
  created() {
    this.$store.commit(mutations.Core.Url.addUrl, { path: 'Users.Login.login', url: 'login.php' })
    this.$store.commit(mutations.Core.Url.addUrl, {
      path: 'Users.Login.logout',
      url: 'api.php?module=Users&action=Logout'
    })
    this.$store.commit(mutations.Core.Url.addUrl, { path: 'Users.Login.remind', url: 'login.php?mode=remind' })
    this.$store.commit(mutations.Core.Url.addUrl, { path: 'Users.Login.getData', url: 'login.php?mode=getData' })
  }
}
</script>
<style></style>
