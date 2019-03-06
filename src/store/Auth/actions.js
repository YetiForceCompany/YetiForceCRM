/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import authAxios from '../../services/Auth.js'
import apiAxios from '../../services/Api.js'
import actions from '../actions.js'
import mutations from '../mutations.js'
import getters from '../../store/getters.js'

export default {
  /**
   * Fetch view data
   *
   * @param {object} state
   */
  [actions.Auth.fetchViewData]({ commit }) {
    commit(mutations.Auth.setViewData, {
      LANGUAGES: ['polish', 'english', 'german'],
      IS_BLOCKED_IP: false, //bruteforce check,
      MESSAGE: '', //\App\Session::get('UserLoginMessageType'),
      MESSAGE_TYPE: '',
      LOGIN_PAGE_REMEMBER_CREDENTIALS: true, // AppConfig::security('LOGIN_PAGE_REMEMBER_CREDENTIALS')
      FORGOT_PASSWORD: false, //{if AppConfig::security('RESET_LOGIN_PASSWORD') && App\Mail::getDefaultSmtp()}
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
  [actions.Auth.login]({ commit, rootGetters }, user) {
    authAxios({
      url: rootGetters[getters.Base.url].Auth.login,
      data: user,
      method: 'POST'
    })
      .then(response => {
        const data = response.data
        if (data.success) {
          commit(mutations.Base.isLoggedIn, {
            isLoggedIn: true
          })
          this.$store.commit(mutations.Base.updateEnv, data.env)
          this.$router.replace('/')
        } else {
          return console.error('Server error', response)
        }
      })
      .catch(error => console.log(error))
      .catch(err => {
        reject(err)
      })
  }
}
