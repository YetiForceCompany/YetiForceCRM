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
export default [
  {
    parent: 'Core',
    name: 'Core.Users.Login',
    path: 'users/login',
    redirect: 'users/login/form',
    componentPath: 'layouts/Login.vue',
    beforeEnter: fetchChildRouteData,
    children: [
      {
        name: 'Core.Users.Login.LoginForm',
        path: 'form',
        meta: { module: 'Core.Users', view: 'Login' },
        componentPath: 'pages/Login/Form.vue'
      },
      {
        name: 'Core.Users.Login.2FA',
        path: '2fa',
        meta: { module: 'Core.Users', view: 'Login' },
        componentPath: 'pages/Login/2FA.vue'
      },
      {
        name: 'Core.Users.Login.Reminder',
        path: 'reminder',
        meta: { module: 'Core.Users', view: 'Login' },
        componentPath: 'pages/Login/Reminder.vue'
      }
    ]
  }
]
