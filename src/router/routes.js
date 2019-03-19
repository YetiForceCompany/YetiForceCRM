/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import ModuleLoader from '../ModuleLoader.js'

const routes = [
  {
    name: 'Main',
    path: '/',
    component: () => import('src/layouts/Basic.vue')
  }
]

// Load module routes
if (typeof window.modules === 'object') {
  ModuleLoader.loadRoutes(routes, window.modules)
}

// Always leave this as last one
if (process.env.MODE !== 'ssr') {
  routes.push({
    path: '*',
    component: () => import('pages/Error404.vue')
  })
}

export default routes
