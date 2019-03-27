/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import Objects from '/src/utilities/Objects.js'
export default {
  /**
   * Update language store
   *
   * @param   {object}  state
   * @param   {object}  payload
   */
  update(state, payload) {
    state = Objects.mergeDeepReactive(state, payload)
  }
}
