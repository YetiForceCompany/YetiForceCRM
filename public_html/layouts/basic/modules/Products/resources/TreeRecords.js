/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_TreeRecords_Js(
	'Products_TreeRecords_Js',
	{},
	{
		getRecordsParams: function (container) {
			let selected = [],
				category = [];
			$.each(this.treeInstance.jstree('get_selected', true), function (index, value) {
				if (value.original.isrecord) {
					selected.push(value.text);
				} else {
					category.push(value.original.record_id);
				}
			});
			return {
				module: app.getModuleName(),
				view: app.getViewName(),
				branches: selected,
				filter: container.find('#moduleFilter').val(),
				category: category
			};
		}
	}
);
