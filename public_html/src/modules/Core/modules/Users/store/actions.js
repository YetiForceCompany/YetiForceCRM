/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import loginAxios from '/src/services/Login.js'
import getters from '/src/store/getters.js'
import mutations from '/src/store/mutations.js'
import { initSocket } from '/src/services/WebSocket.js'

export default {
  /**
   * Fetch view data
   *
   * @param {object} state
   */
  fetchData({ commit, rootGetters }, view) {
    // loginAxios({
    //   url: rootGetters[getters.Core.Url.get](`Users.${view}.getData`),
    //   method: 'POST'
    // }).then(response => {
    //   commit('Global/update', { Core: response.data.env })
    // })
    commit('Global/update', { Core: window.env.Core })
  },
  /**
   * Login action
   *
   * @param   {object}  store
   * @param   {object}  formData
   */
  login({ commit, rootGetters }, { formData, vm }) {
    loginAxios({
      url: rootGetters[getters.Core.Url.get]('Users.Login.login'),
      data: formData,
      method: 'POST'
    }).then(response => {
      const data = response.data
      if (data.result === true) {
        commit('Global/update', { Core: data.env })
        commit(mutations.Core.Users.isLoggedIn, true)
        initSocket().then(() => {
          this.$router.replace('/')
        })
      } else if (data.result === '2fa') {
        this.$router.replace(`/users/login/2FA`)
      } else {
        return console.error('Server error', response)
      }
    })
  },
  /**
   * Logout action
   *
   * @param   {object}  store
   */
  logout({ commit, rootGetters }) {
    if (rootGetters[getters.Core.Users.isLoggedIn]) {
      loginAxios({
        url: rootGetters[getters.Core.Url.get]('Users.Login.logout'),
        method: 'POST'
      }).then(response => {
        const data = response.data
        if (data.result === true) {
          commit(mutations.Core.Users.isLoggedIn, false)
          initSocket().close()
          this.$router.replace('/users/login')
        }
      })
    }
  },
  /**
   * Remind action
   *
   * @param   {object}  store
   * @param   {object}  formData
   */
  remind({ commit, rootGetters }, formData) {
    loginAxios({
      url: rootGetters[getters.Core.Url.get]('Users.remind'),
      data: formData,
      method: 'POST'
    }).then(response => {
      this.$router.replace('/users/login/form')
    })
  }
}
