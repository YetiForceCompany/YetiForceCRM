/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

import Quasar from '../../../libraries/quasar.js'
import KnowledgeBaseComponent from './KnowledgeBase.vue'
import RecordPreviewComponent from './RecordPreview.vue'
import store from '../../../libraries/vuex.js'

let VueInstance = null
window.KnowledgeBase = {
	component: KnowledgeBaseComponent,
	mount(config) {
		KnowledgeBaseComponent.moduleName = config.moduleName
		VueInstance = new window.Vue(KnowledgeBaseComponent).$mount(config.el)
		return VueInstance
	},
	store
}
window.RecordPreview = {
	component: RecordPreviewComponent,
	mount(config) {
		RecordPreviewComponent.options = config.options
		VueInstance = new window.Vue(RecordPreviewComponent).$mount(config.el)
		return VueInstance
	}
}
export default VueInstance
