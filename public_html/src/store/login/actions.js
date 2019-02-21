import axios from 'axios'
export function login({commit}, user) {
  return new Promise((resolve, reject) => {
    commit('auth_request')
    axios({url: 'index.php?module=Users&action=Login', data: user, method: 'POST'})
      .then(resp => {
        const token = resp.data.token
        const user = resp.data.user
        localStorage.setItem('token', token)
        axios.defaults.headers.common['Authorization'] = token
        commit('auth_success', token, user)
        resolve(resp)
      })
      .catch(err => {
        commit('auth_error')
        localStorage.removeItem('token')
        reject(err)
      })
  })
}

