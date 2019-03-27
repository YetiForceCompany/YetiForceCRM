/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import store from '/src/store/index.js'
import mutations from '/src/store/mutations.js'

const BaseService = axios.create({
  baseURL: '/'
})
BaseService.interceptors.response.use(
  function(response) {
    return response
  },
  function(error) {
    store.commit(mutations.Debug.pushError, { source: 'BaseService', data: error })
    return Promise.reject(error)
  }
)
export default BaseService
