/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import authAxios from 'src/services/Auth.js'
import apiAxios from 'src/services/Api.js'
import actions from 'src/store/actions.js'
import mutations from 'src/store/mutations.js'
import getters from 'src/store/getters.js'

export default {
  /**
   * Fetch view data
   *
   * @param {object} state
   */
  [actions.User.fetchViewData]({ commit }) {
    commit(mutations.User.setViewData, {
      LANGUAGES: ['polish', 'english', 'german'],
      IS_BLOCKED_IP: false, //bruteforce check,
      MESSAGE: '', //\App\Session::get('UserLoginMessageType'),
      MESSAGE_TYPE: '',
      LOGIN_PAGE_REMEMBER_CREDENTIALS: true, // AppConfig::security('LOGIN_PAGE_REMEMBER_CREDENTIALS')
      FORGOT_PASSWORD: true, //{if AppConfig::security('RESET_LOGIN_PASSWORD') && App\Mail::getDefaultSmtp()}
      LANGUAGE_SELECTION: true,
      DEFAULT_LANGUAGE: 'polish',
      LAYOUT_SELECTION: true,
      LAYOUTS: ['material', 'ios'] //\App\Layout::getAllLayouts()
    })
  },
  /**
   * Login action
   *
   * @param   {object}  store
   * @param   {object}  user
   */
  [actions.User.login]({ commit, rootGetters }, user) {
    console.log(rootGetters[getters.Url.all].User.login)
    authAxios({
      url: rootGetters[getters.Url.all].User.login,
      data: user,
      method: 'POST'
    })
      .then(response => {
        console.log(response)
        const data = response.data
        if (data.success) {
          commit(mutations.Global.update, data.env)
          this.$router.replace('/')
        } else {
          return console.error('Server error', response)
        }
      })
      .catch(error => console.error(error))
      .catch(err => {
        reject(err)
      })
  }
}
