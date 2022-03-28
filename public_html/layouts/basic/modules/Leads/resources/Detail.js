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

Vtiger_Detail_Js(
	'Leads_Detail_Js',
	{
		//cache will store the convert lead data(Model)
		cache: {},
		//Holds detail view instance
		detailCurrentInstance: false,
		/*
		 * function to trigger Convert Lead action
		 * @param: Convert Lead url, currentElement.
		 */
		convertLead: function (convertLeadUrl, buttonElement) {
			var instance = Leads_Detail_Js.detailCurrentInstance;
			//Initially clear the elements to overwtite earliear cache
			instance.convertLeadContainer = false;
			instance.convertLeadForm = false;
			instance.convertLeadModules = false;
			if (jQuery.isEmptyObject(Leads_Detail_Js.cache)) {
				AppConnector.request(convertLeadUrl).done(
					function (data) {
						if (data) {
							Leads_Detail_Js.cache = data;
							instance.displayConvertLeadModel(data, buttonElement);
						}
					},
					function (error, err) {}
				);
			} else {
				instance.displayConvertLeadModel(Leads_Detail_Js.cache, buttonElement);
			}
		}
	},
	{
		//Contains the convert lead form
		convertLeadForm: false,
		//contains the convert lead container
		convertLeadContainer: false,
		//contains all the checkbox elements of modules
		convertLeadModules: false,
		//constructor
		init: function () {
			this._super();
			Leads_Detail_Js.detailCurrentInstance = this;
		},
		/*
		 * function to disable the Convert Lead button
		 */
		disableConvertLeadButton: function (button) {
			jQuery(button).attr('disabled', 'disabled');
		},
		/*
		 * function to enable the Convert Lead button
		 */
		enableConvertLeadButton: function (button) {
			jQuery(button).removeAttr('disabled');
		},
		/*
		 * function to enable all the input and textarea elements
		 */
		removeDisableAttr: function (moduleBlock) {
			moduleBlock.find('input,textarea,select').removeAttr('disabled');
		},
		/*
		 * function to disable all the input and textarea elements
		 */
		addDisableAttr: function (moduleBlock) {
			moduleBlock.find('input,textarea,select').attr('disabled', 'disabled');
		},
		/*
		 * function to display the convert lead model
		 * @param: data used to show the model, currentElement.
		 */
		displayConvertLeadModel: function (data, buttonElement) {
			var instance = this;
			var errorElement = jQuery(data).find('#convertLeadError');
			if (errorElement.length != '0') {
				var errorMsg = errorElement.val();
				var errorTitle = jQuery(data).find('#convertLeadErrorTitle').val();
				var params = {
					title: errorTitle,
					text: errorMsg,
					addclass: 'convertLeadNotify',
					width: '35%',
					type: 'error',
					pnotify_after_open: function () {
						instance.disableConvertLeadButton(buttonElement);
					},
					pnotify_after_close: function () {
						instance.enableConvertLeadButton(buttonElement);
					}
				};
				app.showNotify(params);
			} else {
				var callBackFunction = function (data) {
					var editViewObj = Vtiger_Edit_Js.getInstance();
					jQuery(data).find('.fieldInfo').collapse({
						parent: '#leadAccordion',
						toggle: false
					});
					editViewObj.registerBasicEvents(data);
					var checkBoxElements = instance.getConvertLeadModules();
					jQuery.each(checkBoxElements, function (index, element) {
						instance.checkingModuleSelection(element);
					});
					instance.registerForReferenceField();
					instance.registerConvertLeadEvents();
					instance.getConvertLeadForm().validationEngine(app.validationEngineOptions);
					instance.registerConvertLeadSubmit();
				};
				app.showModalWindow(
					data,
					function (data) {
						if (typeof callBackFunction == 'function') {
							callBackFunction(data);
						}
					},
					{
						'text-align': 'left'
					}
				);
			}
		},
		/*
		 * function to check which module is selected
		 * to disable or enable all the elements with in the block
		 */
		checkingModuleSelection: function (element) {
			var instance = this;
			var module = jQuery(element).val();
			var moduleBlock = jQuery(element)
				.closest('.convertLeadModules')
				.find('#' + module + '_FieldInfo');
			if (jQuery(element).is(':checked')) {
				instance.removeDisableAttr(moduleBlock);
			} else {
				instance.addDisableAttr(moduleBlock);
			}
		},
		registerForReferenceField: function () {
			var container = this.getConvertLeadContainer();
			var referenceField = jQuery('.reference', container);
			if (referenceField.length > 0) {
				jQuery('#AccountsModule').attr('readonly', 'readonly');
			}
		},
		/*
		 * function to register Convert Lead Events
		 */
		registerConvertLeadEvents: function () {
			var container = this.getConvertLeadContainer();
			var instance = this;
			//Trigger Event to change the icon while shown and hidden the accordion body
			container
				.on('hide.bs.collapse', '.js-collapse ', function (e) {
					$(e.currentTarget)
						.closest('.convertLeadModules')
						.find('.fas')
						.removeClass('fa-chevron-up')
						.addClass('fa-chevron-down');
				})
				.on('show.bs.collapse', '.js-collapse ', function (e) {
					$(e.currentTarget)
						.closest('.convertLeadModules')
						.find('.fas')
						.removeClass('fa-chevron-down')
						.addClass('fa-chevron-up');
				});

			//Trigger Event on click of Transfer related records modules
			container.on('click', '.transferModule', function (e) {
				var currentTarget = jQuery(e.currentTarget);
				var module = currentTarget.val();
				var moduleBlock = jQuery('#' + module + '_FieldInfo');
				if (currentTarget.is(':checked')) {
					jQuery('#' + module + 'Module').prop('checked', true);
					moduleBlock.collapse('show');
					instance.removeDisableAttr(moduleBlock);
				}
			});

			//Trigger Event on click of the Modules selection to convert the lead
			container.on('click', '.convertLeadModuleSelection', function (e) {
				var currentTarget = jQuery(e.currentTarget);
				var currentModuleName = currentTarget.val();
				var moduleBlock = currentTarget.closest('.convertLeadModules').find('#' + currentModuleName + '_FieldInfo');
				var currentTransferModuleElement = jQuery('#transfer' + currentModuleName);
				var otherTransferModuleElement = jQuery('input[name="transferModule"]').not(currentTransferModuleElement);
				var otherTransferModuleValue = jQuery(otherTransferModuleElement).val();
				var otherModuleElement = jQuery('#' + otherTransferModuleValue + 'Module');

				if (currentTarget.is(':checked')) {
					moduleBlock.collapse('show');
					instance.removeDisableAttr(moduleBlock);
					if (!otherModuleElement.is(':checked')) {
						jQuery(currentTransferModuleElement).prop('checked', true);
					}
				} else {
					moduleBlock.collapse('hide');
					instance.addDisableAttr(moduleBlock);
					jQuery(currentTransferModuleElement).prop('checked', false);
					if (otherModuleElement.is(':checked')) {
						jQuery(otherTransferModuleElement).prop('checked', true);
					}
				}
				e.stopImmediatePropagation();
			});
		},
		/*
		 * function to register Convert Lead Submit Event
		 */
		registerConvertLeadSubmit: function () {
			var thisInstance = this;
			var formElement = this.getConvertLeadForm();

			formElement.on('jqv.form.validating', function (e) {
				var jQv = jQuery(e.currentTarget).data('jqv');
				//Remove the earlier validated fields from history so that it wont count disabled fields
				jQv.InvalidFields = [];
			});

			//Convert Lead Form Submission
			formElement.on('submit', function (e) {
				var convertLeadModuleElements = thisInstance.getConvertLeadModules();
				var moduleArray = [];
				var accountModel = formElement.find('#AccountsModule');

				//If the validation fails in the hidden Block, we should show that Block with error.
				var invalidFields = formElement.data('jqv').InvalidFields;
				if (invalidFields.length > 0) {
					var fieldElement = invalidFields[0];
					var moduleBlock = jQuery(fieldElement).closest('.js-collapse');
					moduleBlock.collapse('show');
					e.preventDefault();
					return;
				}

				jQuery.each(convertLeadModuleElements, function (index, element) {
					if (jQuery(element).is(':checked')) {
						moduleArray.push(jQuery(element).val());
					}
				});
				formElement.find('input[name="modules"]').val(JSON.stringify(moduleArray));

				var organizationElement = accountModel.length;
				if (organizationElement != '0') {
					if (jQuery.inArray('Accounts', moduleArray) == -1) {
						app.showAlert(app.vtranslate('JS_SELECT_ORGANIZATION'));
						e.preventDefault();
					}
				}
			});
		},
		/*
		 * function to get all the checkboxes which are representing the modules selection
		 */
		getConvertLeadModules: function () {
			var container = this.getConvertLeadContainer();
			if (this.convertLeadModules == false) {
				this.convertLeadModules = jQuery('.convertLeadModuleSelection', container);
			}
			return this.convertLeadModules;
		},
		/*
		 * function to get Convert Lead Form
		 */
		getConvertLeadForm: function () {
			if (this.convertLeadForm == false) {
				this.convertLeadForm = jQuery('#convertLeadForm');
			}
			return this.convertLeadForm;
		},
		/*
		 * function to get Convert Lead Container
		 */
		getConvertLeadContainer: function () {
			if (this.convertLeadContainer == false) {
				this.convertLeadContainer = jQuery('#leadAccordion');
			}
			return this.convertLeadContainer;
		},
		saveFieldValues: function (fieldDetailList) {
			var aDeferred = jQuery.Deferred();
			var recordId = this.getRecordId();

			var data = {};
			if (typeof fieldDetailList !== 'undefined') {
				data = fieldDetailList;
			}

			data['record'] = recordId;
			data['module'] = app.getModuleName();
			data['action'] = 'SaveAjax';

			AppConnector.request(data).done(function (reponseData) {
				aDeferred.resolve(reponseData);
			});
			if (fieldDetailList && fieldDetailList.field == 'leadstatus') {
				let btn = jQuery('.btn-convertLead');
				let conversionAvailableStatus = jQuery('#conversion_available_status').val();
				if (conversionAvailableStatus) {
					let status = JSON.parse(conversionAvailableStatus);
					if (status.length === 0 || jQuery.inArray(fieldDetailList.value, status) != -1) {
						btn.removeClass('d-none');
					} else {
						btn.addClass('d-none');
					}
				} else {
					btn.addClass('d-none');
				}
			}
			return aDeferred.promise();
		},
		updateConvertLeadvalue: function () {
			var thisInstance = Vtiger_Detail_Js.getInstance();
			var detailContentsHolder = thisInstance.getContentHolder();
			detailContentsHolder.on(thisInstance.fieldUpdatedEvent, 'input,select', function (e, params) {
				var elem = jQuery(e.currentTarget);
				var fieldName = elem.attr('name');
				var ajaxnewValue = params['new'];

				if (!jQuery.isEmptyObject(Leads_Detail_Js.cache)) {
					var sampleCache = jQuery(Leads_Detail_Js.cache);
					var contextElem = sampleCache.find('[name="' + fieldName + '"]');

					if (elem.is('select')) {
						var oldvalue = contextElem.val();
						contextElem.find('option[value="' + oldvalue + '"]').removeAttr('selected');
						contextElem.find('option[value="' + ajaxnewValue + '"]').attr('selected', 'selected');
						contextElem.trigger('change');
					} else {
						contextElem.attr('value', ajaxnewValue);
					}
					Leads_Detail_Js.cache = sampleCache;
				}
			});
		},
		registerEvents: function () {
			this._super();
			this.updateConvertLeadvalue();
		}
	}
);
