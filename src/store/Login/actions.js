import loginAxios from '../../services/Login.js'
import { LocalStorage } from 'quasar'

export function login({ commit, dispatch }, user) {
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
      commit('AUTH_USER', {
        tokenId: data.tokenId,
        userId: data.userId,
        admin: data.admin,
        userName: data.userName
      })
      dispatch('setLogoutTimer', data.expiresIn)
      this.$router.replace('/')
    })
    .catch(error => console.log(error))
    .catch(err => {
      LocalStorage.remove('tokenId')
      reject(err)
    })
}
export function setLogoutTimer({ commit }, expirationTime) {
  setTimeout(() => {
    commit('CLEAR_AUTH_DATA')
  }, expirationTime * 100000)
}
export function tryAutoLogin({ commit }) {
  return new Promise(resolve => {
    const token = localStorage.getItem('tokenId')
    const expirationDate = new Date(localStorage.getItem('expiresIn')).getTime()
    const now = new Date().getTime()
    if (!token || now >= expirationDate) {
      commit('CLEAR_AUTH_DATA')
      resolve(false)
    } else {
      commit('AUTH_USER', {
        tokenId: token,
        userId: localStorage.getItem('userId'),
        admin: localStorage.getItem('admin'),
        userName: localStorage.getItem('userName')
      })
      resolve(true)
    }
  })
}
