/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
const routes = [
  {
    name: 'Layout',
    path: '/',
    component: () => import('layouts/Basic.vue')
  },
  {
    name: 'Login',
    path: '/users/login',
    redirect: '/users/login/form',
    component: () => import('layouts/Users/Login.vue'),
    children: [
      {
        name: 'Form',
        path: '/users/login/form',
        component: () => import('pages/Users/Login/Form.vue')
      },
      {
        name: '2FA',
        path: '/users/login/2fa',
        component: () => import('pages/Users/Login/2FA.vue')
      },
      {
        name: 'Reminder',
        path: '/users/login/reminder',
        component: () => import('pages/Users/Login/Reminder.vue')
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
