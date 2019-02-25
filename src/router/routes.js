import moduleLoader from '../ModuleLoader.client.js'

const routes = [
  {
    name: 'Layout',
    path: '/',
    component: () => import('layouts/Basic.vue'),
    children: [
      { name: 'Index', path: '', component: () => import('pages/Index.vue') },
      {
        name: 'Login',
        path: '/login',
        component: () => import('pages/Login.vue')
      }
    ]
  }
]

// Load module routes
if (typeof window.modules === 'object') {
  for (const moduleName in window.modules) {
    const moduleConf = window.modules[moduleName]
    moduleLoader.attachRoutes(routes, moduleConf)
  }
}

// Always leave this as last one
if (process.env.MODE !== 'ssr') {
  routes.push({
    path: '*',
    component: () => import('pages/Error404.vue')
  })
}

export default routes
