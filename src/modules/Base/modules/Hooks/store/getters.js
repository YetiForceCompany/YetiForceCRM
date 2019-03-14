/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import Objects from 'utilities/Objects.js'
export default {
  /**
   * Get hooks for specified path
   */
  get(state) {
    return hookName => Objects.get(state, hookName)
  }
}
