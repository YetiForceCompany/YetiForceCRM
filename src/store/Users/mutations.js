/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import mutations from 'src/store/mutations.js'
export default {
  /**
   * Set authorization data
   *
   * @param   {object}  state
   * @param   {object}  data
   */
  [mutations.Users.isLoggedIn](state, data) {
    state.isLoggedIn = data.isLoggedIn
  }
}
