/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import ModuleLoader from 'src/ModuleLoader.js'
export default {
  /**
   * Get modules
   *
   * @param   {object}  state
   *
   * @return  {function}  (fullModuleName)
   */
  get(state) {
    return fullModuleName => ModuleLoader.getModule(fullModuleName, state.modules)
  },

  /**
   * Get all modules
   *
   * @param   {object}  state
   *
   * @return  {array}
   */
  all(state) {
    return state.modules
  }
}
