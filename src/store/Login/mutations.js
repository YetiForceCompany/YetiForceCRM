export function AUTH_USER(state, userData) {
  state.idToken = userData.token
  state.userId = userData.userId
}

export function STORE_USER(state, user) {
  state.user = user
}

export function CLEAR_AUTH_DATA(state) {
  state.idToken = null
  state.userId = null
}
