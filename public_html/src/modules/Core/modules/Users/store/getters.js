/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import state from './state.js'

let getters = {
  /**
   * Check if user is logged in
   *
   * @param   {null|string}  state
   *
   * @return  {bool}
   */
  isLoggedIn(state) {
    return state.isLoggedIn !== false
  }
}

function defaultGeters(state) {
  return Object.keys(state).reduce((getters, key) => {
    getters[key] = state => state[key]
    return getters
  }, {})
}

export default Object.assign(defaultGeters(state), getters)
