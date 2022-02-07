/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_ListPreview_Js(
	'Accounts_ListPreview_Js',
	{},
	{
		/**
		 * Sets account hierarchy
		 */
		registerHierarchyRecordCount: function () {
			const iframe = $('.listPreviewframe');
			iframe.on('load', function () {
				var contents = iframe.contents();
				var hierarchyButton = contents.find('.detailViewTitle .hierarchy');
				if (hierarchyButton) {
					AppConnector.request({
						module: app.getModuleName(),
						action: 'RelationAjax',
						record: contents.find('#recordId').val(),
						mode: 'getHierarchyCount'
					}).done(function (response) {
						if (response.success) {
							contents.find('.detailViewTitle .hierarchy .badge').html(response.result);
						}
					});
				}
			});
		},
		/**
		 * Executes event listener.
		 * @param {jQuery} container - current container for reference.
		 */
		postLoadListViewRecordsEvents: function (container) {
			this._super(container);
			this.registerHierarchyRecordCount();
		},
		/**
		 * Registers ListPreview's events.
		 */
		registerEvents: function () {
			this._super();
			this.registerHierarchyRecordCount();
		}
	}
);
