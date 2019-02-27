/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import loginAxios from '../../services/Login.js'
import globalAxios from '../../services/Global.js'
import { LocalStorage } from 'quasar'
import actions from '../actions.js'
import mutations from '../mutations.js'

export default {
  /**
   * Login action
   *
   * @param   {any}     commit
   * @param   {any}     dispatch
   * @param   {object}  user
   */
  [actions.Login.login]({ commit, dispatch }, user) {
    loginAxios({
      url: 'login.php/',
      data: user,
      method: 'POST'
    })
      .then(({ data }) => {
        const now = new Date()
        const expirationDate = new Date(now.getTime() + data.expiresIn * 100000)
        LocalStorage.set('tokenId', data.tokenId)
        LocalStorage.set('userId', data.userId)
        LocalStorage.set('userName', data.userName)
        LocalStorage.set('admin', data.admin)
        LocalStorage.set('expiresIn', expirationDate)
        commit(mutations.Login.authUser, {
          tokenId: data.tokenId,
          userId: data.userId,
          admin: data.admin,
          userName: data.userName
        })
        globalAxios.defaults.headers.common['Authorization'] = data.tokenId
        dispatch(actions.Login.setLogoutTimer, data.expiresIn)
        this.$router.replace('/')
      })
      .catch(error => console.log(error))
      .catch(err => {
        LocalStorage.remove('tokenId')
        reject(err)
      })
  },

  /**
   * Clear authentication data, when the expirationTime passed
   *
   * @param   {any}     commit
   * @param   {any}     dispatch
   * @param   {number}  expirationTime
   */
  [actions.Login.setLogoutTimer]({ commit }, expirationTime) {
    setTimeout(() => {
      commit(mutations.Login.clearAuthData)
    }, expirationTime * 100000)
  },

  /**
   * Try auto login on application start
   *
   * @param   {any}     commit
   */
  [actions.Login.tryAutoLogin]({ commit }) {
    return new Promise(resolve => {
      const token = localStorage.getItem('tokenId')
      const expirationDate = new Date(localStorage.getItem('expiresIn')).getTime()
      const now = new Date().getTime()
      if (!token || now >= expirationDate) {
        commit(mutations.Login.clearAuthData)
        resolve(false)
      } else {
        commit(mutations.Login.authUser, {
          tokenId: token,
          userId: localStorage.getItem('userId'),
          admin: localStorage.getItem('admin'),
          userName: localStorage.getItem('userName')
        })
        resolve(true)
      }
    })
  }
}
