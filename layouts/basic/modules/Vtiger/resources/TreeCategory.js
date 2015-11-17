/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("Vtiger_TreeCategory_Js", {}, {
	modalContainer: false,
	treeInstance: false,
	getModalContainer: function () {
		if (this.modalContainer == false) {
			this.modalContainer = jQuery('#globalmodal .modal-content');
		}
		return this.modalContainer;

	},
	generateTree: function (container) {
		var thisInstance = this;
		if (thisInstance.treeInstance == false) {
			var treeValues = container.find('#treePopupValues').val();
			var data = JSON.parse(treeValues);
			thisInstance.treeInstance = container.find("#treePopupContents");
			thisInstance.treeInstance.jstree({
				core: {
					data: data
				},
				plugins: [
					"checkbox"
				]
			});
		}
		return this.treeInstance;
	},
	registerEvents: function () {
		var container = this.getModalContainer();
		this.generateTree(container);
	}
});
jQuery(function () {
	var instance = new Vtiger_TreeCategory_Js();
	instance.registerEvents();
});
