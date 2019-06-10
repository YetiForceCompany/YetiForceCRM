/**
 * KnowledgeBase components initializations
 *
 * @description KnowledgeBase views' instances
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

import KnowledgeBaseComponent from './KnowledgeBase/KnowledgeBase.vue'
import KnowledgeBaseModal from './KnowledgeBase/KnowledgeBaseModal.vue'
import ArticlePreviewComponent from './KnowledgeBase/ArticlePreviewModal.vue'
import store from '../../../store/index.js'
import { createNamespacedHelpers } from 'vuex'
const { mapActions } = createNamespacedHelpers('KnowledgeBase')
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
			render: h => h(KnowledgeBaseComponent),
			methods: {
				...mapActions(['fetchCategories', 'initState'])
			},
			async created() {
				await this.initState(config.state)
			}
		}).$mount(config.el)
	}
}
window.ArticlePreviewVueComponent = {
	component: ArticlePreviewComponent,
	mount(config) {
		ArticlePreviewComponent.state = config.state
		return new Vue({
			store,
			render: h => h(ArticlePreviewComponent)
		}).$mount(config.el)
	}
}
window.KnowledgeBaseModalVueComponent = {
	component: KnowledgeBaseModal,
	mount(config) {
		KnowledgeBaseModal.state = config.state
		return new Vue({
			store,
			render: h => h(KnowledgeBaseModal)
		}).$mount(config.el)
	}
}
