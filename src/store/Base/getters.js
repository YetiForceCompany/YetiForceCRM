/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import getters from '../../store/getters.js'
import Objects from '../../utilities/Objects.js'

export default {
  /**
   * Get global configuration from server
   *
   * @param {object} state
   *
   * @returns {object}
   */
  [getters.Base.config](state) {
    return state.config
  },

  /**
   * Get menu positions
   *
   * @param {object} state
   *
   * @returns {array}
   */
  [getters.Base.menuPositions](state) {
    return state.menu.positions
  },

  /**
   * Get urls
   *
   * @param {object} state
   *
   * @returns {object}
   */
  [getters.Base.url](state) {
    return state.url
  }
}
