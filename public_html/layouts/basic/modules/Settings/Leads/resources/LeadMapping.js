/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 *************************************************************************************/
'use strict';

jQuery.Class(
	'Settings_LeadMapping_Js',
	{
		/**
		 * Function to register event to edit leads mapping
		 */
		triggerEdit: function (editUrl) {
			var aDeferred = jQuery.Deferred();
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});

			AppConnector.request(editUrl)
				.done(function (data) {
					var detailContentsHolder = jQuery('.contentsDiv');
					detailContentsHolder.html(data);
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					var leadMappingInstance = new Settings_LeadMapping_Js();
					leadMappingInstance.registerEventsForEditView();
				})
				.fail(function (error) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				});
			return aDeferred.promise();
		},

		/**
		 * Function to register event for delete lead mapping
		 */
		triggerDelete: function (event, url) {
			var element = jQuery(event.currentTarget);
			var mappingContainer = element.closest('.listViewEntries');
			var mappingId = mappingContainer.data('cfmid');
			var deleteUrl = url + '&mappingId=' + mappingId;
			AppConnector.request(deleteUrl).done(function (data) {
				var message = data.result[0];
				var params = {
					text: message
				};
				if (data.success) {
					mappingContainer.remove();
				} else {
					params['type'] = 'error';
				}
				Settings_Vtiger_Index_Js.showMessage(params);
			});
		}
	},
	{
		/**
		 * Function to register events for edit view of leads mapping
		 */
		registerEventsForEditView: function () {
			var form = jQuery('#leadsMapping');
			var select2Elements = form.find('.select2');
			App.Fields.Picklist.showSelect2ElementView(select2Elements);
			this.registerEventForAddingNewMapping();
			this.registerOnChangeEventForSourceModule();
			this.registerEventToDeleteMapping();
			this.registerEventForFormSubmit();
			this.registerOnChangeEventOfTargetModule();
			jQuery('select.accountsFields.select2,select.contactFields.select2').trigger('change', false);
		},

		/**
		 * Function to register event for adding new convert to lead mapping
		 */
		registerEventForAddingNewMapping: function () {
			jQuery('#addMapping').on('click', function (e) {
				var convertLeadMappingTable = jQuery('#convertLeadMapping');
				var lastSequenceNumber = convertLeadMappingTable
					.find('tr:not(.newMapping):last[sequence-number]')
					.attr('sequence-number');
				var newSequenceNumber = parseInt(lastSequenceNumber) + 1;
				var newMapping = jQuery('.newMapping').clone(true, true);
				newMapping.attr('sequence-number', newSequenceNumber);
				newMapping.find('select.leadsFields.newSelect').attr('name', 'mapping[' + newSequenceNumber + '][lead]');
				newMapping.find('select.accountsFields.newSelect').attr('name', 'mapping[' + newSequenceNumber + '][account]');
				newMapping.find('select.contactFields.newSelect').attr('name', 'mapping[' + newSequenceNumber + '][contact]');
				newMapping.removeClass('d-none newMapping');
				newMapping.appendTo(convertLeadMappingTable);
				newMapping.find('.newSelect').removeClass('newSelect').addClass('select2');
				var select2Elements = newMapping.find('.select2');
				App.Fields.Picklist.showSelect2ElementView(select2Elements);
				jQuery('select.accountsFields.select2,select.contactFields.select2', newMapping).trigger('change', false);
			});
		},

		/**
		 * Function to register on change event for select2 element
		 */
		registerOnChangeEventForSourceModule: function () {
			var form = jQuery('#leadsMapping');
			form.on('change', '.leadsFields', function (e) {
				var element = jQuery(e.currentTarget);
				var container = jQuery(element.closest('tr'));
				var selectedValue = element.val();
				var selectedOption = element.find('option[value="' + selectedValue + '"]');
				var selectedDataType = selectedOption.data('type');
				var accountFieldsSelectElement = container.find('select.accountsFields.select2');
				var contactFieldsSelectElement = container.find('select.contactFields.select2');

				var accountFieldsBasedOnType = form
					.find('.newMapping')
					.find('.accountsFields')
					.children()
					.filter('[data-type="' + selectedDataType + '"]')
					.clone(true, true);
				var contactFieldsBasedOnType = form
					.find('.newMapping')
					.find('.contactFields')
					.children()
					.filter('option[data-type="' + selectedDataType + '"]')
					.clone(true, true);
				selectedDataType = app.vtranslate('JS_' + selectedDataType);
				container.find('.selectedFieldDataType').html(selectedDataType);

				var noneValue = app.vtranslate('JS_NONE');
				accountFieldsSelectElement.html(accountFieldsBasedOnType);
				contactFieldsSelectElement.html(contactFieldsBasedOnType);

				if (selectedDataType != 'None') {
					accountFieldsSelectElement.prepend(
						'<option data-type="' +
							noneValue +
							'" label="' +
							noneValue +
							'" value="0" selected>' +
							noneValue +
							'</option>'
					);
					contactFieldsSelectElement.prepend(
						'<option data-type="' +
							noneValue +
							'" label="' +
							noneValue +
							'" value="0" selected>' +
							noneValue +
							'</option>'
					);
				}

				accountFieldsSelectElement.trigger('change');
				contactFieldsSelectElement.trigger('change');
			});
		},

		/**
		 * Function to register event to delete mapping
		 */
		registerEventToDeleteMapping: function () {
			var form = jQuery('#leadsMapping');
			form.on('click', '.deleteMapping', function (e) {
				var element = jQuery(e.currentTarget);
				var mappingContainer = element.closest('tr');
				var mappingContainerSequenceNumber = mappingContainer.attr('sequence-number');
				var deletableName = 'mapping[' + mappingContainerSequenceNumber + '][deletable]';
				mappingContainer.prepend('<input type="hidden" name="' + deletableName + '" />');
				mappingContainer.data('deletable', true).hide('slow');
				var params = {
					text: app.vtranslate('JS_MAPPING_DELETED_SUCCESSFULLY')
				};
				Settings_Vtiger_Index_Js.showMessage(params);
			});
		},

		/**
		 * Function to handle edit view convert lead form submit
		 */
		registerEventForFormSubmit: function () {
			jQuery('#leadsMapping').on('submit', function (e) {
				e.preventDefault();
				var mappingDetails = jQuery(this).serializeFormData();
				var params = {
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					action: 'MappingSave',
					mapping: mappingDetails
				};

				var progressIndicatorElement = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});

				AppConnector.request(params)
					.done(function (data) {
						if (data.success) {
							var detailViewParams = {
								module: app.getModuleName(),
								parent: app.getParentModuleName(),
								view: 'MappingDetail'
							};
							AppConnector.request(detailViewParams).done(function (data) {
								var detailContentsHolder = jQuery('.contentsDiv');
								detailContentsHolder.html(data);
								progressIndicatorElement.progressIndicator({ mode: 'hide' });
							});
						}
						let notifyParams;
						if (!data.result.status) {
							notifyParams = {
								title: app.vtranslate('JS_INVALID_MAPPING'),
								text: data.result,
								type: 'error'
							};
						} else {
							notifyParams = {
								title: app.vtranslate('JS_MAPPING_SAVED_SUCCESSFULLY'),
								type: 'info'
							};
						}
						app.showNotify(notifyParams);
					})
					.fail(function (error) {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
					});
			});
		},

		/**
		 * Function to register on chnage event of target module
		 */
		registerOnChangeEventOfTargetModule: function () {
			var form = jQuery('#leadsMapping');
			form.on('change', 'select.accountsFields.select2,select.contactFields.select2', function (e, executeChange) {
				if (typeof executeChange === 'undefined') {
					executeChange = true;
				}
				var selectElement = jQuery(e.currentTarget);
				var selectedValue = selectElement.children().filter('option:selected').text();
				var selectedOptionId = selectElement.children().filter('option:selected').val();
				var mappingContainer = selectElement.closest('tr');

				var duplicateOption = false;
				var existingIdElement;
				if (selectedOptionId == '0') {
					selectedOptionId = 'false';
				}

				if (!executeChange || selectedOptionId == 'false') {
					selectElement.attr('selectedId', selectedOptionId);
					return;
				}

				//check for source module field
				var sourceModuleSelect = mappingContainer.find('select.leadsFields.select2');
				var sourceModuleSelectedField = sourceModuleSelect.val();
				var sourceModuleSelectedFieldLabel = sourceModuleSelect
					.find('option[value="' + sourceModuleSelectedField + '"]')
					.text();
				if (sourceModuleSelectedField == '0') {
					duplicateOption = true;
					var errorParams = {
						text:
							app.vtranslate('JS_CANT_MAP') +
							' ' +
							selectedValue +
							' ' +
							app.vtranslate('JS_WITH') +
							' ' +
							sourceModuleSelectedFieldLabel,
						type: 'error'
					};
					Settings_Vtiger_Index_Js.showMessage(errorParams);
				}

				if (selectElement.hasClass('accountsFields')) {
					existingIdElement = jQuery('select.accountsFields.select2[selectedid="' + selectedOptionId + '"]');
				} else {
					existingIdElement = jQuery('select.contactFields.select2[selectedid="' + selectedOptionId + '"]');
				}

				if (existingIdElement.length > 0) {
					duplicateOption = true;
				}

				if (duplicateOption) {
					var selectedFieldId = selectElement.attr('selectedid');
					if (selectedFieldId == 'false') {
						selectElement.attr('selectedId', 'false');
					} else if (selectedFieldId != 'false') {
						selectElement.attr('selectedId', selectedFieldId);
					}
					var warningMessage = selectedValue + ' ' + app.vtranslate('JS_IS_ALREADY_BEEN_MAPPED');
					var notificationParams = {
						text: warningMessage,
						type: 'error'
					};
					Settings_Vtiger_Index_Js.showMessage(notificationParams);
					selectElement.val(0).trigger('change');
				} else {
					selectElement.attr('selectedId', selectedOptionId);
				}
			});
		}
	}
);
