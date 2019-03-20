/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
export default {
  /**
   * Set authorization data
   *
   * @param   {object}  state
   * @param   {object}  data
   */
  isLoggedIn(state, isLoggedIn) {
    state.isLoggedIn = isLoggedIn
  },

  setMessage(state, message) {
    state.message = message
  }
}
