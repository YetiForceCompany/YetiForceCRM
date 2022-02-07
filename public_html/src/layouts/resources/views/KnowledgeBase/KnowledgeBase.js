/**
 * KnowledgeBase components initializations
 *
 * @description KnowledgeBase views' instances
 * @license YetiForce Public License 5.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

import KnowledgeBaseComponent from './KnowledgeBase.vue'
import KnowledgeBaseModal from './KnowledgeBaseModal.vue'
import ArticlePreviewComponent from './ArticlePreviewModal.vue'
import store from 'store'
import moduleStore from './store'
import { createNamespacedHelpers } from 'vuex'
Vue.config.productionTip = false

const { mapActions } = createNamespacedHelpers('KnowledgeBase')
store.registerModule('KnowledgeBase', moduleStore)

Vue.mixin({
	methods: {
		translate(key) {
			return app.vtranslate(key)
		},
	},
})
window.KnowledgeBase = {
	component: KnowledgeBaseComponent,
	mount(config) {
		KnowledgeBaseComponent.state = config.state
		return new Vue({
			store,
			render: (h) => h(KnowledgeBaseComponent),
			methods: {
				...mapActions(['fetchCategories', 'initState']),
			},
			created() {
				this.initState(config.state)
			},
		}).$mount(config.el)
	},
}
window.ArticlePreviewVueComponent = {
	component: ArticlePreviewComponent,
	mount(config) {
		ArticlePreviewComponent.state = config.state
		return new Vue({
			store,
			render: (h) => h(ArticlePreviewComponent),
		}).$mount(config.el)
	},
}
window.KnowledgeBaseModalVueComponent = {
	component: KnowledgeBaseModal,
	mount(config) {
		KnowledgeBaseModal.state = config.state
		return new Vue({
			store,
			render: (h) => h(KnowledgeBaseModal),
		}).$mount(config.el)
	},
}
