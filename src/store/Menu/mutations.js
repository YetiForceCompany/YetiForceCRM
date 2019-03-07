/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import mutations from 'src/store/mutations.js'

export default {
  /**
   * Update menu positions
   *
   * @param {object} state
   * @param {array} positions
   */
  [mutations.Menu.updateItems](state, items) {
    state.items = items
  }
}
