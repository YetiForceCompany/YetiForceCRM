/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
// import { store } from '/src/store/index.js'
// import mutations from '/src/store/mutations.js'
// import { i18n } from '../i18n/index.js'
// import { Router } from '../router/index.js'
import axios from 'axios'
import VueInstance from '../modules/KnowledgeBase/Tree.js'
const BaseService = axios.create({
	baseURL: '/'
})

BaseService.interceptors.response.use(
	function(response) {
		return response
	},
	function(error) {
		const data = error.response.data
		let type = 'error'
		data.type = data.type || type
		return Promise.reject(error)
	}
)
export default BaseService
