export function AUTH_USER(state, userData) {
  state.tokenId = userData.token
  state.userId = userData.userId
  state.userName = userData.token
  state.admin = userData.userId
  state.expiresIn = userData.expiresIn
}

export function STORE_USER(state, user) {
  state.user = user
}

export function CLEAR_AUTH_DATA(state) {
  state.tokenId = null
  state.userId = null
  state.userName = null
  state.admin = null
  state.expiresIn = null
}
