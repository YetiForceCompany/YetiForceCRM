/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

import Vue from 'vue';
import Tree from './Tree.vue';
import Quasar from 'quasar/dist/quasar.umd.js';
import iconSet from 'quasar/icon-set/mdi-v3.js';
const {
	QLayout,
	QPageContainer,
	QPage,
	QHeader,
	QFooter,
	QDrawer,
	QPageSticky,
	QPageScroller,
	QTree
} = Quasar.components;

Vue.use(Quasar, {
	components: QLayout,
	QPageContainer,
	QPage,
	QHeader,
	QFooter,
	QDrawer,
	QPageSticky,
	QPageScroller,
	QTree
});
Quasar.iconSet.set(iconSet);
window.KnowledgeBaseTree = {
	component: Tree,
	mount(config) {
		return new Vue(Tree).$mount(config.el);
	}
};
export default KnowledgeBaseTree;
