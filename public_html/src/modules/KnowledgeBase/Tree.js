/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

import Vue from 'vue';
import Tree from './Tree.vue';
import Quasar from 'quasar/dist/quasar.umd.js';
import iconSet from 'quasar/icon-set/mdi-v3.js';
import BaseService from '../../services/Base.js';
const componentsList = [
	'QLayout',
	'QPageContainer',
	'QPage',
	'QHeader',
	'QFooter',
	'QDrawer',
	'QPageSticky',
	'QPageScroller',
	'QTree'
];
Vue.prototype.$axios = BaseService;
Vue.use(Quasar, {
	components: Quasar.components[componentsList]
	// plugins: Quasar.components['Notify']
}).use(BaseService);
Quasar.iconSet.set(iconSet);

let VueInstance = null;
window.KnowledgeBaseTree = {
	component: Tree,
	mount(config) {
		VueInstance = new Vue(Tree).$mount(config.el);
		return VueInstance;
	}
};
export default VueInstance;
