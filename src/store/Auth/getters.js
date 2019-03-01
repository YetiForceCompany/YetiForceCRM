/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import getters from '../getters.js'
export default {
  /**
   * Check if user is authenticated
   *
   * @param   {null|string}  state
   *
   * @return  {bool}
   */
  [getters.Auth.isAuthenticated](state) {
    return state.tokenId !== null
  }
}
