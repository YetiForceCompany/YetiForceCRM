
const routes = [
  {
    path: '/',
    component: () => import('layouts/Basic.vue'),
    children: [
      { path: '', component: () => import('pages/Index.vue') },
      { path: '/users', component: () => import('pages/Users.vue') },
      { path: '/roles', component: () => import('pages/Roles.vue') },
      { path: '/profiles', component: () => import('pages/Profiles.vue') },
      { path: '/groups', component: () => import('pages/Groups.vue') },
    ]
  },
]

// Always leave this as last one
if (process.env.MODE !== 'ssr') {
  routes.push({
    path: '*',
    component: () => import('pages/Error404.vue')
  })
}

export default routes
