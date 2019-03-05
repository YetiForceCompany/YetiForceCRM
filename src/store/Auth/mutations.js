/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import mutations from '../mutations.js'
export default {
  [mutations.Auth.setViewData](state, data) {
    state.view = {
      LANGUAGES: data.LANGUAGES,
      IS_BLOCKED_IP: data.IS_BLOCKED_IP,
      MESSAGE: data.MESSAGE,
      MESSAGE_TYPE: data.MESSAGE_TYPE,
      LOGIN_PAGE_REMEMBER_CREDENTIALS: data.LOGIN_PAGE_REMEMBER_CREDENTIALS,
      FORGOT_PASSWORD: data.FORGOT_PASSWORD,
      LANGUAGE_SELECTION: data.LANGUAGE_SELECTION,
      DEFAULT_LANGUAGE: data.DEFAULT_LANGUAGE,
      LAYOUT_SELECTION: data.LAYOUT_SELECTION,
      LAYOUTS: data.LAYOUTS
    }
  }
}
