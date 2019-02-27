import getters from '../../store/getters.js'

export default {
  /**
   * Get menu positions
   *
   * @param {object} state
   * @returns {array}
   */
  [getters.Base.menuPositions](state) {
    return state.menu.positions
  }
}
