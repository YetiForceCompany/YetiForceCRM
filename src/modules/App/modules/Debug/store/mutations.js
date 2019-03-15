/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import Objects from 'utilities/Objects.js'

export default {
  /**
   * Push error to state
   *
   * @param   {object}  state
   * @param   {object}  payload
   */
  pushError(state, payload) {
    state.errors.push({ [payload.source]: Objects.mergeDeepReactive({}, payload.data) })
  }
}
