/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import ModuleLoader from '../ModuleLoader.js'

const routes = [
  {
    name: 'App',
    path: '/',
    component: () => import('/src/layouts/Basic.vue.js')
  }
]

// Load module routes
if (typeof window.modules === 'object') {
  ModuleLoader.loadRoutes(routes, window.modules)
}

export default routes
