/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

"use strict";

jQuery.Class("KnowledgeBase_Tree_Js", {
	registerEvents: function() {
		Quasar.iconSet.set(Quasar.iconSet.mdiV3);
		KnowledgeBaseTree.mount({
			el: "#KnowledgeBaseTree"
		});
	}
});
