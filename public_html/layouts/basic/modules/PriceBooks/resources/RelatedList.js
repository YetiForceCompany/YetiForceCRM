/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_RelatedList_Js("PriceBooks_RelatedList_Js", {}, {
	/**
	 * Function to get params for show event invocation
	 */
	getPopupParams: function () {
		var params = this._super();
		if (this.moduleName === 'Products') {
			params['view'] = "PriceBookProductPopup";
			params['src_field'] = 'priceBookRelatedList';
		}
		return params;
	},
	/**
	 * Function to handle the adding relations between parent and child window
	 */
	addRelations: function (idList) {
		var aDeferred = jQuery.Deferred();
		AppConnector.request({
			module: this.parentModuleName,
			action: 'RelationAjax',
			mode: 'addListPrice',
			related_module: this.moduleName,
			src_record: this.parentRecordId,
			relinfo: $.isArray(idList) ? JSON.stringify(idList) : idList
		}).then(function (responseData) {
			aDeferred.resolve(responseData);
		}, function (textStatus, errorThrown) {
			aDeferred.reject(textStatus, errorThrown);
		});
		return aDeferred.promise();
	},
	/**
	 * Function to show listprice update form
	 */
	showListPriceUpdate: function (data) {
		var thisInstance = this;
		var detail = Vtiger_Detail_Js.getInstance();
		app.showModalWindow(data, function (data) {
			data.find('#listPriceUpdate').validationEngine(app.validationEngineOptions);
			var container = jQuery('#listPriceUpdate');
			container.on('submit', function (e) {
				e.preventDefault();
				var invalidFields = container.data('jqv').InvalidFields;
				if (invalidFields.length == 0) {
					var relid = container.find('input[name="relid"]').val();
					var listPriceVal = container.find('input[name="currentPrice"]').val();
					thisInstance.addRelations([{id: relid, price: listPriceVal}]).then(function (data) {
						thisInstance.loadRelatedList().then(function (data) {
							detail.registerRelatedModulesRecordCount();
						});
					});
					app.hideModalWindow();
				}
			});
		});
	},
	listPriceUpdateContainer: false,
	/**
	 * Function to get listPrice update container
	 */
	getListPriceUpdateContainer: function () {
		return this.listPriceUpdateContainer;
	},
	getListPriceEditForm: function (requestUrl) {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var listPriceContainer = this.getListPriceUpdateContainer();
		if (listPriceContainer != false) {
			aDeferred.resolve(listPriceContainer);
		} else {
			AppConnector.request(requestUrl).then(function (data) {
				thisInstance.listPriceUpdateContainer = data;
				aDeferred.resolve(data);
			}, function (textStatus, errorThrown) {
				aDeferred.reject(textStatus, errorThrown);
			});
		}
		return aDeferred.promise();
	},
	registerEventForEditListPrice: function () {
		var thisInstance = this;
		this.content.on('click', 'button.editListPrice', function (e) {
			e.stopPropagation();
			var elem = jQuery(e.currentTarget);
			var requestUrl = elem.data('url');
			thisInstance.getListPriceEditForm(requestUrl).then(function (data) {
				var form = jQuery(data);
				form.find('input[name="relid"]').val(elem.data('relatedRecordid'));
				form.find('input[name="currentPrice"]').val(elem.data('listPrice'));
				thisInstance.showListPriceUpdate(form);
			});
		});
	},
	registerPostLoadEvents: function () {
		this._super();
		this.registerEventForEditListPrice();
	},
});
