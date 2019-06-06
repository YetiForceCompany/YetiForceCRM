/**
 * KnowledgeBase components initializations
 *
 * @description KnowledgeBase views' instances
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

import KnowledgeBaseComponent from './KnowledgeBase/KnowledgeBase.vue'
import RecordPreviewComponent from './KnowledgeBase/RecordPreviewModal.vue'
import store from '../../../store/index.js'
Vue.mixin({
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
		return new Vue({
			store,
			render: h => h(KnowledgeBaseComponent)
		}).$mount(config.el)
	}
}
window.RecordPreviewVueComponent = {
	component: RecordPreviewComponent,
	mount(config) {
		RecordPreviewComponent.state = config.state
		return new Vue({
			store,
			render: h => h(RecordPreviewComponent)
		}).$mount(config.el)
	}
}
