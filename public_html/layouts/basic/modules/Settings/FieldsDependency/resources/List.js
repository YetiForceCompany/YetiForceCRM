/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js(
	'Settings_FieldsDependency_List_Js',
	{},
	{
		registerFilterChangeEvent: function () {
			var thisInstance = this;
			$('#moduleFilter').on('change', function (e) {
				$('#pageNumber').val('1');
				$('#pageToJump').val('1');
				var params = {
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					sourceModule: $(e.currentTarget).val()
				};
				$('#recordsCount').val('');
				$('#totalPageCount').text('');
				thisInstance.getListViewRecords(params).done(function (data) {
					thisInstance.updatePagination();
				});
			});
		},
		registerEvents: function () {
			this._super();
			this.registerFilterChangeEvent();
		}
	}
);
