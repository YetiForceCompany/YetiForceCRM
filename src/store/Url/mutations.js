/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import mutations from 'src/store/mutations.js'
import Objects from 'src/utilities/Objects.js'

export default {
  /**
   * Add url to Env state
   *
   * @param {object} state
   * @param {object} payload
   */
  [mutations.Url.addUrl](state, { path, url }) {
    Objects.setReactive(state.url, path, url)
  }
}
