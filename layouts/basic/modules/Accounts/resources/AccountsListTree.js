/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("Accounts_AccountsListTree_Js", {}, {
	modalContainer: false,
	treeInstance: false,
	treeData: false,
	getContainer: function () {
		if (this.modalContainer == false) {
			this.modalContainer = jQuery('#centerPanel');
		}
		return this.modalContainer;
	},
	getRecords: function (container) {
		if (this.treeData == false && container != 'undefined') {
			var treeValues = container.find('#treePopupValues').val();
			this.treeData = JSON.parse(treeValues);
		}		
		return this.treeData;
	},
	generateTree: function (container) {
		var thisInstance = this;
		if (thisInstance.treeInstance == false) {
			thisInstance.treeInstance = container.find("#treePopupContents");
			thisInstance.treeInstance.jstree({
				core: {
					data: thisInstance.getRecords(container),
					themes: {
						name: 'proton',
						responsive: true
					}
				},
				plugins: [
					"checkbox",
				]
			});
		}
	},	
	registerEvents: function () {
		var container = this.getContainer();
		this.getRecords(container);
		this.generateTree(container);
		
	}
});
