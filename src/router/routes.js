/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
const routes = [
  {
    name: 'Layout',
    path: '/',
    component: () => import('layouts/Basic.vue')
  },
  {
    name: 'User',
    path: '/user',
    redirect: '/user/login',
    component: () => import('layouts/User.vue'),
    children: [
      {
        name: 'Reminder',
        path: '/user/reminder',
        component: () => import('pages/User/Reminder.vue')
      },
      {
        name: 'Login',
        path: '/user/login',
        component: () => import('pages/User/Login.vue')
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
