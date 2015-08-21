/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("Supplies_Edit_Js", {}, {
	lineItemContentsContainer: false,
	rowClass: 'tr.rowSup',
	discountMondalFields: ['aggregationType', 'globalDiscount', 'groupCheckbox', 'groupDiscount', 'individualDiscount', 'individualDiscountType'],
	taxMondalFields: ['aggregationType', 'globalTax', 'groupCheckbox', 'groupTax', 'individualTax'],
	/**
	 * Function that is used to get the line item container
	 * @return : jQuery object
	 */
	getSupTableContainer: function () {
		if (this.lineItemContentsContainer == false) {
			this.lineItemContentsContainer = $('.suppliesItemTable');
		}
		return this.lineItemContentsContainer;
	},
	getNextLineItemRowNumber: function () {
		var rowNumber = $(this.rowClass, this.getSupTableContainer()).length;
		$('#suppliesRowNo').val(rowNumber + 1);
		return ++rowNumber;
	},
	getAccountId: function () {
		var accountReferenceField = $('#accountReferenceField').val();
		if (accountReferenceField != '') {
			return $('[name="' + accountReferenceField + '"]').val();
		}
		return '';
	},
	checkDeleteIcon: function () {
		var subTable = this.getSupTableContainer();
		if (subTable.find(this.rowClass).length > 1) {
			this.showLineItemsDeleteIcon();
		} else {
			this.hideLineItemsDeleteIcon();
		}
	},
	showLineItemsDeleteIcon: function () {
		this.getSupTableContainer().find('.deleteRow').removeClass('hide');
	},
	hideLineItemsDeleteIcon: function () {
		this.getSupTableContainer().find('.deleteRow').addClass('hide');
	},
	getClosestRow: function (element) {
		return element.closest(this.rowClass);
	},
	/**
	 * Function which will return the basic row which can be used to add new rows
	 * @return jQuery object which you can use to
	 */
	getBasicRow: function () {
		var newRow = $('#blackSuppliesTable tbody').clone(true, true);
		return newRow;
	},
	isRecordSelected: function (element) {
		var parentRow = element.closest('tr');
		var productField = parentRow.find('.recordLabel');
		var response = productField.validationEngine('validate');
		return response;
	},
	getTaxModeSelectElement: function (row) {
		var subTable = this.getSupTableContainer();
		if (subTable.find('thead .taxMode').length > 0) {
			return $('.taxMode');
		}
		return row.find('.taxMode');
	},
	isIndividualTaxMode: function (row) {
		var taxModeElement = this.getTaxModeSelectElement(row);
		var selectedOption = taxModeElement.find('option:selected');
		if (selectedOption.val() == '1') {
			return true;
		}
		return false;
	},
	isGroupTaxMode: function () {
		var taxTypeElement = this.getTaxModeSelectElement();
		var selectedOption = taxTypeElement.find('option:selected');
		if (selectedOption.val() == '0') {
			return true;
		}
		return false;
	},
	showIndividualTax: function (row) {
		var thisInstance = this;
		var colTax = thisInstance.getSupTableContainer().find('.colTax');
		if (thisInstance.isIndividualTaxMode()) {
			colTax.removeClass('hide');
		} else {
			colTax.addClass('hide');
			colTax.find('.tax').val('0');
			thisInstance.rowsCalculations();
		}
	},
	getDiscountModeSelectElement: function (row) {
		var subTable = this.getSupTableContainer();
		if (subTable.find('thead .discountMode').length > 0) {
			return $('.discountMode');
		}
		return row.find('.discountMode');
	},
	isIndividualDiscountMode: function (row) {
		var discountModeElement = this.getDiscountModeSelectElement(row);
		var selectedOption = discountModeElement.find('option:selected');
		if (selectedOption.val() == '1') {
			return true;
		}
		return false;
	},
	showIndividualDiscount: function (row) {
		var thisInstance = this;
		var colDiscount = thisInstance.getSupTableContainer().find('.colDiscount');
		if (thisInstance.isIndividualDiscountMode()) {
			colDiscount.removeClass('hide');
		} else {
			colDiscount.addClass('hide');
			colDiscount.find('.discount').val('0');
			thisInstance.rowsCalculations();
		}
	},
	getCurrency: function () {
		var currency = $('[name="currency"]', this.getSupTableContainer());
		return currency.find('option:selected').val();
	},
	getTax: function (row) {
		return app.parseNumberToFloat($('.tax', row).val());
	},
	getQuantityValue: function (row) {
		return app.parseNumberToFloat($('.qty', row).val());
	},
	getUnitPriceValue: function (row) {
		return app.parseNumberToFloat($('.unitPrice', row).val());
	},
	getDiscount: function (row) {
		var discount = $('.discount', row).val();
		if (discount == undefined) {
			discount = 0;
		}
		return app.parseNumberToFloat(discount);
	},
	getNetPrice: function (row) {
		return app.parseNumberToFloat($('.netPrice', row).val());
	},
	getTotalPrice: function (row) {
		return app.parseNumberToFloat($('.totalPrice', row).val());
	},
	getGrossPrice: function (row) {
		return app.parseNumberToFloat($('.grossPrice', row).val());
	},
	getPurchase: function (row) {
		var qty = this.getQuantityValue(row);
		return app.parseNumberToFloat($('.purchase', row).val()) * qty;
	},
	getSummaryGrossPrice: function () {
		var thisInstance = this;
		var price = 0;
		this.getSupTableContainer().find(this.rowClass).each(function (index) {
			price += thisInstance.getGrossPrice($(this));
		});
		return app.parseNumberToFloat(price);
	},
	setUnitPriceValue: function (row, val) {
		row.find('.unitPrice').val(val).attr('title', val);
		return this;
	},
	setNetPrice: function (row, val) {
		val = app.parseNumberToShow(val);
		$('.netPriceText', row).text(val);
		$('.netPrice', row).val(val);
	},
	setGrossPrice: function (row, val) {
		val = app.parseNumberToShow(val);
		$('.grossPriceText', row).text(val);
		$('.grossPrice', row).val(val);
	},
	setTotalPrice: function (row, val) {
		val = app.parseNumberToShow(val);
		$('.totalPriceText', row).text(val);
		$('.totalPrice', row).val(val);
	},
	setMargin: function (row, val) {
		val = app.parseNumberToShow(val);
		$('.margin', row).val(val);
	},
	setMarginP: function (row, val) {
		val = app.parseNumberToShow(val);
		$('.marginp', row).val(val);
	},
	quantityChangeActions: function (row) {
		this.rowCalculations(row);
		this.summaryCalculations();
	},
	rowCalculations: function (row) {
		this.calculateTotalPrice(row);
		this.calculateNetPrice(row);
		this.calculateGrossPrice(row);
		this.calculateMargin(row);
	},
	rowsCalculations: function () {
		var thisInstance = this;
		this.getSupTableContainer().find(this.rowClass).each(function (index) {
			thisInstance.quantityChangeActions($(this));
		});
	},
	summaryCalculations: function () {
		var thisInstance = this;
		this.getSupTableContainer().find('tfoot .wisableTd').each(function (index) {
			thisInstance.calculatSummary($(this), $(this).data('sumfield'));
		});
	},
	calculatSummary: function (element, field) {
		var thisInstance = this;
		var sum = 0;
		this.getSupTableContainer().find(this.rowClass).each(function (index) {
			sum += app.parseNumberToFloat($(this).find('.' + field).val());
		});
		element.text(app.parseNumberToShow(sum));
	},
	calculateNetPrice: function (row) {
		var netPrice = this.getTotalPrice(row) - this.getDiscount(row);
		this.setNetPrice(row, netPrice);
	},
	calculateGrossPrice: function (row) {
		var netPrice = this.getNetPrice(row);
		if (this.isIndividualTaxMode(row)) {
			var tax = this.getTax(row);
			netPrice += tax;
		}
		this.setGrossPrice(row, netPrice);
	},
	calculateTotalPrice: function (row) {
		var netPriceBeforeDiscount = this.getQuantityValue(row) * this.getUnitPriceValue(row);
		this.setTotalPrice(row, netPriceBeforeDiscount);
	},
	calculateMargin: function (row) {
		var netPrice = this.getNetPrice(row);
		var purchase = this.getPurchase(row);
		var margin = netPrice - purchase;

		this.setMargin(row, margin);
		var marginp = '0';
		if (purchase !== 0) {
			marginp = (margin / purchase) * 100;
		}
		this.setMarginP(row, marginp);
	},
	calculateDiscount: function (row, mondal) {
		var netPriceBeforeDiscount = this.getTotalPrice(row),
				valuePrices = netPriceBeforeDiscount,
				globalDiscount = 0,
				groupDiscount = 0,
				individualDiscount = 0,
				valueDiscount = 0;

		var discountsType = mondal.find('.discountsType').val();

		if (discountsType == '0' || discountsType == '1') {
			if (mondal.find('.activepanel .globalDiscount').length > 0) {
				var globalDiscount = mondal.find('.activepanel .globalDiscount').val();
			}
			if (mondal.find('.activepanel .individualDiscountType').length > 0) {
				var individualTypeDiscount = mondal.find('.activepanel .individualDiscountType[name="individual"]:checked').val();
				var value = mondal.find('.activepanel .individualDiscountValue').val();
				if (individualTypeDiscount == 'percentage') {
					individualDiscount = (value / 100) * netPriceBeforeDiscount;
				} else {
					individualDiscount = value;
				}
			}
			if (mondal.find('.activepanel .groupCheckbox').length > 0 && mondal.find('.activepanel .groupCheckbox').prop("checked") == true) {
				var groupDiscount = mondal.find('.groupValue').val();
				groupDiscount = netPriceBeforeDiscount * (parseFloat(groupDiscount) / 100);
			}

			valuePrices = valuePrices * ((100 - parseFloat(globalDiscount)) / 100);
			valuePrices = valuePrices - parseFloat(individualDiscount);
			valuePrices = valuePrices - parseFloat(groupDiscount);
		} else if (discountsType == '2') {
			mondal.find('.activepanel').each(function (index) {
				var panel = $(this);
				if (panel.find('.globalDiscount').length > 0) {
					var globalDiscount = parseFloat(panel.find('.globalDiscount').val());
					valuePrices = valuePrices * ((100 - globalDiscount) / 100);
				} else if (panel.find('.groupCheckbox').length > 0 && panel.find('.groupCheckbox').prop("checked") == true) {
					var groupDiscount = parseFloat(panel.find('.groupValue').val());
					valuePrices = valuePrices * ((100 - groupDiscount) / 100);
				} else if (panel.find('.individualDiscountType').length > 0) {
					var value = parseFloat(panel.find('.individualDiscountValue').val());
					if (panel.find('.individualDiscountType[name="individual"]:checked').val() == 'percentage') {
						valuePrices = valuePrices * ((100 - value) / 100);
					} else {
						valuePrices = valuePrices - value;
					}
				}
			});
		}

		mondal.find('.valuePrices').text(app.parseNumberToFloat(valuePrices));
		mondal.find('.valueDiscount').text(netPriceBeforeDiscount - app.parseNumberToFloat(valuePrices));
	},
	calculateTax: function (row, mondal) {
		var netPriceWithoutTax = this.getTotalPrice(row),
				valuePrices = netPriceWithoutTax,
				globalTax = 0,
				groupTax = 0,
				regionalTax = 0,
				individualTax = 0,
				valueTax = 0;

		var taxType = mondal.find('.taxsType').val();
		if (taxType == '0' || taxType == '1') {
			if (mondal.find('.activepanel .globalTax').length > 0) {
				var globalTax = mondal.find('.activepanel .globalTax').val();
			}
			if (mondal.find('.activepanel .individualTaxValue').length > 0) {
				var value = mondal.find('.activepanel .individualTaxValue').val();
				individualTax = (value / 100) * valuePrices;
			}
			if (mondal.find('.activepanel .groupTax').length > 0) {
				var groupTax = mondal.find('.groupTax').val();
				groupTax = netPriceWithoutTax * (parseFloat(groupTax) / 100);
			}
			if (mondal.find('.activepanel .regionalTax').length > 0) {
				var regionalTax = mondal.find('.regionalTax').val();
				regionalTax = netPriceWithoutTax * (parseFloat(regionalTax) / 100);
			}

			valuePrices = valuePrices * ((100 + parseFloat(globalTax)) / 100);
			valuePrices = valuePrices + parseFloat(individualTax);
			valuePrices = valuePrices + parseFloat(groupTax);
			valuePrices = valuePrices + parseFloat(regionalTax);
		} else if (taxType == '2') {
			mondal.find('.activepanel').each(function (index) {
				var panel = $(this);
				if (panel.find('.globalTax').length > 0) {
					var globalTax = parseFloat(panel.find('.globalTax').val());
					valuePrices = valuePrices * ((100 + globalTax) / 100);
				} else if (panel.find('.groupTax').length > 0) {
					var groupTax = parseFloat(panel.find('.groupTax').val());
					valuePrices = valuePrices * ((100 + groupTax) / 100);
				} else if (panel.find('.regionalTax').length > 0) {
					var regionalTax = parseFloat(panel.find('.regionalTax').val());
					valuePrices = valuePrices * ((100 + regionalTax) / 100);
				} else if (panel.find('.individualTaxValue').length > 0) {
					var value = parseFloat(panel.find('.individualTaxValue').val());
					valuePrices = ((value + 100) / 100) * valuePrices;
				}
			});
		}

		mondal.find('.valuePrices').text(app.parseNumberToFloat(valuePrices));
		mondal.find('.valueTax').text(app.parseNumberToFloat(valuePrices - netPriceWithoutTax));
	},
	updateRowSequence: function () {
		var subTable = this.getSupTableContainer();
		subTable.find(this.rowClass).each(function (index) {
			$(this).find('.sequence').val(index + 1);
		});
	},
	registerSuppliesSaveData: function (container) {
		var thisInstance = this;
		container.on(Vtiger_Edit_Js.recordPreSave, function (e, data) {
			if (!thisInstance.checkLimits(container)) {
				return false;
			}
			var table = container.find('#blackSuppliesTable');
			table.find('[name]').removeAttr('name');
		});
	},
	/**
	 * Function which will be used to handle price book popup
	 * @params :  popupImageElement - popup image element
	 */
	pricebooksPopupHandler: function (popupImageElement) {
		var thisInstance = this;
		var lineItemRow = popupImageElement.closest(this.rowClass);
		var rowName = lineItemRow.find('.rowName');
		var params = {};
		params.module = 'PriceBooks';
		params.src_module = $('[name="popupReferenceModule"]', rowName).val();
		params.src_record = $('.sourceField', rowName).val();
		params.src_field = $('[name="popupReferenceModule"]', rowName).data('field');
		params.get_url = 'getProductUnitPriceURL';
		params.currency_id = thisInstance.getCurrency();

		this.showPopup(params).then(function (data) {
			var responseData = JSON.parse(data);
			for (var id in responseData) {
				thisInstance.setUnitPriceValue(lineItemRow, responseData[id]);
			}
			thisInstance.quantityChangeActions(thisInstance.getClosestRow(rowName));
		});
	},
	showPopup: function (params) {
		var aDeferred = $.Deferred();
		var popupInstance = Vtiger_Popup_Js.getInstance();
		popupInstance.show(params, function (data) {
			aDeferred.resolve(data);
		});
		return aDeferred.promise();
	},
	subProductsCashe: [],
	loadSubProducts: function (parentRow, indicator) {
		var thisInstance = this;
		var recordId = jQuery('input.sourceField', parentRow).val();
		var recordModule = parentRow.find('.rowName input[name="popupReferenceModule"]').val();
		thisInstance.removeSubProducts(parentRow);
		if (recordId == '0' || $.inArray(recordModule, ['Products', 'Services']) < 0) {
			return false;
		}
		if (thisInstance.subProductsCashe[recordId]) {
			thisInstance.addSubProducts(parentRow, thisInstance.subProductsCashe[recordId]);
			return false;
		}
		var subProrductParams = {
			module: "Products",
			action: "SubProducts",
			record: recordId
		}
		if (indicator) {
			var progressInstace = jQuery.progressIndicator();
		}
		AppConnector.request(subProrductParams).then(
				function (data) {
					var responseData = data.result;
					thisInstance.subProductsCashe[recordId] = responseData;
					thisInstance.addSubProducts(parentRow, responseData);
					if (progressInstace) {
						progressInstace.hide();
					}
				},
				function (error, err) {
					if (progressInstace) {
						progressInstace.hide();
					}
					//TODO : handle the error case
				}
		);
	},
	removeSubProducts: function (parentRow) {
		var subProductsContainer = $('.subProductsContainer ul', parentRow);
		subProductsContainer.find("li").remove();
	},
	addSubProducts: function (parentRow, responseData) {
		var subProductsContainer = $('.subProductsContainer ul', parentRow);
		for (var id in responseData) {
			var priductText = $("<li>").text(responseData[id]);
			subProductsContainer.append(priductText);
		}
	},
	mapResultsToFields: function (referenceModule, parentRow, responseData) {
		var thisInstance = this;

		for (var id in responseData) {
			var recordData = responseData[id];
			var description = recordData.description;
			var unitPriceValues = recordData.unitPriceValues;
			var unitPriceValuesJson = JSON.stringify(unitPriceValues);

			for (var field in recordData) {
				parentRow.find('input.' + field).val(recordData[field]);
			}

			var currencyId = thisInstance.getCurrency();
			if (typeof unitPriceValues[currencyId] !== 'undefined') {
				thisInstance.setUnitPriceValue(parentRow, unitPriceValues[currencyId]);
			}
			$('input.unitPrice', parentRow).attr('list-info', unitPriceValuesJson);
			$('textarea.commentTextarea', parentRow).val(description);

			thisInstance.showIndividualTax(parentRow);
		}
		if (referenceModule === 'Products') {
			thisInstance.loadSubProducts(parentRow, true);
		}
		this.quantityChangeActions(parentRow);
	},
	saveDiscountsParameters: function (parentRow, mondal) {
		var thisInstance = this;
		var info = {};
		var extend = ['aggregationType', 'groupCheckbox', 'individualDiscountType'];
		$.each(thisInstance.discountMondalFields, function (index, param) {
			if ($.inArray(param, extend) >= 0) {
				if (mondal.find('[name="' + param + '"]:checked').length > 1) {
					info[param] = [];
					mondal.find('[name="' + param + '"]:checked').each(function (index) {
						info[param].push($(this).val());
					});
				} else {
					info[param] = mondal.find('[name="' + param + '"]:checked').val();
				}
			} else {
				info[param] = mondal.find('[name="' + param + '"]').val();
			}
		});
		parentRow.find('.discountParam').val(JSON.stringify(info));
	},
	saveTaxsParameters: function (parentRow, mondal) {
		var thisInstance = this;
		var info = {};
		var extend = ['aggregationType', 'groupCheckbox', 'individualTaxType'];
		$.each(thisInstance.taxMondalFields, function (index, param) {
			if ($.inArray(param, extend) >= 0) {
				if (mondal.find('[name="' + param + '"]:checked').length > 1) {
					info[param] = [];
					mondal.find('[name="' + param + '"]:checked').each(function (index) {
						info[param].push($(this).val());
					});
				} else {
					info[param] = mondal.find('[name="' + param + '"]:checked').val();
				}
			} else {
				info[param] = mondal.find('[name="' + param + '"]').val();
			}
		});
		parentRow.find('.taxParam').val(JSON.stringify(info));
	},
	showExpandedRow: function (row) {
		var thisInstance = this;
		var subTable = thisInstance.getSupTableContainer();
		var rowSupExpanded = subTable.find('[numrowex="' + row.attr('numrow') + '"]');
		var element = row.find('.toggleVisibility');
		element.data('status', '1');
		element.find('.glyphicon').removeClass('glyphicon-menu-down');
		element.find('.glyphicon').addClass('glyphicon-menu-up');
		rowSupExpanded.removeClass('hide');
		thisInstance.loadCkEditorElement(rowSupExpanded.find('.ckEditorSource'));
	},
	hideExpandedRow: function (row) {
		var thisInstance = this;
		var subTable = thisInstance.getSupTableContainer();
		var rowSupExpanded = subTable.find('[numrowex="' + row.attr('numrow') + '"]');
		var element = row.find('.toggleVisibility');
		element.data('status', '0');
		element.find('.glyphicon').removeClass('glyphicon-menu-up');
		element.find('.glyphicon').addClass('glyphicon-menu-down');
		rowSupExpanded.addClass('hide');
		var editorInstance = CKEDITOR.instances[rowSupExpanded.find('.ckEditorSource').attr('id')];
		if (editorInstance) {
			editorInstance.destroy();
		}
	},
	initDiscountsParameters: function (parentRow, mondal) {
		var thisInstance = this;
		var parameters = parentRow.find('.discountParam').val();
		if (parameters == '') {
			return;
		}
		var parameters = JSON.parse(parameters);
		$.each(thisInstance.discountMondalFields, function (index, param) {
			var parameter = parameters[param];
			var field = mondal.find('[name="' + param + '"]');

			if (field.attr('type') == 'checkbox' || field.attr('type') == 'radio') {
				var array = parameter;
				if (!$.isArray(array)) {
					array = [array];
				}
				$.each(array, function (index, arrayValue) {
					var value = field.filter('[value="' + arrayValue + '"]').prop('checked', true)
					if (param == 'aggregationType') {
						value.closest('.panel').find('.panel-body').show();
						value.closest('.panel').addClass('activepanel');
					}
				});
			} else if (field.prop("tagName") == 'SELECT') {
				field.find('option[value="' + parameter + '"]').prop('selected', 'selected').change();
			} else {
				mondal.find('[name="' + param + '"]').val(parameter);
			}
		});

		thisInstance.calculateDiscount(parentRow, mondal);
	},
	initTaxParameters: function (parentRow, mondal) {
		var thisInstance = this;
		var parameters = parentRow.find('.taxParam').val();
		if (parameters == '') {
			return;
		}
		var parameters = JSON.parse(parameters);
		$.each(thisInstance.taxMondalFields, function (index, param) {
			var parameter = parameters[param];
			var field = mondal.find('[name="' + param + '"]');

			if (field.attr('type') == 'checkbox' || field.attr('type') == 'radio') {
				var array = parameter;
				if (!$.isArray(array)) {
					array = [array];
				}
				$.each(array, function (index, arrayValue) {
					var value = field.filter('[value="' + arrayValue + '"]').prop('checked', true)
					if (param == 'aggregationType') {
						value.closest('.panel').find('.panel-body').show();
						value.closest('.panel').addClass('activepanel');
					}
				});
			} else if (field.prop("tagName") == 'SELECT') {
				field.find('option[value="' + parameter + '"]').prop('selected', 'selected').change();
			} else {
				mondal.find('[name="' + param + '"]').val(parameter);
			}
		});

		thisInstance.calculateTax(parentRow, mondal);
	},
	limitEnableSave: false,
	checkLimits: function () {
		var thisInstance = this;
		var account = thisInstance.getAccountId();
		var response = true;

		if (account == '' || $('#suppliesLimit').val() == '0' || thisInstance.limitEnableSave) {
			return true;
		}

		var params = {}
		params.data = {
			module: app.getModuleName(),
			action: 'CheckLimits',
			record: account,
			currency: thisInstance.getCurrency(),
			price: thisInstance.getSummaryGrossPrice(),
			limitConfig: $('#suppliesLimit').val(),
		}
		params.async = false;
		params.dataType = 'json';
		var progressInstace = jQuery.progressIndicator();
		AppConnector.request(params).then(
				function (data) {
					progressInstace.hide();
					var editViewForm = thisInstance.getForm();
					if (data.result.status == false) {
						app.showModalWindow(data.result.html, function (data) {
							data.find('.enableSave').on('click', function (e, data) {
								thisInstance.limitEnableSave = true;
								editViewForm.submit();
								app.hideModalWindow();
							});
						});
						response = false;
					}
				},
				function (error, err) {
					progressInstace.hide();
				}
		);
		return response;
	},
	registerAddRow: function (container) {
		var thisInstance = this;
		var subTable = this.getSupTableContainer();
		container.find('.btn-toolbar .addButton').on('click', function (e, data) {
			var table = container.find('#blackSuppliesTable');
			var newRow = thisInstance.getBasicRow();
			var sequenceNumber = thisInstance.getNextLineItemRowNumber();
			var module = $(e.currentTarget).data('module');
			var field = $(e.currentTarget).data('field');
			var wysiwyg = $(e.currentTarget).data('wysiwyg');

			var replaced = newRow.html().replace(/_NUM_/g, sequenceNumber);
			newRow.html(replaced);
			newRow = newRow.find('tr').appendTo(subTable.find('tbody'));

			newRow.find('.rowName input[name="popupReferenceModule"]').val(module).data('field', field);
			thisInstance.registerAutoCompleteFields(newRow);
			thisInstance.initRow(newRow);
		});
	},
	registerSortableRow: function () {
		var thisInstance = this;
		var subTable = thisInstance.getSupTableContainer();
		subTable.sortable({
			handle: '.dragHandle',
			items: thisInstance.rowClass,
			revert: true,
			tolerance: 'pointer',
			placeholder: "ui-state-highlight",
			helper: function (e, ui) {
				ui.children().each(function (index, element) {
					element = $(element);
					element.width(element.width());
				})
				return ui;
			},
			start: function (event, ui) {
				subTable.find(thisInstance.rowClass).each(function (index, element) {
					var row = $(element);
					thisInstance.hideExpandedRow(row);
				})
				ui.item.startPos = ui.item.index();
			},
			stop: function (event, ui) {
				var numrow = $(ui.item.context).attr('numrow');
				var child = subTable.find('.numRow' + numrow).remove().clone();
				subTable.find('[numrow="' + numrow + '"]').after(child);
				if (ui.item.startPos < ui.item.index()) {
					var child = subTable.find('.numRow' + numrow).next().remove().clone();
					subTable.find('[numrow="' + numrow + '"]').before(child);
				}
				thisInstance.updateRowSequence();
			}
		});
		subTable.disableSelection();
	},
	registerShowHideExpanded: function (container) {
		var thisInstance = this;
		container.on('click', '.toggleVisibility', function (e) {
			var element = $(e.currentTarget);
			var row = thisInstance.getClosestRow(element);
			if (element.data('status') == '0') {
				thisInstance.showExpandedRow(row);
			} else {
				thisInstance.hideExpandedRow(row);
			}
		});
	},
	registerPriceBookPopUp: function (container) {
		var thisInstance = this;
		container.on('click', '.priceBookPopup', function (e) {
			var element = $(e.currentTarget);
			var response = thisInstance.isRecordSelected(element);
			if (response == true) {
				return;
			}
			thisInstance.pricebooksPopupHandler(element);
		});
	},
	registerRowChangeEvent: function (container) {
		var thisInstance = this;
		container.on('focusout', '.qty', function (e) {
			var element = $(e.currentTarget);
			thisInstance.quantityChangeActions(thisInstance.getClosestRow(element));
		});
		container.on('focusout', '.unitPrice', function (e) {
			var element = $(e.currentTarget);
			thisInstance.quantityChangeActions(thisInstance.getClosestRow(element));
		});
		container.on('focusout', '.purchase', function (e) {
			var element = $(e.currentTarget);
			thisInstance.quantityChangeActions(thisInstance.getClosestRow(element));
		});
		container.on('change', '.taxMode', function (e) {
			var element = $(e.currentTarget);
			thisInstance.showIndividualTax(thisInstance.getClosestRow(element));
		});
		container.on('change', '.discountMode', function (e) {
			var element = $(e.currentTarget);
			thisInstance.showIndividualDiscount(thisInstance.getClosestRow(element));
		});
	},
	registerSubProducts: function (container) {
		var thisInstance = this;
		container.find(this.rowClass).each(function (index) {
			thisInstance.loadSubProducts($(this), false);
		});
	},
	registerClearReferenceSelection: function (container) {
		var thisInstance = this;
		container.on('click', '.clearReferenceSelection', function (e) {
			var element = $(e.currentTarget);
			var row = thisInstance.getClosestRow(element)
			thisInstance.removeSubProducts(row);
			row.find('.unitPrice,.tax,.discount,.margin,.purchase').val('0');
			row.find('textarea').val('');
			thisInstance.quantityChangeActions(row);
		});
	},
	registerDeleteLineItemEvent: function (container) {
		var thisInstance = this;
		container.on('click', '.deleteRow', function (e) {
			var element = $(e.currentTarget);
			thisInstance.getClosestRow(element).remove();
			thisInstance.checkDeleteIcon();
		});
	},
	registerChangeDiscount: function (container) {
		var thisInstance = this;
		container.on('click', '.changeDiscount', function (e) {
			var element = $(e.currentTarget);
			var parentRow = element.closest(thisInstance.rowClass);

			var params = {
				module: 'Products',
				view: 'Discounts',
				record: parentRow.find('.rowName .sourceField').val(),
				currency: thisInstance.getCurrency(),
				sourceModule: app.getModuleName(),
				sourceRecord: app.getRecordId(),
				totalPrice: thisInstance.getTotalPrice(parentRow),
				accountField: container.find('#accountReferenceField').val(),
			}

			var progressInstace = jQuery.progressIndicator();
			AppConnector.request(params).then(
					function (data) {
						app.showModalWindow(data, function (data) {
							thisInstance.initDiscountsParameters(parentRow, $(data));
							thisInstance.registerChangeDiscountModal(data, parentRow, params);
						});
						progressInstace.hide();
					},
					function (error, err) {
						progressInstace.hide();
					}
			);
		});
	},
	registerChangeDiscountModal: function (mondal, parentRow, params) {
		var thisInstance = this;
		mondal.on('change', '.individualDiscountType', function (e) {
			var element = $(e.currentTarget);
			mondal.find('.individualDiscountContainer .input-group-addon').text(element.data('symbol'));
		});
		mondal.on('change', '.activeCheckbox[name="aggregationType"]', function (e) {
			var element = $(e.currentTarget);

			if (element.attr('type') == 'checkbox' && this.checked) {
				element.closest('.panel').find('.panel-body').show();
				element.closest('.panel').addClass('activepanel');
			} else if (element.attr('type') == 'radio') {
				mondal.find('.panel').removeClass('activepanel');
				mondal.find('.panel .panel-body').hide();
				element.closest('.panel').find('.panel-body').show();
				element.closest('.panel').addClass('activepanel');
			} else {
				element.closest('.panel').find('.panel-body').hide();
				element.closest('.panel').removeClass('activepanel');
			}
		});
		mondal.on('change', '.activeCheckbox, .globalDiscount,.individualDiscountValue,.individualDiscountType,.groupCheckbox', function (e) {
			var element = $(e.currentTarget);
			thisInstance.calculateDiscount(parentRow, mondal);
		});
		mondal.on('click', '.saveDiscount', function (e) {
			parentRow.find('.discount').val(mondal.find('.valueDiscount').text());
			thisInstance.quantityChangeActions(parentRow);
			thisInstance.saveDiscountsParameters(parentRow, mondal);
			app.hideModalWindow();
		});
	},
	registerChangeTax: function (container) {
		var thisInstance = this;
		container.on('click', '.changeTax', function (e) {
			var element = $(e.currentTarget);
			var parentRow = element.closest(thisInstance.rowClass);

			var params = {
				module: 'Products',
				view: 'Taxs',
				record: parentRow.find('.rowName .sourceField').val(),
				recordModule: parentRow.find('.rowName [name="popupReferenceModule"]').val(),
				currency: thisInstance.getCurrency(),
				sourceModule: app.getModuleName(),
				sourceRecord: app.getRecordId(),
				totalPrice: thisInstance.getTotalPrice(parentRow),
				accountField: container.find('#accountReferenceField').val(),
			}

			var progressInstace = jQuery.progressIndicator();
			AppConnector.request(params).then(
					function (data) {
						app.showModalWindow(data, function (data) {
							thisInstance.initTaxParameters(parentRow, $(data));
							thisInstance.registerChangeTaxModal(data, parentRow, params);
						});
						progressInstace.hide();
					},
					function (error, err) {
						progressInstace.hide();
					}
			);
		});
	},
	registerChangeTaxModal: function (mondal, parentRow, params) {
		var thisInstance = this;
		mondal.on('change', '.individualTaxType', function (e) {
			var element = $(e.currentTarget);
			mondal.find('.individualTaxContainer .input-group-addon').text(element.data('symbol'));
		});
		mondal.on('change', '.activeCheckbox[name="aggregationType"]', function (e) {
			var element = $(e.currentTarget);

			if (element.attr('type') == 'checkbox' && this.checked) {
				element.closest('.panel').find('.panel-body').show();
				element.closest('.panel').addClass('activepanel');
			} else if (element.attr('type') == 'radio') {
				mondal.find('.panel').removeClass('activepanel');
				mondal.find('.panel .panel-body').hide();
				element.closest('.panel').find('.panel-body').show();
				element.closest('.panel').addClass('activepanel');
			} else {
				element.closest('.panel').find('.panel-body').hide();
				element.closest('.panel').removeClass('activepanel');
			}
		});
		mondal.on('change', '.activeCheckbox, .globalTax, .individualTaxValue, .groupTax, .regionalTax', function (e) {
			var element = $(e.currentTarget);
			thisInstance.calculateTax(parentRow, mondal);
		});
		mondal.on('click', '.saveTaxs', function (e) {
			parentRow.find('.tax').val(mondal.find('.valueTax').text());
			thisInstance.quantityChangeActions(parentRow);
			thisInstance.saveTaxsParameters(parentRow, mondal);
			app.hideModalWindow();
		});
	},
	registerRowAutoComplete: function (container) {
		var thisInstance = this;
		var sourceFieldElement = container.find('.rowName .sourceField');
		sourceFieldElement.on(Vtiger_Edit_Js.postReferenceSelectionEvent, function (e, rq) {
			var element = $(e.currentTarget);
			var parentRow = element.closest(thisInstance.rowClass);

			if (rq.data.label) {
				var record = rq.data.id;
			} else {
				for (var id in rq.data) {
					var record = id;
				}
			}

			var selectedModule = parentRow.find('.rowName [name="popupReferenceModule"]').val();
			var dataUrl = "index.php?module=" + app.getModuleName() + "&action=GetDetails&record=" + record + "&currency_id=" + thisInstance.getCurrency();
			AppConnector.request(dataUrl).then(
					function (data) {
						for (var id in data) {
							if (typeof data[id] == "object") {
								var recordData = data[id];
								thisInstance.mapResultsToFields(selectedModule, parentRow, recordData);
							}
						}
					},
					function (error, err) {
					}
			);
		});
	},
	initRow: function (container) {
		var thisInstance = this;
		if (typeof container == 'undefined') {
			container = thisInstance.getSupTableContainer();
		}
		thisInstance.registerDeleteLineItemEvent(container);
		thisInstance.registerPriceBookPopUp(container);
		thisInstance.registerRowChangeEvent(container);
		thisInstance.registerRowAutoComplete(container);
		thisInstance.checkDeleteIcon();
	},
	/**
	 * Function which will register all the events
	 */
	registerBasicEvents: function (container) {
		this._super(container);
		this.registerSuppliesSaveData(container);
		this.registerAddRow(container);

		this.initRow();
		this.registerSortableRow();
		this.registerSubProducts(container);
		this.registerChangeDiscount(container);
		this.registerChangeTax(container);
		this.registerClearReferenceSelection(container);
		this.registerShowHideExpanded(container);
	},
});

