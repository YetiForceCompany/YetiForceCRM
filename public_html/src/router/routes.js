/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import ModuleLoader from '../ModuleLoader.js'

const routes = [
  {
    name: 'App',
    path: '/',
    component: () => import('/src/App.vue.js'),
    children: [
      {
        parent: 'App',
        name: 'Basic',
        path: '/',
        redirect: 'base/home',
        component: () => import('/src/layouts/Basic.vue.js')
      },
      {
        parent: 'App',
        name: 'Error404',
        path: '*',
        component: () => import('../pages/Error404.vue.js')
      }
    ]
  }
]

// Load module routes
if (typeof window !== 'undefined' && typeof window.modules === 'object') {
  ModuleLoader.loadRoutes(routes, window.modules)
}

export default routes
