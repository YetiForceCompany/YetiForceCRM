/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class("Settings_Inventory_Index_Js", {}, {
	//Stored history of Name and duplicate check result
	duplicateCheckCache: {},
	/**
	 * This function will show the model for Add/Edit
	 */
	edit: function (url, currentTrElement) {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;

		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		AppConnector.request(url).done(function (data) {
			var callBackFunction = function (data) {
				//cache should be empty when modal opened
				thisInstance.duplicateCheckCache = {};
				var form = jQuery('#formInventory');
				var params = app.validationEngineOptions;
				params.onValidationComplete = function (form, valid) {
					if (valid) {
						thisInstance.saveDetails(form, currentTrElement);
						return valid;
					}
				}
				form.validationEngine(params);
				form.on('submit', function (e) {
					e.preventDefault();
				})
			}
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			app.showModalWindow(data, function (data) {
				if (typeof callBackFunction == 'function') {
					callBackFunction(data);
				}
			}, {});
		}).fail(function (error) {
			aDeferred.reject(error);
		});
		return aDeferred.promise();
	},
	/*
	 * Function to Save the Details
	 */
	saveDetails: function (form, currentTrElement) {
		const thisInstance = this;
		let params = form.serializeFormData();
		const saveButton = form.find('[type="submit"]');
		saveButton.prop('disabled', true);
		if (typeof params === "undefined") {
			params = {};
		}
		thisInstance.validateName(params).done((data) => {
			if (typeof data === "undefined") {
				saveButton.prop('disabled', false);
				return false;
			}
			const progressIndicatorElement = $.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			params.module = app.getModuleName();
			params.parent = app.getParentModuleName();
			params.action = 'SaveAjax';
			params.view = app.getViewName();
			AppConnector.request(params).done((data) => {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				app.hideModalWindow();
				if (typeof data == 'string') {
					data = JSON.parse(data);
				}
				data['result']['value'] = App.Fields.Double.formatToDisplay(data['result']['value']);
				//Adding or update details in the list
				if (form.find('.addView').val() == 'true') {
					thisInstance.addDetails(data['result']);
				} else {
					thisInstance.updateDetails(data['result'], currentTrElement);
				}
				//show notification after details saved
				Settings_Vtiger_Index_Js.showMessage({
					text: app.vtranslate('JS_SAVE_CHANGES')
				});
			});
		}).fail((data, err) => {
			saveButton.prop('disabled', false);
			return false;
		});
	},
	/*
	 * Function to add the Details in the list after saving
	 */
	addDetails: function (details) {
		let container = jQuery('#inventory'),
			currency = jQuery('#currency'),
			symbol = '%',
			table = $('.inventoryTable', container);
		if (currency.length > 0) {
			currency = JSON.parse(currency.val());
			symbol = currency.currency_symbol;
		}
		if (details.default === 1) {
			table.find('.default').prop('checked', false);
		}
		let trElement = $(`<tr class="opacity" data-id="${details.id}">
					<td class="textAlignCenter ${details.row_type}"><label class="name">${details.name}</label></td>
					<td class="textAlignCenter ${details.row_type}"><span class="value">${details.value} ${symbol}</span></td>
					<td class="textAlignCenter ${details.row_type}">
					<div class="float-right  w-50 d-flex justify-content-between mr-2">
						<input class="status js-update-field mt-2" checked type="checkbox">
						<div class="actions">
							<button class="btn btn-info btn-sm text-white editInventory u-cursor-pointer" data-url="${details._editurl}">
							<span title="Edycja" class="fas fa-edit alignBottom"></span>
							</button>
							<button class="removeInventory u-cursor-pointer btn btn-danger btn-sm text-white" data-url="${details._editurl}">
							<span title="Usuń" class="fas fa-trash-alt alignBottom"></span>
							</button>
						</div>
					</div>
					</td>
					</tr>`);
		table.append(trElement);
	},
	/*
	 * Function to update the details in the list after edit
	 */
	updateDetails: function (data, currentTrElement) {
		var currency = jQuery('#currency');
		var symbol = '%';
		if (currency.length > 0) {
			currency = JSON.parse(currency.val());
			symbol = currency.currency_symbol;
		}
		currentTrElement.find('.name').text(data['name']);
		currentTrElement.find('.value').text(data['value'] + ' ' + symbol);
		if (data['status'] === 0) {
			currentTrElement.find('.status').prop('checked', true);
		} else {
			currentTrElement.find('.status').prop('checked', false);
		}
		if (data['default'] === 1) {
			let table = $('.inventoryTable');
			table.find('.default').prop('checked', false);
			currentTrElement.find('.default').prop('checked', true);
		} else {
			currentTrElement.find('.default').prop('checked', false);
		}
	},
	/*
	 * Function to validate the Name to avoid duplicates
	 */
	validateName: function (data) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();

		var name = data.name;
		var form = jQuery('#formInventory');
		var nameElement = form.find('[name="name"]');
		if (!(name in thisInstance.duplicateCheckCache)) {
			thisInstance.checkDuplicateName(data).done(function (data) {
				thisInstance.duplicateCheckCache[name] = data['success'];
				if (data['success']) {
					thisInstance.duplicateCheckCache['message'] = data['message'];
					nameElement.validationEngine('showPrompt', data['message'], 'error', 'bottomLeft', true);
					aDeferred.reject(data);
				}
				aDeferred.resolve(data);
			}).fail(function (data, err) {
				aDeferred.reject(data);
			});
		} else {
			if (thisInstance.duplicateCheckCache[name] == true) {
				var result = thisInstance.duplicateCheckCache['message'];
				nameElement.validationEngine('showPrompt', result, 'error', 'bottomLeft', true);
				aDeferred.reject();
			} else {
				aDeferred.resolve();
			}
		}
		return aDeferred.promise();
	},
	/*
	 * Function to check Duplication of inventory Name
	 */
	checkDuplicateName: function (details) {
		var aDeferred = jQuery.Deferred();
		var name = details.name;
		var id = details.id;
		var moduleName = app.getModuleName();
		var params = {
			'module': moduleName,
			'parent': app.getParentModuleName(),
			'action': 'SaveAjax',
			'mode': 'checkDuplicateName',
			'name': name,
			'id': id,
			'view': app.getViewName()
		}

		AppConnector.request(params).done(function (data) {
			if (typeof data == 'string') {
				data = JSON.parse(data);
			}
			var response = data['result'];
			aDeferred.resolve(response);
		}).fail(function (error, err) {
			aDeferred.reject(error, err);
		});
		return aDeferred.promise();
	},
	/*
	 * Function to update status as enabled or disabled
	 */
	updateCheckbox: function (currentTarget) {
		var aDeferred = jQuery.Deferred();

		var currentTrElement = currentTarget.closest('tr');
		var id = currentTrElement.data('id');
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		var updatedCheckbox = currentTarget.data('field-name');

		var params = {
			'module': app.getModuleName(),
			'parent': app.getParentModuleName(),
			'action': 'SaveAjax',
			'id': id,
			'view': app.getViewName(),
		}

		if (updatedCheckbox === 'status') {
			params.status = currentTarget.is(':checked') ? 0 : 1;
		} else if (updatedCheckbox === 'default') {
			params.default = currentTarget.is(':checked') ? 1 : 0;
			let table = $('.inventoryTable');
			if (params.default === 1) {
				table.find('.default').not(currentTarget).prop('checked', false);
			}
		}

		AppConnector.request(params).done(function (data) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			aDeferred.resolve(data);
		}).fail(function (error, err) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			aDeferred.reject(error);
		});
		return aDeferred.promise();
	},
	removeInventory: function (inventoryElement) {
		var message = app.vtranslate('JS_DELETE_INVENTORY_CONFIRMATION');
		Vtiger_Helper_Js.showConfirmationBox({'message': message}).done(function (e) {
			var params = {};
			params['view'] = app.getViewName();
			params['id'] = inventoryElement.data('id');
			app.saveAjax('deleteInventory', params).done(function (data) {
				Settings_Vtiger_Index_Js.showMessage({type: 'success', text: data.result.message});
				inventoryElement.remove();
			});
		});
	},
	/*
	 * Function to register all actions in the List
	 */
	registerActions: function () {
		var thisInstance = this;
		var container = jQuery('#inventory');
		//register click event for Add New Inventory button
		container.find('.addInventory').on('click', function (e) {
			var addInventoryButton = jQuery(e.currentTarget);
			var createUrl = addInventoryButton.data('url');
			thisInstance.edit(createUrl);
		});

		//register event for edit icon
		container.on('click', '.editInventory', function (e) {
			var editButton = jQuery(e.currentTarget);
			var currentTrElement = editButton.closest('tr');
			thisInstance.edit(editButton.data('url'), currentTrElement);
		});

		//register event for edit icon
		container.on('click', '.removeInventory', function (e) {
			var removeInventoryButton = jQuery(e.currentTarget);
			var currentTrElement = removeInventoryButton.closest('tr');
			thisInstance.removeInventory(currentTrElement);
		});

		//register event for checkbox to change the Status
		container.on('click', '[type="checkbox"]', function (e) {
			var currentTarget = jQuery(e.currentTarget);

			thisInstance.updateCheckbox(currentTarget).done(function (data) {
				var params = {};
				params.text = app.vtranslate('JS_SAVE_CHANGES');
				Settings_Vtiger_Index_Js.showMessage(params);
			});
		});

	},
	registerEvents: function () {
		this.registerActions();
	}
});
