/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

jQuery.Class(
	'Settings_Currency_Js',
	{
		//holds the currency instance
		currencyInstance: false,

		/**
		 * This function used to triggerAdd Currency
		 */
		triggerAdd: function (event) {
			event.stopPropagation();
			var instance = Settings_Currency_Js.currencyInstance;
			instance.showEditView();
		},

		/**
		 * This function used to trigger Edit Currency
		 */
		triggerEdit: function (event, id) {
			event.stopPropagation();
			var instance = Settings_Currency_Js.currencyInstance;
			instance.showEditView(id);
		},

		/**
		 * This function used to trigger default currency
		 * @param {object} event
		 * @param {int} id
		 */
		triggerDefault: function (event, id) {
			event.stopPropagation();
			app.showConfirmModal({
				text: app.vtranslate('JS_CURRENCY_DEFAULT_CONFIRMED'),
				confirmedCallback: () => {
					var progressIndicatorElement = jQuery.progressIndicator({
						position: 'html',
						blockInfo: { enabled: true }
					});
					app.saveAjax('setDefault', null, { record: id }).done(function (data) {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						Settings_Vtiger_Index_Js.showMessage({
							text: app.vtranslate('JS_CURRENCY_DETAILS_SAVED')
						});
						Settings_Currency_Js.currencyInstance.loadListViewContents();
					});
				}
			});
		},

		/**
		 * This function used to trigger Delete Currency
		 */
		triggerDelete: function (event, id) {
			event.stopPropagation();
			var currentTarget = jQuery(event.currentTarget);
			var currentTrEle = currentTarget.closest('tr');
			var instance = Settings_Currency_Js.currencyInstance;
			instance.transformEdit(id).done(function (data) {
				var callBackFunction = function (data) {
					var form = jQuery('#transformCurrency');

					//register all select2 Elements
					App.Fields.Picklist.showSelect2ElementView(form.find('select.select2'));

					form.on('submit', function (e) {
						e.preventDefault();
						var transferCurrencyEle = form.find('select[name="transform_to_id"]');
						instance.deleteCurrency(id, transferCurrencyEle, currentTrEle);
					});
				};

				app.showModalWindow(
					data,
					function (data) {
						if (typeof callBackFunction == 'function') {
							callBackFunction(data);
						}
					},
					{ width: '500px' }
				);
			});
		}
	},
	{
		//constructor
		init: function () {
			Settings_Currency_Js.currencyInstance = this;
		},

		/*
		 * function to show editView for Add/Edit Currency
		 * @params: id - currencyId
		 */
		showEditView: function (id) {
			var thisInstance = this;
			var aDeferred = jQuery.Deferred();

			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});

			var params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['view'] = 'EditAjax';
			params['record'] = id;

			AppConnector.request(params)
				.done(function (data) {
					var callBackFunction = function (data) {
						var form = jQuery('#editCurrency');
						var record = form.find('[name="record"]').val();

						//register all select2 Elements
						App.Fields.Picklist.showSelect2ElementView(form.find('select.select2'));
						var currencyStatus = form.find('[name="currency_status"]').is(':checked');
						if (record != '' && currencyStatus) {
							//While editing currency, register the status change event
							thisInstance.registerCurrencyStatusChangeEvent(form);
						}
						//If we change the currency name, change the code and symbol for that currency
						thisInstance.registerCurrencyNameChangeEvent(form);

						var params = app.validationEngineOptions;
						params.onValidationComplete = function (form, valid) {
							if (valid) {
								thisInstance.saveCurrencyDetails(form);
								return valid;
							}
						};
						form.validationEngine(params);

						form.on('submit', function (e) {
							e.preventDefault();
						});
					};

					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					app.showModalWindow(
						data,
						function (data) {
							if (typeof callBackFunction == 'function') {
								callBackFunction(data);
							}
						},
						{ width: '600px' }
					);
				})
				.fail(function (error) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					aDeferred.reject(error);
				});
			return aDeferred.promise();
		},

		/**
		 * Register Change event for currency status
		 */
		registerCurrencyStatusChangeEvent: function (form) {
			/*If the status changed to Inactive while editing currency,
		 currency should transfer to other existing currencies */
			form.find('[name="currency_status"]').on('change', function (e) {
				var currentTarget = jQuery(e.currentTarget);
				if (currentTarget.is(':checked')) {
					form.find('div.transferCurrency').addClass('d-none');
				} else {
					form.find('div.transferCurrency').removeClass('d-none');
				}
			});
		},

		/**
		 * Register Change event for currency Name
		 */
		registerCurrencyNameChangeEvent: function (form) {
			var currencyNameEle = form.find('select[name="currency_name"]');
			//on change of currencyName, update the currency code & symbol
			currencyNameEle.on('change', function () {
				var selectedCurrencyOption = currencyNameEle.find('option:selected');
				form.find('[name="currency_code"]').val(selectedCurrencyOption.data('code'));
				form.find('[name="currency_symbol"]').val(selectedCurrencyOption.data('symbol'));
			});
		},

		/**
		 * This function will save the currency details
		 */
		saveCurrencyDetails: function (form) {
			var thisInstance = this;
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});

			var data = form.serializeFormData();
			data['module'] = app.getModuleName();
			data['parent'] = app.getParentModuleName();
			data['action'] = 'SaveAjax';
			data['mode'] = 'save';

			AppConnector.request(data)
				.done(function (data) {
					if (data['success']) {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						app.hideModalWindow();
						var params = {};
						params.text = app.vtranslate('JS_CURRENCY_DETAILS_SAVED');
						Settings_Vtiger_Index_Js.showMessage(params);
						thisInstance.loadListViewContents();
					}
				})
				.fail(function (error) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				});
		},

		/**
		 * This function will load the listView contents after Add/Edit currency
		 */
		loadListViewContents: function () {
			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});

			var params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['view'] = 'List';

			AppConnector.request(params)
				.done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					//replace the new list view contents
					jQuery('#listViewContents').html(data);
				})
				.fail(function (error, err) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				});
		},

		/**
		 * This function will show the Transform Currency view while delete the currency
		 */
		transformEdit: function (id) {
			var aDeferred = jQuery.Deferred();

			var params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['view'] = 'TransformEditAjax';
			params['record'] = id;

			AppConnector.request(params)
				.done(function (data) {
					aDeferred.resolve(data);
				})
				.fail(function (error, err) {
					aDeferred.reject(error, err);
				});
			return aDeferred.promise();
		},

		/**
		 * This function will delete the currency and save the transferCurrency details
		 */
		deleteCurrency: function (id, transferCurrencyEle, currentTrEle) {
			var transferCurrencyId = transferCurrencyEle.find('option:selected').val();
			var params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['action'] = 'DeleteAjax';
			params['record'] = id;
			params['transform_to_id'] = transferCurrencyId;

			AppConnector.request(params).done(function (data) {
				app.hideModalWindow();
				var params = {};
				params.text = app.vtranslate('JS_CURRENCY_DELETED_SUCCESSFULLY');
				Settings_Vtiger_Index_Js.showMessage(params);
				currentTrEle.fadeOut('slow').remove();
			});
		},

		registerRowClick: function () {
			var thisInstance = this;
			jQuery('#listViewContents').on('click', '.listViewEntries', function (e) {
				var currentRow = jQuery(e.currentTarget);
				if (currentRow.find('.yfi yfi-full-editing-view ').length <= 0) {
					return;
				}
				thisInstance.showEditView(currentRow.data('id'));
			});
		},

		registerEvents: function () {
			this.registerRowClick();
		}
	}
);

jQuery(document).ready(function () {
	var currencyInstance = new Settings_Currency_Js();
	currencyInstance.registerEvents();
});
