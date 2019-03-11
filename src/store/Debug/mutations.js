/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import mutations from 'src/store/mutations.js'
import Objects from 'src/utilities/Objects.js'

export default {
  /**
   * Push error to state
   *
   * @param   {object}  state
   * @param   {object}  payload
   */
  [mutations.Debug.pushError](state, payload) {
    state.errors.push({ [payload.source]: Objects.mergeDeepReactive({}, payload.data) })
  }
}
