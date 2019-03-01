/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
const routes = [
  {
    name: 'Layout',
    path: '/',
    component: () => import('layouts/Basic.vue')
  },
  {
    name: 'Auth',
    path: '/auth',
    redirect: '/auth/login',
    component: () => import('layouts/Auth.vue'),
    children: [
      {
        name: 'Reminder',
        path: '/auth/reminder',
        component: () => import('pages/Reminder.vue')
      },
      {
        name: 'Login',
        path: '/auth/login',
        component: () => import('pages/Login.vue')
      },
      {
        name: 'Brute',
        path: '/auth/brute',
        component: () => import('pages/Brute.vue')
      }
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
