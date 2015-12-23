/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Vtiger_TreeRecords_Js("Products_TreeRecords_Js",{},{
	getRecordsParams: function (container) {
		var thisInstance = this;
		var selectedFilter = container.find('#moduleFilter').val();
		var selected = [];
		$.each(thisInstance.treeInstance.jstree("get_selected", true), function (index, value) {
			if(value.original.isrecord){
				selected.push(value.text);
			}
		});
		var params = {
			module: app.getModuleName(),
			view: app.getViewName(),
			branches: selected,
			filter: selectedFilter
		};
		if(app.getMainParams('isActiveCategory') == '1'){
			params.category = thisInstance.treeInstance.jstree("getCategory");
		}
		return params;
	},
});
