import mutations from '../../store/mutations.js'

export default {
  /**
   * Set menu positions
   *
   * @param {object} state
   * @param {array} positions
   */
  [mutations.Base.updateMenuPositions](state, positions) {
    state.menu.positions = positions
  }
}
