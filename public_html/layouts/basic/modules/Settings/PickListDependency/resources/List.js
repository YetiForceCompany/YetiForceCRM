/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js(
	'Settings_PickListDependency_List_Js',
	{},
	{
		container: false,
		registerFilterChangeEvent: function () {
			let container = this.getContainer();
			container.find('#moduleFilter').on('change', (e) => {
				container.find('#pageNumber').val('1');
				container.find('#pageToJump').val('1');
				container.find('#orderBy').val('');
				container.find('#sortOrder').val('');
				let params = {
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					sourceModule: $(e.currentTarget).val()
				};
				container.find('#recordsCount').val('');
				container.find('#totalPageCount').text('');
				this.getListViewRecords(params).done(() => {
					this.updatePagination();
				});
			});
		},
		getContainer: function () {
			if (this.container === false) {
				this.container = $('div.contentsDiv');
			}
			return this.container;
		},
		registerEvents: function () {
			this._super();
			this.registerFilterChangeEvent();
		}
	}
);
