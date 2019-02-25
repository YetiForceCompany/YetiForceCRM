const routes = [
  {
    name: 'Layout',
    path: '/',
    component: () => import('layouts/Basic.vue'),
    children: [
      { path: '', component: () => import('pages/Index.vue') },
      { path: '/login', component: () => import('pages/Login.vue') }
    ]
  }
]

// Load module routes
if (typeof window.modules === 'object') {
  for (const moduleName in window.modules) {
    const moduleConf = window.modules[moduleName]
    moduleConf.routes.forEach(route => {
      route.component = () =>
        import(`../modules/${moduleName}/${route.componentPath}`)
      if (typeof route.parent === 'string') {
        for (const parentRoute of routes) {
          if (parentRoute.name === route.parent) {
            parentRoute.children.push(route)
          }
        }
      } else {
        routes.push(route)
      }
    })
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
