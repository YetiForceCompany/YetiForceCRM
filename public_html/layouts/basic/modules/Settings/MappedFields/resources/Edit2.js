/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_MappedFields_Edit_Js(
	'Settings_MappedFields_Edit2_Js',
	{},
	{
		step2Container: false,
		advanceFilterInstance: false,
		restictName: ['_csrf', 'mode', 'module', 'parent', 'record', 'view'],
		init: function () {
			this.initialize();
		},
		/**
		 * Function to get the container which holds all the reports step1 elements
		 * @return jQuery object
		 */
		getContainer: function () {
			return this.step2Container;
		},
		/**
		 * Function to set the reports step1 container
		 * @params : element - which represents the reports step1 container
		 * @return : current instance
		 */
		setContainer: function (element) {
			this.step2Container = element;
			return this;
		},
		/**
		 * Function  to intialize the reports step1
		 */
		initialize: function (container) {
			if (typeof container === 'undefined') {
				container = jQuery('#mf_step2');
			}
			if (container.is('#mf_step2')) {
				this.setContainer(container);
			} else {
				this.setContainer(jQuery('#mf_step2'));
			}
		},
		submit: function () {
			var aDeferred = jQuery.Deferred();
			var form = this.getContainer();
			var formData = form.serializeFormData();
			var saveData = this.getData(formData);
			saveData.record = formData.record;
			this.validationMappingFields().done(function (data) {
				if (data) {
					var progressIndicatorElement = jQuery.progressIndicator({
						position: 'html',
						blockInfo: {
							enabled: true
						}
					});
					app.saveAjax('step2', saveData).done(function (data) {
						if (data.success == true) {
							Settings_Vtiger_Index_Js.showMessage({
								text: app.vtranslate('JS_MF_SAVED_SUCCESSFULLY')
							});
							var mfRecordElement = jQuery('[name="record"]', form);
							if (mfRecordElement.val() === '') {
								mfRecordElement.val(data.result.id);
								formData['record'] = data.result.id;
							}
							formData['record'] = data.result.id;
							AppConnector.request(formData)
								.done(function (data) {
									form.hide();
									progressIndicatorElement.progressIndicator({
										mode: 'hide'
									});
									aDeferred.resolve(data);
								})
								.fail(function (error, err) {
									app.errorLog(error, err);
								});
						}
					});
				} else {
					aDeferred.resolve(false);
				}
			});

			return aDeferred.promise();
		},
		getData: function (data) {
			var mappingData = [];
			var mappingConditions = [];
			for (var i in data) {
				var name = i.split('][');
				if (name.length == 2) {
					if (name[1] != 'default]' && data[i] != 0) mappingData[i] = data[i];
					else if (name[1] == 'default]') mappingData[i] = jQuery.isArray(data[i]) ? data[i].join(',') : data[i].trim();
				} else if (name.length != 2 && jQuery.inArray(i, this.restictName) == -1) {
					mappingConditions[i] = data[i];
				}
			}
			mappingData = jQuery.extend({}, mappingData);
			mappingConditions = jQuery.extend({}, mappingConditions);
			return { mapping: mappingData, otherConditions: JSON.stringify(mappingConditions) };
		},
		registerCancelStepClickEvent: function (form) {
			jQuery('button.cancelLink', form).on('click', function () {
				window.history.back();
			});
		},
		registerEvents: function () {
			var container = this.getContainer();

			var opts = app.validationEngineOptions;
			// to prevent the page reload after the validation has completed
			opts['onValidationComplete'] = function (form, valid) {
				//returns the valid status
				return valid;
			};
			opts['promptPosition'] = 'bottomRight';
			container.validationEngine(opts);
			App.Fields.Picklist.showSelect2ElementView(container.find('.select2'));
			this.registerCancelStepClickEvent(container);
			this.registerEventsForEditView();
		},
		/**
		 * Function to register event for adding new convert to field mapping
		 */
		registerEventForAddingNewMapping: function () {
			var thisInstance = this;
			jQuery('#addMapping').on('click', function (e) {
				var mappingToGenerateTable = jQuery('#mappingToGenerate');
				var lastSequenceNumber = mappingToGenerateTable.find('tr:not(.d-none)[sequence-number]').last();
				var newSequenceNumber = thisInstance.getSequenceNumber(lastSequenceNumber) + 1;
				var newMapping = jQuery('.newMapping').clone(true, true);
				newMapping.attr('sequence-number', newSequenceNumber);
				newMapping.find('select.sourceFields.newSelect').attr('name', 'mapping[' + newSequenceNumber + '][source]');
				newMapping.find('select.targetFields.newSelect').attr('name', 'mapping[' + newSequenceNumber + '][target]');
				newMapping.find('input.mappingType').attr('name', 'mapping[' + newSequenceNumber + '][type]');
				newMapping.removeClass('d-none newMapping');
				newMapping.appendTo(mappingToGenerateTable);
				newMapping.find('.newSelect').removeClass('newSelect').addClass('select2');
				var select2Elements = newMapping.find('.select2');
				App.Fields.Picklist.showSelect2ElementView(select2Elements);
				jQuery('select.targetFields', newMapping).trigger('change', false);
				thisInstance.loadDefaultValueWidgetForMappedFields(newMapping.find('.select2'));
			});
		},
		getSequenceNumber: function (element) {
			let sequenceNumber;
			if (element.length) {
				sequenceNumber = element.attr('sequence-number');
			} else {
				sequenceNumber = 0;
			}
			return parseInt(sequenceNumber);
		},
		/**
		 * Function to register on change event for select2 element
		 */
		registerOnChangeEventForSourceModule: function () {
			var form = this.getContainer();
			form.on('change', '.sourceFields', function (e) {
				var element = jQuery(e.currentTarget);
				var container = jQuery(element.closest('tr'));
				var selectedValue = element.val();
				var selectedOption = element.find('option[value="' + selectedValue + '"]');
				var selectedDataType = selectedOption.data('type');
				var mappingType = selectedOption.data('mappingtype');
				var fieldsSelectElement = container.find('select.targetFields.select2');
				var fieldsBasedOnType = form.find('.newMapping').find('.targetFields').children().clone(true, true);
				var options = jQuery('<div></div>');
				container.find('.mappingType').val(mappingType);
				fieldsBasedOnType.each(function (i, e) {
					var element = jQuery(e);
					if (element.is('option') && (element.data('type') == selectedDataType || element.data('type') == 'none')) {
						options.append(element);
					} else if (element.is('optgroup') && element.find('[data-type="' + selectedDataType + '"]').length > 0) {
						element.children().each(function (q, k) {
							var option = jQuery(k);
							if (option.data('type') !== selectedDataType) {
								option.remove();
							}
						});
						options.append(element);
					}
				});

				container
					.find('.selectedFieldDataType')
					.html(selectedOption.data('type-name') ? selectedOption.data('type-name') : '');
				fieldsSelectElement.html(options.children());

				fieldsSelectElement.trigger('change', false);
			});
		},
		/**
		 * Function to register event to delete mapping
		 */
		registerEventToDeleteMapping: function () {
			var form = this.getContainer();
			form.on('click', '.deleteMapping', function (e) {
				var element = jQuery(e.currentTarget);
				var trContainer = element.closest('tr');
				trContainer.remove();
			});
		},
		loadDefaultValueWidget: function (element) {
			var thisInstance = this;
			var id = element.val() + '_defaultvalue';
			var affectedRow = jQuery('#defaultValuesElementsContainer').find('#' + id);
			var dafeultTd = element.closest('td').next();
			var defaultValueElement = dafeultTd.find('input.default');
			var defaultValue = '';
			if (defaultValueElement.length) {
				defaultValue = defaultValueElement.val();
			}
			dafeultTd.children().remove();
			var exist = jQuery('#mappingToGenerate').find('#' + id);
			if (affectedRow.length === 0 || !element.val() || exist.length > 0) {
				return;
			}
			var seqNumber = thisInstance.getSequenceNumber(element.closest('tr'));
			var copyOfDefaultValue = affectedRow.clone(true, true);
			copyOfDefaultValue.prop('disabled', false).attr('name', 'mapping[' + seqNumber + '][default]');
			if (copyOfDefaultValue.is(':checkbox') && defaultValue) {
				copyOfDefaultValue.prop('checked', true);
			} else if (defaultValue) {
				defaultValue =
					typeof copyOfDefaultValue.attr('multiple') !== 'undefined' ? defaultValue.split(',') : defaultValue;
				copyOfDefaultValue.val(defaultValue);
			}
			copyOfDefaultValue.appendTo(dafeultTd);
			App.Fields.Picklist.showSelect2ElementView(dafeultTd.find('select'));
		},
		loadDefaultValueWidgetForMappedFields: function (fieldsList) {
			var thisInstance = this;
			fieldsList.each(function (i, element) {
				thisInstance.loadDefaultValueWidget(jQuery(this));
			});
			fieldsList.on('change', function (e) {
				var element = jQuery(e.currentTarget);
				thisInstance.loadDefaultValueWidget(element);
			});
		},
		/**
		 * Function to register events for edit view of fields mapping
		 */
		registerEventsForEditView: function () {
			this.registerEventForAddingNewMapping();
			this.registerOnChangeEventForSourceModule();
			this.registerEventToDeleteMapping();
			this.loadDefaultValueWidgetForMappedFields(jQuery('select.targetFields:not(.newSelect)'));
		},
		/*
		 * Function to register on chnage event of target module
		 */
		validationMappingFields: function () {
			let aDeferred = jQuery.Deferred(),
				mappingTable = jQuery('#mappingToGenerate tr:not(.d-none)');

			mappingTable.each(function (i, e) {
				let breakSave = false,
					targetField = jQuery(this).find('.targetFields :selected'),
					moduleName;
				if (mappingTable.find('.targetFields option[value="' + targetField.val() + '"]:selected').length > 1) {
					moduleName = jQuery('.targetModuleName').text();
					breakSave = moduleName + ': ' + targetField.text();
				}
				if (breakSave) {
					let warningMessage = breakSave + ' <br />' + app.vtranslate('JS_IS_ALREADY_BEEN_MAPPED'),
						notificationParams = {
							text: warningMessage,
							type: 'error'
						};
					Settings_Vtiger_Index_Js.showMessage(notificationParams);
					aDeferred.resolve(false);
					return false;
				}
			});
			aDeferred.resolve(true);
			return aDeferred.promise();
		}
	}
);
