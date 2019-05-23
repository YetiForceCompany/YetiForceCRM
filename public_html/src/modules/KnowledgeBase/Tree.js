/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

import VuePlugin from 'quasar/src/vue-plugin.js'

import {
	QLayout,
	QPageContainer,
	QPage,
	QHeader,
	QFooter,
	QDrawer,
	QPageSticky,
	QPageScroller,
	QToolbar,
	QToolbarTitle,
	QBtn,
	QBreadcrumbs,
	QBreadcrumbsEl,
	QIcon,
	QInput,
	QToggle,
	QTooltip,
	QScrollArea,
	QList,
	QItem,
	QItemLabel,
	QItemSection,
	QTable,
	QDialog,
	QCard,
	QBar,
	QSpace,
	QCardSection,
	QCarousel,
	QCarouselSlide,
	QCarouselControl
} from 'quasar/src/components.js'
import * as directives from 'quasar/src/directives.js'
import * as plugins from 'quasar/src/plugins.js'
import * as utils from 'quasar/src/utils.js'

import Vue from 'vue'
import Tree from './Tree.vue'
import mdi from 'quasar/icon-set/mdi-v3.js'
import BaseService from '../../services/Base.js'
Vue.prototype.$axios = BaseService
const Quasar = {
	...VuePlugin,
	install(Vue, opts) {
		VuePlugin.install(Vue, {
			components: {
				QLayout,
				QPageContainer,
				QPage,
				QHeader,
				QFooter,
				QDrawer,
				QPageSticky,
				QPageScroller,
				QToolbar,
				QToolbarTitle,
				QBtn,
				QBreadcrumbs,
				QBreadcrumbsEl,
				QIcon,
				QInput,
				QToggle,
				QTooltip,
				QScrollArea,
				QList,
				QItem,
				QItemLabel,
				QItemSection,
				QTable,
				QDialog,
				QCard,
				QBar,
				QSpace,
				QCardSection,
				QCarousel,
				QCarouselSlide,
				QCarouselControl
			},
			directives,
			plugins: {},
			...opts
		})
	}
}
Vue.use(Quasar).use(BaseService)
Quasar.iconSet.set(mdi)

let VueInstance = null
window.KnowledgeBaseTree = {
	component: Tree,
	mount(config) {
		VueInstance = new Vue(Tree).$mount(config.el)
		return VueInstance
	}
}
export default VueInstance
