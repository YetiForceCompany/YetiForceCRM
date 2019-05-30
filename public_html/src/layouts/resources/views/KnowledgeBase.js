/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

import Quasar from '../../../quasar.config.js'
import KnowledgeBaseComponent from './KnowledgeBase/KnowledgeBase.vue'
import RecordPreviewComponent from './KnowledgeBase/RecordPreviewModal.vue'
import store from '../../../store/index.js'

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
		return new window.Vue({
			store,
			render: h => h(KnowledgeBaseComponent)
		}).$mount(config.el)
	}
}
window.RecordPreview = {
	component: RecordPreviewComponent,
	mount(config) {
		RecordPreviewComponent.state = config.state
		return new window.Vue({
			store,
			render: h => h(RecordPreviewComponent)
		}).$mount(config.el)
	}
}
