/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

import Vue from 'vue';
import Tree from './Tree.vue';

window.KnowledgeBaseTree = {
	component: Tree,
	mount(config) {
		return new Vue(Tree).$mount(config.el);
	}
};
export default KnowledgeBaseTree;
