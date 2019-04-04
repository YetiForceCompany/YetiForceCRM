/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import routes from './routes.js'
import getters from '/src/store/getters.js'

Vue.use(VueRouter)

/*
 * If not building with SSR mode, you can
 * directly export the Router instantiation
 */
let Router = null
export default function({ store }) {
  Router = new VueRouter({
    scrollBehavior: () => ({ y: 0 }),
    routes,
    mode: 'hash',
    base: '/'
  })

  Router.beforeEach((routeTo, routeFrom, next) => {
    Quasar.plugins.Loading.show({
      spinner: Quasar.components.QSpinnerGears
    })
    next()
  })
  Router.afterEach(() => {
    Quasar.plugins.Loading.hide()
  })
  return Router
}
export { Router }
