/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import Objects from 'src/utilities/Objects.js'

export default {
  /**
   * Get urls
   *
   * @param {object} state
   *
   * @returns {object}
   */
  get(state) {
    console.log('get', state)
    return path => {
      console.log('path', path, state)
      return Objects.get(state, path)
    }
  }
}
