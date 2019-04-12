/**
 * Icons mutations
 *
 * @description Icon mutations
 * @license YetiForce Public License 3.0
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */
export default {
  /**
   * Add or replace icon
   *
   * @param {object} state
   * @param {object} payload {label:'icon label',value:'icon class'}
   */
  setIcon(state, payload) {
    state.icons[payload.label] = payload.value
  }
}
