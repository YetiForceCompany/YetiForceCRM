/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

export default [
  {
    parent: 'Core',
    name: 'Core.Users.Login',
    path: 'users/login',
    redirect: 'users/login/form',
    componentPath: 'layouts/Login',
    children: [
      {
        name: 'Core.Users.Login.LoginForm',
        path: 'form',
        meta: { langModule: 'Users' },
        componentPath: 'pages/Login/Form'
      },
      {
        name: 'Core.Users.Login.2FA',
        path: '2fa',
        meta: { langModule: 'Users' },
        componentPath: 'pages/Login/2FA'
      },
      {
        name: 'Core.Users.Login.Reminder',
        path: 'reminder',
        meta: { langModule: 'Users' },
        componentPath: 'pages/Login/Reminder'
      }
    ]
  }
]
