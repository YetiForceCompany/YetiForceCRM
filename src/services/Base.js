/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import axios from 'axios'
import store from 'src/store'
import mutations from 'src/store/mutations.js'

const BaseService = axios.create({
  baseURL: '/'
})
BaseService.interceptors.response.use(
  function(response) {
    return response
  },
  function(error) {
    store.commit(mutations.Global.update, { ERROR: error })
    return Promise.reject(error)
  }
)
export default BaseService
