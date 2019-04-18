/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import ModuleLoader from '../ModuleLoader.js'

const routes = [
  {
    name: 'App',
    path: '/',
    component: () => import('/src/App.vue.js')
  },
  {
    name: '404',
    path: '*',
    component: () => import('/src/pages/errors/404.vue.js')
  },
  {
    name: 'Exception',
    path: '/exception',
    props: true,
    component: () => import('/src/pages/errors/Exception.vue.js')
  }
]

// Load module routes
if (typeof window !== 'undefined' && typeof window.modules === 'object') {
  ModuleLoader.loadRoutes(routes, window.modules)
}

export default routes
