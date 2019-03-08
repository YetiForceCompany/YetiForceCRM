/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import loginAxios from 'src/services/Login.js'
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
  [actions.Users.fetchData]({ commit, rootGetters }) {
    loginAxios({
      url: rootGetters[getters.Url]('Users.getData'),
      method: 'POST'
    }).then(response => {
      commit(mutations.Global.update, response.data.env)
    })
    //TODO commit to remove when rootGetters[getters.Url.all].Users.getData is ready
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
      Users: {
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
  [actions.Users.login]({ commit, rootGetters }, formData) {
    loginAxios({
      url: rootGetters[getters.Url]('Users.login'),
      data: formData,
      method: 'POST'
    }).then(response => {
      const data = response.data
      if (data.result === true) {
        commit(mutations.Global.update, data.env)
        this.$router.replace('/')
      } else if (data.result.step !== undefined) {
        this.$router.replace(`/users/login/${data.result.step}`)
      } else {
        return console.error('Server error', response)
      }
    })
  },
  /**
   * Remind action
   *
   * @param   {object}  store
   * @param   {object}  formData
   */
  [actions.Users.remind]({ commit, rootGetters }, formData) {
    loginAxios({
      url: rootGetters[getters.Url]('Users.remind'),
      data: formData,
      method: 'POST'
    }).then(response => {
      this.$router.replace('/users/login/form')
    })
  }
}
