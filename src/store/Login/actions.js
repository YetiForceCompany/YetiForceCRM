import loginAxios from '../../services/Login.js'
import globalAxios from '../../services/Global.js'

export function login({ commit, dispatch }, user) {
  loginAxios({
    url: 'login.php/',
    data: user,
    method: 'POST'
  })
    .then(res => {
      const now = new Date()
      const expirationDate = new Date(now.getTime() + res.data.expiresIn * 1000)
      localStorage.setItem('token', res.data.idToken)
      localStorage.setItem('userId', res.data.localId)
      localStorage.setItem('expirationDate', expirationDate)
      commit('AUTH_USER', {
        token: res.data.idToken,
        userId: res.data.localId
      })
      dispatch('setLogoutTimer', res.data.expiresIn)
    })
    .catch(error => console.log(error))
    .catch(err => {
      commit('auth_error')
      localStorage.removeItem('token')
      reject(err)
    })
}
export function setLogoutTimer({ commit }, expirationTime) {
  setTimeout(() => {
    commit('CLEAR_AUTH_DATA')
  }, expirationTime * 1000)
}
export function tryAutoLogin({ commit }) {
  const token = localStorage.getItem('token')
  if (!token) {
    return
  }
  const expirationDate = localStorage.getItem('expirationDate')
  const now = new Date()
  if (now >= expirationDate) {
    return
  }
  const userId = localStorage.getItem('userId')
  commit('AUTH_USER', {
    token: token,
    userId: userId
  })
}
