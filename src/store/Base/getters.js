import getters from '../../store/getters.js'
import Objects from '../../utilities/Objects.js'

export default {
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
    return path => Objects.get(state.url, path)
  }
}
