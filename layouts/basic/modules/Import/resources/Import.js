/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 ************************************************************************************/

if (typeof (ImportJs) == 'undefined') {
	/*
	 * Namespaced javascript class for Import
	 */
	ImportJs = {
		toogleMergeConfiguration: function () {
			var mergeChecked = jQuery('#auto_merge').is(':checked');
			var duplicateMergeConfiguration = jQuery('#duplicates_merge_configuration');
			if (mergeChecked) {
				duplicateMergeConfiguration.show();
			} else {
				duplicateMergeConfiguration.hide();
			}
		},
		checkFileType: function () {
			var filePath = jQuery('#import_file').val();
			if (filePath != '') {
				var fileExtension = filePath.split('.').pop();
				jQuery('#type').val(fileExtension);
				ImportJs.handleFileTypeChange();
			}
		},
		handleFileTypeChange: function () {
			var fileType = jQuery('#type').val();
			var delimiterContainer = jQuery('#delimiter_container');
			var hasHeaderContainer = jQuery('#has_header_container');
			var xmlTpl = jQuery('#xml_tpl');
			var extension = jQuery('#zipExtension');

			switch(fileType) {
				case 'xml':
					delimiterContainer.hide();
					hasHeaderContainer.hide();
					xmlTpl.removeClass('hide');
					extension.addClass('hide');
					break;
				case 'zip':
					delimiterContainer.hide();
					hasHeaderContainer.hide();
					extension.removeClass('hide');
					break;
				default:
					delimiterContainer.show();
					hasHeaderContainer.show();
					extension.addClass('hide');
					xmlTpl.addClass('hide');
			}
		},
		uploadAndParse: function () {
			if (!ImportJs.validateFilePath())
				return false;
			if (!ImportJs.validateMergeCriteria())
				return false;
			return true;
		},
		registerImportClickEvent: function () {
			jQuery('#importButton').on('click', function (e) {
				var result = ImportJs.sanitizeAndSubmit()
				return result;
			});
		},
		validateFilePath: function () {
			var importFile = jQuery('#import_file');
			var filePath = importFile.val();
			if (jQuery.trim(filePath) == '') {
				var errorMessage = app.vtranslate('JS_IMPORT_FILE_CAN_NOT_BE_EMPTY');
				var params = {
					text: errorMessage,
					type: 'error'
				};
				Vtiger_Helper_Js.showMessage(params);
				importFile.focus();
				return false;
			}
			if (!ImportJs.uploadFilter("import_file", "csv|vcf|xml|zip")) {
				return false;
			}
			if (!ImportJs.uploadFileSize("import_file")) {
				return false;
			}
			return true;
		},
		uploadFilter: function (elementId, allowedExtensions) {
			var obj = jQuery('#' + elementId);
			if (obj) {
				var filePath = obj.val();
				var fileParts = filePath.toLowerCase().split('.');
				var fileType = fileParts[fileParts.length - 1];
				var validExtensions = allowedExtensions.toLowerCase().split('|');

				if (validExtensions.indexOf(fileType) < 0) {
					var errorMessage = app.vtranslate('JS_SELECT_FILE_EXTENSION') + '\n' + validExtensions;
					var params = {
						text: errorMessage,
						type: 'error'
					};
					Vtiger_Helper_Js.showMessage(params);
					obj.focus();
					return false;
				}
			}
			return true;
		},
		uploadFileSize: function (elementId) {
			var element = jQuery('#' + elementId);
			var importMaxUploadSize = element.closest('td').data('importUploadSize');
			var importMaxUploadSizeInMb = element.closest('td').data('importUploadSizeMb');
			var uploadedFileSize = element.get(0).files[0].size;
			if (uploadedFileSize > importMaxUploadSize) {
				var errorMessage = app.vtranslate('JS_UPLOADED_FILE_SIZE_EXCEEDS') + " " + importMaxUploadSizeInMb + " MB." + app.vtranslate('JS_PLEASE_SPLIT_FILE_AND_IMPORT_AGAIN');
				var params = {
					text: errorMessage,
					type: 'error'
				};
				Vtiger_Helper_Js.showMessage(params);
				return false;
			}
			return true;
		},
		validateMergeCriteria: function () {
			$mergeChecked = jQuery('#auto_merge').is(':checked');
			if ($mergeChecked) {
				var selectedOptions = jQuery('#selected_merge_fields option');
				if (selectedOptions.length == 0) {
					var errorMessage = app.vtranslate('JS_PLEASE_SELECT_ONE_FIELD_FOR_MERGE');
					var params = {
						text: errorMessage,
						'type': 'error'
					};
					Vtiger_Helper_Js.showMessage(params);
					return false;
				}
			}
			ImportJs.convertOptionsToJSONArray('#selected_merge_fields', '#merge_fields');
			return true;
		},
		convertOptionsToJSONArray: function (objName, targetObjName) {
			var obj = jQuery(objName);
			var arr = [];
			if (typeof (obj) != 'undefined' && obj[0] != '') {
				for (i = 0; i < obj[0].length; ++i) {
					arr.push(obj[0].options[i].value);
				}
			}
			if (targetObjName != 'undefined') {
				var targetObj = $(targetObjName);
				if (typeof (targetObj) != 'undefined')
					targetObj.val(JSON.stringify(arr));
			}
			return arr;
		},
		copySelectedOptions: function (source, destination) {

			var srcObj = jQuery(source);
			var destObj = jQuery(destination);

			if (typeof (srcObj) == 'undefined' || typeof (destObj) == 'undefined')
				return;

			for (i = 0; i < srcObj[0].length; i++) {
				if (srcObj[0].options[i].selected == true) {
					var rowFound = false;
					var existingObj = null;
					for (j = 0; j < destObj[0].length; j++) {
						if (destObj[0].options[j].value == srcObj[0].options[i].value) {
							rowFound = true;
							existingObj = destObj[0].options[j];
							break;
						}
					}

					if (rowFound != true) {
						var opt = $('<option selected>');
						opt.attr('value', srcObj[0].options[i].value);
						opt.text(srcObj[0].options[i].text);
						jQuery(destObj[0]).append(opt);
						srcObj[0].options[i].selected = false;
						rowFound = false;
					} else {
						if (existingObj != null)
							existingObj.selected = true;
					}
				}
			}
		},
		removeSelectedOptions: function (objName) {
			var obj = jQuery(objName);
			if (obj == null || typeof (obj) == 'undefined')
				return;

			for (i = obj[0].options.length - 1; i >= 0; i--) {
				if (obj[0].options[i].selected == true) {
					obj[0].options[i] = null;
				}
			}
		},
		sanitizeAndSubmit: function () {
			if (!ImportJs.sanitizeFieldMapping())
				return false;
			if (!ImportJs.validateCustomMap())
				return false;
			return true;
		},
		sanitizeFieldMapping: function () {
			var fieldsList = jQuery('.fieldIdentifier');
			var mappedFields = {};
			var inventoryMappedFields = {};
			var errorMessage;
			var params = {};
			var mappedDefaultValues = {};
			for (var i = 0; i < fieldsList.length; ++i) {
				var fieldElement = jQuery(fieldsList.get(i));
				var rowId = jQuery('[name=row_counter]', fieldElement).get(0).value;
				var selectElement = jQuery('select', fieldElement);
				var selectedFieldElement = selectElement.find('option:selected');
				var selectedFieldName = selectedFieldElement.val();
				var selectedFieldDefaultValueElement = jQuery('#' + selectedFieldName + '_defaultvalue', fieldElement);
				var defaultValue = '';
				if (selectedFieldDefaultValueElement.attr('type') == 'checkbox') {
					defaultValue = selectedFieldDefaultValueElement.is(':checked');
				} else {
					defaultValue = selectedFieldDefaultValueElement.val();
				}
				if (selectedFieldName != '') {
					if(selectElement.hasClass('inventory')){
						var stopImmediately = ImportJs.checkIfMappedFieldExist(selectedFieldName, inventoryMappedFields, selectedFieldElement);
						inventoryMappedFields[selectedFieldName] = rowId - 1;
					}else{
						var stopImmediately = ImportJs.checkIfMappedFieldExist(selectedFieldName, mappedFields, selectedFieldElement);
						mappedFields[selectedFieldName] = rowId - 1;
					}
					if(stopImmediately){
						return false;
					}
					if (defaultValue != '') {
						mappedDefaultValues[selectedFieldName] = defaultValue;
					}
				}
			}

			var mandatoryFields = JSON.parse(jQuery('#mandatory_fields').val());
			var moduleName = app.getModuleName();
			var missingMandatoryFields = [];
			for (var mandatoryFieldName in mandatoryFields) {
				if (mandatoryFieldName in mappedFields) {
					continue;
				} else {
					missingMandatoryFields.push('"' + mandatoryFields[mandatoryFieldName] + '"');
				}
			}
			if (missingMandatoryFields.length > 0) {
				errorMessage = app.vtranslate('JS_MAP_MANDATORY_FIELDS') + missingMandatoryFields.join(',');
				params = {
					text: errorMessage,
					'type': 'error'
				};
				Vtiger_Helper_Js.showMessage(params);
				return false;
			}
			jQuery('#field_mapping').val(JSON.stringify(mappedFields));
			jQuery('#inventory_field_mapping').val(JSON.stringify(inventoryMappedFields));
			jQuery('#default_values').val(JSON.stringify(mappedDefaultValues));
			return true;
		},
		checkIfMappedFieldExist: function (selectedFieldName, mappedFields, selectedFieldElement){
			if (selectedFieldName in mappedFields) {
				var errorMessage = app.vtranslate('JS_FIELD_MAPPED_MORE_THAN_ONCE') + " " + selectedFieldElement.data('label');
				var params = {
					text: errorMessage,
					'type': 'error'
				};
				Vtiger_Helper_Js.showMessage(params);
				return true;
			}
			return false;
		},
		validateCustomMap: function () {
			var errorMessage;
			var params = {};
			var saveMap = jQuery('#save_map').is(':checked');
			if (saveMap) {
				var mapName = jQuery('#save_map_as').val();
				if (jQuery.trim(mapName) == '') {
					errorMessage = app.vtranslate('JS_MAP_NAME_CAN_NOT_BE_EMPTY');
					params = {
						text: errorMessage,
						'type': 'error'
					};
					Vtiger_Helper_Js.showMessage(params);
					return false;
				}
				var mapOptions = jQuery('#saved_maps option');
				for (var i = 0; i < mapOptions.length; ++i) {
					var mapOption = jQuery(mapOptions.get(i));
					if (mapOption.html() == mapName) {
						errorMessage = app.vtranslate('JS_MAP_NAME_ALREADY_EXISTS');
						params = {
							text: errorMessage,
							'type': 'error'
						};
						Vtiger_Helper_Js.showMessage(params);
						return false;
					}
				}
			}
			return true;
		},
		loadSavedMap: function () {
			var selectedMapElement = jQuery('#saved_maps option:selected');
			var mapId = selectedMapElement.attr('id');
			var fieldsList = jQuery('.fieldIdentifier');
			var deleteMapContainer = jQuery('#delete_map_container');
			fieldsList.each(function (i, element) {
				var fieldElement = jQuery(element);
				jQuery('[name=mapped_fields]', fieldElement).val('');
			});
			if (mapId == -1) {
				deleteMapContainer.hide();
				return;
			}
			deleteMapContainer.show();
			var mappingString = selectedMapElement.val()
			if (mappingString == '')
				return;
			var mappingPairs = mappingString.split('&');
			var mapping = {};
			for (var i = 0; i < mappingPairs.length; ++i) {
				var mappingPair = mappingPairs[i].split('=');
				var header = mappingPair[0];
				header = header.replace(/\/eq\//g, '=');
				header = header.replace(/\/amp\//g, '&');
				mapping["'" + header + "'"] = mappingPair[1];
			}
			fieldsList.each(function (i, element) {
				var fieldElement = jQuery(element);
				var mappedFields = jQuery('[name=mapped_fields]', fieldElement);
				var rowId = jQuery('[name=row_counter]', fieldElement).get(0).value;
				var headerNameElement = jQuery('[name=header_name]', fieldElement).get(0);
				var headerName = jQuery(headerNameElement).html();
				if ("'" + headerName + "'" in mapping) {
					mappedFields.val(mapping["'" + headerName + "'"]);
				} else if (rowId in mapping) {
					mappedFields.val($rowId);
				}
				mappedFields.trigger('chosen:updated');
				ImportJs.loadDefaultValueWidget(fieldElement.attr('id'));
			});
		},
		deleteMap: function (module) {
			if (confirm(app.vtranslate('LBL_DELETE_CONFIRMATION'))) {
				var selectedMapElement = jQuery('#saved_maps option:selected');
				var mapId = selectedMapElement.attr('id');
				var status = jQuery('#status');
				status.show();
				var postData = {
					"module": module,
					"view": 'Import',
					"mode": 'deleteMap',
					"mapid": mapId
				}

				AppConnector.request(postData).then(
						function (data) {
							jQuery('#savedMapsContainer').html(data);
							status.hide();
							var parent = jQuery("#saved_maps");
							app.changeSelectElementView(parent);
						},
						function (error, err) {

						}
				);
			}
		},
		loadDefaultValueWidget: function (rowIdentifierId) {
			var affectedRow = jQuery('#' + rowIdentifierId);
			if (typeof affectedRow == 'undefined' || affectedRow == null)
				return;
			var selectedFieldElement = jQuery('[name=mapped_fields]', affectedRow).get(0);
			var selectedFieldName = jQuery(selectedFieldElement).val();
			var defaultValueContainer = jQuery(jQuery('[name=default_value_container]', affectedRow).get(0));
			var allDefaultValuesContainer = jQuery('#defaultValuesElementsContainer');
			if (defaultValueContainer.children.length > 0) {
				var copyOfDefaultValueWidget = jQuery(':first', defaultValueContainer).detach();
				copyOfDefaultValueWidget.appendTo(allDefaultValuesContainer);
			}
			var selectedFieldDefValueContainer = jQuery('#' + selectedFieldName + '_defaultvalue_container', allDefaultValuesContainer);
			var defaultValueWidget = selectedFieldDefValueContainer.detach();
			defaultValueWidget.appendTo(defaultValueContainer);
		},
		loadDefaultValueWidgetForMappedFields: function () {
			var fieldsList = jQuery('.fieldIdentifier');
			fieldsList.each(function (i, element) {
				var fieldElement = jQuery(element);
				var mappedFieldName = jQuery('[name=mapped_fields]', fieldElement).val();
				if (mappedFieldName != '') {
					ImportJs.loadDefaultValueWidget(fieldElement.attr('id'));
				}
			});

		},
		submitAction: function () {
			var form = jQuery('[name="importAdvanced"]');
			form.on('submit',function(){
				var progressIndicatorElement = jQuery.progressIndicator({
					'message': app.vtranslate('JS_SAVE_LOADER_INFO'),
					'position': 'html',
					'blockInfo': {
						'enabled': true
					}
				});
			});
		}
	}

	jQuery(document).ready(function () {
		ImportJs.toogleMergeConfiguration();
		ImportJs.submitAction();
		ImportJs.loadDefaultValueWidgetForMappedFields();
		ImportJs.registerImportClickEvent();
		app.registerEventForDatePickerFields(jQuery('.contentsDiv'));
	});
}
