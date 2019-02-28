/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import mutations from '../../store/mutations.js'
import Objects from '../../utilities/Objects.js'

export default {
  /**
   * Set menu positions
   *
   * @param {object} state
   * @param {array} positions
   */
  [mutations.Base.updateMenuPositions](state, positions) {
    state.menu.positions = positions
  },

  /**
   * Add url to base state
   *
   * @param {object} state
   * @param {object} payload
   */
  [mutations.Base.addUrl](state, { moduleName, path, url }) {
    Objects.setReactive(state.url, path, url)
  }
}
