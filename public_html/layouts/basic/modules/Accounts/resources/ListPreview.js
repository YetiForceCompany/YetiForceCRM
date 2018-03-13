/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
Vtiger_ListPreview_Js("Accounts_ListPreview_Js", {}, {
	/**
	 * Sets account hierarchy
	 */
	registerHierarchyRecordCount: function () {
		const iframe = $(".listPreviewframe");
		iframe.on('load', function () {
			var thisInstance = this;
			var hierarchyButton = $(thisInstance).contents().find(".detailViewTitle .hierarchy");
			if (hierarchyButton) {
				var params = {
					module: app.getModuleName(),
					action: 'RelationAjax',
					record: app.getRecordId(),
					record: $(this).contents().find("#recordId").val(),
					mode: 'getHierarchyCount',
				};
				AppConnector.request(params).then(function (response) {
					if (response.success) {
						$(thisInstance).contents().find(".detailViewTitle .hierarchy .badge").html(response.result);
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
});
