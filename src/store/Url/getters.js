/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import getters from 'src/store/getters.js'
import Objects from 'src/utilities/Objects.js'

export default {
  /**
   * Get urls
   *
   * @param {object} state
   *
   * @returns {object}
   */
  [getters.Url]: state => path => {
    return Objects.get(state, path)
  }
}
