/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import Vue from 'vue'
import VueRouter from 'vue-router'
import routes from './routes.js'
import ModuleLoader from '../ModuleLoader.js'
import getters from 'src/store/getters.js'

import { Loading, QSpinnerGears } from 'quasar'

// Load module routes
if (typeof window.modules === 'object') {
  ModuleLoader.loadRoutes(routes, window.modules)
}
console.log(routes)
Vue.use(VueRouter)

/*
 * If not building with SSR mode, you can
 * directly export the Router instantiation
 */

export default function({ store }) {
  const Router = new VueRouter({
    scrollBehavior: () => ({ y: 0 }),
    routes,

    // Leave these as is and change from quasar.conf.js instead!
    // quasar.conf.js -> build -> vueRouterMode
    // quasar.conf.js -> build -> publicPath
    mode: window.env.routerMode, //process.env.VUE_ROUTER_MODE,
    base: '/'
  })

  Router.beforeEach((routeTo, routeFrom, next) => {
    Loading.show({
      spinner: QSpinnerGears
    })
    if (store.getters[getters.App.Core.Users.isLoggedIn] || routeTo.path.startsWith('/app/core/users/login')) {
      next()
    } else {
      next({ name: 'App.Core.Users.Login' })
    }
  })
  Router.afterEach(() => {
    Loading.hide()
  })

  return Router
}
