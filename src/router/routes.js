/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import store from 'src/store'
import actions from 'src/store/actions'
import { i18n } from 'src/boot/i18n.js'

function fetchChildRouteData(routeTo, routeFrom, next) {
  store.dispatch(actions[routeTo.meta.module].fetchData, routeTo.meta.view).then(res => {
    i18n.locale = routeTo.meta.module
    next()
  })
}

const routes = [
  {
    name: 'Layout',
    path: '/',
    component: () => import('src/layouts/Basic.vue')
  },
  {
    name: 'Login',
    path: '/users/login',
    redirect: '/users/login/form',
    component: () => import('layouts/Users/Login.vue'),
    beforeEnter: fetchChildRouteData,
    children: [
      {
        name: 'LoginForm',
        path: '/users/login/form',
        meta: { module: 'Users', view: 'Login' },
        component: () => import('pages/Users/Login/Form.vue')
      },
      {
        name: '2FA',
        path: '/users/login/2fa',
        meta: { module: 'Users', view: 'Login' },
        component: () => import('pages/Users/Login/2FA.vue')
      },
      {
        name: 'Reminder',
        path: '/users/login/reminder',
        meta: { module: 'Users', view: 'Login' },
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
