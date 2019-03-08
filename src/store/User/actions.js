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
    commit(mutations.Global.update, {
      Env: {
        layout: 'material',
        layouts: ['material', 'ios']
      },
      Language: {
        defaultLanguage: 'en-US',
        lang: 'en-US',
        langs: ['pl-PL', 'en-US']
      },
      User: {
        isBlockedIp: false,
        message: '',
        messageType: '',
        loginPageRememberCredentials: true,
        forgotPassword: true,
        languageSelection: true,
        layoutSelection: true
      }
    })
  },
  /**
   * Login action
   *
   * @param   {object}  store
   * @param   {object}  formData
   */
  [actions.User.login]({ commit, rootGetters }, formData) {
    authAxios({
      url: rootGetters[getters.Url.all].User.login,
      data: formData,
      method: 'POST'
    })
      .then(response => {
        const data = response.data
        if (data.result === true) {
          commit(mutations.Global.update, data.env)
          this.$router.replace('/')
        } else if (data.result.step !== undefined) {
          this.$router.replace(`/user/auth/${data.result.step}`)
        } else {
          return console.error('Server error', response)
        }
      })
      .catch(error => console.error(error))
      .catch(err => {
        reject(err)
      })
  },
  /**
   * Remind action
   *
   * @param   {object}  store
   * @param   {object}  formData
   */
  [actions.User.remind]({ commit, rootGetters }, formData) {
    authAxios({
      url: rootGetters[getters.Url.all].User.remind,
      data: formData,
      method: 'POST'
    })
      .then(response => {
        this.$router.replace('/user/auth/login')
      })
      .catch(error => console.error(error))
      .catch(err => {
        reject(err)
      })
  }
}
