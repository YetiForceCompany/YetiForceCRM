/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
Vtiger_List_Js("Calendar_List_Js", {
	triggerImportAction: function (importUrl) {
		var progressIndicatorElement = jQuery.progressIndicator();
		AppConnector.request(importUrl).then(
				function (data) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					if (data) {
						app.showModalWindow(data, function (data) {
							jQuery('#ical_import').validationEngine(app.validationEngineOptions);
						});
					}
				}
		);
	},
	triggerExportAction: function (exportActionUrl) {
		var progressIndicatorElement = jQuery.progressIndicator();
		var listInstance = Vtiger_List_Js.getInstance();
		// Compute selected ids, excluded ids values, along with cvid value and pass as url parameters
		var selectedIds = listInstance.readSelectedIds(true);
		var excludedIds = listInstance.readExcludedIds(true);
		var cvId = listInstance.getCurrentCvId();
		var pageNumber = jQuery('#pageNumber').val();
		if ('undefined' === typeof cvId)
			exportActionUrl += '&selected_ids=' + selectedIds + '&excluded_ids=' + excludedIds + '&page=' + pageNumber;
		else
			exportActionUrl += '&selected_ids=' + selectedIds + '&excluded_ids=' + excludedIds + '&viewname=' + cvId + '&page=' + pageNumber;
		var listViewInstance = Vtiger_List_Js.getInstance();
		if (listViewInstance.getListSearchInstance()) {
			var searchValue = listViewInstance.getListSearchInstance().getAlphabetSearchValue();
			exportActionUrl += "&search_params=" + JSON.stringify(listViewInstance.getListSearchInstance().getListSearchParams());
			if ((typeof searchValue != "undefined") && (searchValue.length > 0)) {
				exportActionUrl += '&search_key=' + listViewInstance.getListSearchInstance().getAlphabetSearchField();
				exportActionUrl += '&search_value=' + searchValue;
				exportActionUrl += '&operator=s';
			}
		}
		AppConnector.request(exportActionUrl).then(
				function (data) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					if (data) {
						app.showModalWindow(data, function (data) {
						});
					}
				}
		);
	}
}, {});
