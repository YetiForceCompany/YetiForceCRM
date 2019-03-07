/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
const routes = [
  {
    name: 'Layout',
    path: '/',
    component: () => import('layouts/Basic.vue')
  },
  {
    name: 'Auth',
    path: '/user/auth',
    redirect: '/user/auth/login',
    component: () => import('layouts/User/Auth.vue'),
    children: [
      {
        name: 'Login',
        path: '/user/auth/login',
        component: () => import('pages/User/Auth/Login.vue')
      },
      {
        name: 'Qr',
        path: '/user/auth/qr',
        component: () => import('pages/User/Auth/Qr.vue')
      },
      {
        name: 'Reminder',
        path: '/user/auth/reminder',
        component: () => import('pages/User/Auth/Reminder.vue')
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
