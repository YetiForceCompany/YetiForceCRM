/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import getters from 'src/store/getters.js'

export default {
  /**
   * Get menu positions
   *
   * @param {object} state
   *
   * @returns {array}
   */
  [getters.Menu.items](state) {
    return state.items
  }
}
