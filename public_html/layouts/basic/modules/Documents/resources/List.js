/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

Vtiger_List_Js("Documents_List_Js", {
	massMove: function (url) {
		var listInstance = Vtiger_List_Js.getInstance();
		var validationResult = listInstance.checkListRecordSelected();
		if (validationResult != true) {
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var cvId = listInstance.getCurrentCvId();
			var postData = {
				"selected_ids": selectedIds,
				"excluded_ids": excludedIds,
				"viewname": cvId
			};

			var searchValue = listInstance.getAlphabetSearchValue();

			if (searchValue.length > 0) {
				postData['search_key'] = listInstance.getAlphabetSearchField();
				postData['search_value'] = searchValue;
				postData['operator'] = "s";
			}

			var params = {
				"url": url,
				"data": postData
			};
			var progressIndicatorElement = jQuery.progressIndicator();
			AppConnector.request(params).done(function (data) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				var callBackFunction = function (data) {
					listInstance.moveDocuments().done(function (data) {
						if (data) {
							var result = data.result;
							if (result.success) {
								app.hideModalWindow();
								Vtiger_Helper_Js.showPnotify({
									title: app.vtranslate('JS_MOVE_DOCUMENTS'),
									text: result.message,
									delay: '2000',
									type: 'success'
								});
								var urlParams = listInstance.getDefaultParams();
								listInstance.getListViewRecords(urlParams);
							} else {
								Vtiger_Helper_Js.showPnotify({
									title: app.vtranslate('JS_OPERATION_DENIED'),
									text: result.message,
									delay: '2000',
									type: 'error'
								});
							}
						}
					});
				};
				app.showModalWindow(data, callBackFunction);
			});
		} else {
			listInstance.noRecordSelectedAlert();
		}

	}

}, {
	moveDocuments: function () {
		var aDeferred = jQuery.Deferred();
		jQuery('#moveDocuments').on('submit', function (e) {
			var formData = jQuery(e.currentTarget).serializeFormData();
			AppConnector.request(formData).done(function (data) {
				aDeferred.resolve(data);
			}).fail(function (textStatus, errorThrown) {
				aDeferred.reject(textStatus, errorThrown);
			});
			e.preventDefault();
		});
		return aDeferred.promise();
	},
	registerDeleteFilterClickEvent: function () {
		var thisInstance = this;

		var listViewFilterBlock = this.getFilterBlock();
		if (listViewFilterBlock != false) {
			//used mouseup event to stop the propagation of customfilter select change event.
			listViewFilterBlock.on('mouseup', '.js-filter-delete', function (event) {
				//to close the dropdown
				event.stopPropagation();
				thisInstance.getFilterSelectElement().data('select2').close();
				var liElement = jQuery(event.currentTarget).closest('.select2-results__option');
				var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE');
				if (liElement.hasClass('folderOption')) {
					if (liElement.find('.js-filter-delete').hasClass('dull')) {
						Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_FOLDER_IS_NOT_EMPTY'));
						return;
					} else {
						Vtiger_Helper_Js.showConfirmationBox({'message': message}).done(function (e) {
							var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
							var folderId = currentOptionElement.data('folderid');
							var params = {
								module: app.getModuleName(),
								mode: 'delete',
								action: 'Folder',
								folderid: folderId
							};
							AppConnector.request(params).done(function (data) {
								if (data.success) {
									currentOptionElement.remove();
									thisInstance.getFilterSelectElement().trigger('change');
								}
							});
						});
					}

				} else {
					Vtiger_Helper_Js.showConfirmationBox({'message': message}).done(function (e) {
						var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
						AppConnector.requestForm(currentOptionElement.data('deleteurl'));
					});
				}
			});
		}
	}
});
