/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

import Quasar from '../../../quasar.config.js'
import KnowledgeBaseComponent from './KnowledgeBase.vue'
import RecordPreviewComponent from './RecordPreviewModal.vue'
import store from '../../../store/index.js'

let VueInstance = null
window.Vue.component(KnowledgeBaseComponent)
window.Vue.mixin({
	methods: {
		translate(key) {
			return app.vtranslate(key)
		}
	}
})
window.KnowledgeBase = {
	component: KnowledgeBaseComponent,
	mount(config) {
		KnowledgeBaseComponent.state = config.state
		VueInstance = new window.Vue({
			store,
			render: h => h(KnowledgeBaseComponent)
		}).$mount(config.el)
		return VueInstance
	}
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
