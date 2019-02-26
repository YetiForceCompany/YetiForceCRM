const routes = [
  {
    name: 'Layout',
    path: '/',
    component: () => import('layouts/Basic.vue')
  },
  {
    name: 'Login',
    path: '/login',
    component: () => import('pages/Login.vue')
  }
]

// Always leave this as last one
if (process.env.MODE !== 'ssr') {
  routes.push({
    path: '*',
    component: () => import('pages/Error404.vue')
  })
}

export default routes
