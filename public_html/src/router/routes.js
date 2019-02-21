const routes = [
  {
    path: '/',
    component: () => import('layouts/Basic.vue'),
    children: [
      {path: '/login', component: () => import('pages/Login.vue')},
      {path: '', component: () => import('pages/Index.vue')}
    ]
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
