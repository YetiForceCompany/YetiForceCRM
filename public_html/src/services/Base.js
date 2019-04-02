/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import store from '/src/store/index.js'
import mutations from '/src/store/mutations.js'
import { i18n } from '../i18n/index.js'
const BaseService = axios.create({
  baseURL: '/'
})
BaseService.interceptors.response.use(
  function(response) {
    return response
  },
  function(error) {
    if (error.response.data) {
      let data = error.response.data
      Quasar.plugins.Notify.create({
        color: 'negative',
        icon: 'mdi-exclamation',
        message: data.error.message,
        position: 'top',
        actions: [{ label: i18n.t('LBL_CLOSE'), color: 'white' }]
      })
    }
    // store.commit(mutations.Debug.pushError, { source: 'BaseService', data: error })
    return Promise.reject(error)
  }
)
export default BaseService
