/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js(
	'Settings_AutomaticAssignment_List_Js',
	{},
	{
		container: false,
		registerFilterChangeEvent: function () {
			var thisInstance = this;
			jQuery('#moduleFilter').on('change', function (e) {
				jQuery('#pageNumber').val('1');
				jQuery('#pageToJump').val('1');
				jQuery('#orderBy').val('');
				jQuery('#sortOrder').val('');
				var params = {
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					sourceModule: jQuery(e.currentTarget).val()
				};
				//Make the select all count as empty
				jQuery('#recordsCount').val('');
				//Make total number of pages as empty
				jQuery('#totalPageCount').text('');
				thisInstance.getListViewRecords(params).done(function (data) {
					thisInstance.updatePagination();
				});
			});
		},
		getContainer: function () {
			if (this.container == false) {
				this.container = jQuery('div.contentsDiv');
			}
			return this.container;
		},
		registerEvents: function () {
			this._super();
			this.registerFilterChangeEvent();
		}
	}
);
