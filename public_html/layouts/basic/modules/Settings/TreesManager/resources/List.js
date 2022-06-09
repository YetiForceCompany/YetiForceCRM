/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js(
	'Settings_TreesManager_List_Js',
	{
		triggerCreate: function (url) {
			var selectedModule = jQuery('#moduleFilter').val();
			if (selectedModule.length > 0) {
				url += '&source_module=' + selectedModule;
			}
			window.location.href = url;
		}
	},
	{
		/*
		 * function to trigger delete record action
		 * @params: delete record url.
		 */
		DeleteRecord: function (deleteRecordActionUrl) {
			const self = this;
			app.showConfirmModal({
				title: app.vtranslate('LBL_DELETE_CONFIRMATION'),
				confirmedCallback: () => {
					AppConnector.request(deleteRecordActionUrl).done(function (data) {
						if (data.success == true) {
							var params = {
								text: app.vtranslate('JS_TREE_DELETED_SUCCESSFULLY')
							};
							Settings_Vtiger_Index_Js.showMessage(params);
							jQuery('#recordsCount').val('');
							jQuery('#totalPageCount').text('');
							self.getListViewRecords().done(function () {
								self.updatePagination();
							});
						} else {
							app.showNotify({
								text: data.error.message,
								type: 'error'
							});
						}
					});
				}
			});
		},

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

		/*
		 * function to load the contents from the url through pjax
		 */
		loadContents: function (url) {
			var aDeferred = jQuery.Deferred();
			AppConnector.requestPjax(url)
				.done(function (data) {
					jQuery('.contentsDiv').html(data);
					aDeferred.resolve(data);
				})
				.fail(function (error, err) {
					aDeferred.reject(error, err);
				});
			return aDeferred.promise();
		},
		/**
		 * Function to register events
		 */
		registerEvents: function () {
			this._super();
			this.registerFilterChangeEvent();
		}
	}
);
