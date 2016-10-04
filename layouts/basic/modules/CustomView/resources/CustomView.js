/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
var Vtiger_CustomView_Js = {
	contentsCotainer: false,
	columnListSelect2Element: false,
	advanceFilterInstance: false,
	//This will store the columns selection container
	columnSelectElement: false,
	//This will store the input hidden selectedColumnsList element
	selectedColumnsList: false,
	loadFilterView: function (url) {
		var progressIndicatorElement = jQuery.progressIndicator();
		AppConnector.request(url).then(
				function (data) {
					app.hideModalWindow();
					var contents = jQuery(".contentsDiv").html(data);
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					Vtiger_CustomView_Js.registerEvents();
					Vtiger_CustomView_Js.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance(jQuery('.filterContainer', contents));
				},
				function (error, err) {

				}
		);
	},
	loadDateFilterValues: function () {
		var selectedDateFilter = jQuery('#standardDateFilter option:selected');
		var currentDate = selectedDateFilter.data('currentdate');
		var endDate = selectedDateFilter.data('enddate');
		jQuery("#standardFilterCurrentDate").val(currentDate);
		jQuery("#standardFilterEndDate").val(endDate);
	},
	/**
	 * Function to get the contents container
	 * @return : jQuery object of contents container
	 */
	getContentsContainer: function () {
		if (Vtiger_CustomView_Js.contentsCotainer == false) {
			Vtiger_CustomView_Js.contentsCotainer = jQuery('div.contentsDiv');
		}
		return Vtiger_CustomView_Js.contentsCotainer;
	},
	getColumnListSelect2Element: function () {
		return Vtiger_CustomView_Js.columnListSelect2Element;
	},
	/**
	 * Function to get the view columns selection element
	 * @return : jQuery object of view columns selection element
	 */
	getColumnSelectElement: function () {
		if (Vtiger_CustomView_Js.columnSelectElement == false) {
			Vtiger_CustomView_Js.columnSelectElement = jQuery('#viewColumnsSelect');
		}
		return Vtiger_CustomView_Js.columnSelectElement;
	},
	/**
	 * Function to get the selected columns list
	 * @return : jQuery object of selectedColumnsList
	 */
	getSelectedColumnsList: function () {
		if (Vtiger_CustomView_Js.selectedColumnsList == false) {
			Vtiger_CustomView_Js.selectedColumnsList = jQuery('#selectedColumnsList');
		}
		return Vtiger_CustomView_Js.selectedColumnsList;
	},
	/**
	 * Function which will get the selected columns
	 * @return : array of selected values
	 */
	getSelectedColumns: function () {
		var columnListSelectElement = Vtiger_CustomView_Js.getColumnSelectElement();
		return columnListSelectElement.val();
	},
	saveFilter: function () {
		var aDeferred = jQuery.Deferred();
		var formElement = jQuery("#CustomView");
		var formData = formElement.serializeFormData();

		var progress = $.progressIndicator({
			'message': app.vtranslate('JS_SAVE_LOADER_INFO'),
			'blockInfo': {
				'enabled': true
			}
		});

		AppConnector.request(formData).then(
				function (data) {
					aDeferred.resolve(data);
				},
				function (error) {
					aDeferred.reject(error);
				}
		)
		return aDeferred.promise();
	},
	saveAndViewFilter: function () {
		Vtiger_CustomView_Js.saveFilter().then(
				function (response) {
					if (response.success) {
						if (app.getParentModuleName() == 'Settings') {
							var url = 'index.php?module=CustomView&parent=Settings&view=Index';
						} else {
							var url = response['result']['listviewurl'];
						}
						window.location.href = url;
					} else {
						$.unblockUI()
						var params = {
							title: app.vtranslate('JS_DUPLICATE_RECORD'),
							text: response.error['message']
						};
						Vtiger_Helper_Js.showPnotify(params);
					}
				},
				function (error) {

				}
		);
	},
	/**
	 * Function which will register the select2 elements for columns selection
	 */
	registerSelect2ElementForColumnsSelection: function () {
		var selectElement = Vtiger_CustomView_Js.getColumnSelectElement();
		return app.changeSelectElementView(selectElement, 'selectize', {plugins: ['drag_drop', 'remove_button'], maxItems: 12});
	},
	registerIconEvents: function () {
		var container = this.getContentsContainer();
		container.on('change', '.iconPreferences input', function (e) {
			var currentTarget = $(e.currentTarget);
			var buttonElement = currentTarget.closest('.btn');
			var iconElement = currentTarget.next();
			if (currentTarget.prop('checked')) {
				buttonElement.removeClass('btn-default').addClass('btn-primary');
				iconElement.removeClass(iconElement.data('unchecked')).addClass(iconElement.data('check'));
			} else {
				buttonElement.removeClass('btn-primary').addClass('btn-default');
				iconElement.removeClass(iconElement.data('check')).addClass(iconElement.data('unchecked'));
			}
		});
		container.find('.iconPreferences input').each(function (e) {
			jQuery(this).trigger('change');
		})
	},
	registerCkEditorElement: function () {
		var container = this.getContentsContainer();
		container.find('.ckEditorSource').each(function (e) {
			var ckEditorInstance = new Vtiger_CkEditor_Js();
			ckEditorInstance.loadCkEditor(jQuery(this)); //{toolbar: 'Basic'}
		})
	},
	registerBlockToggleEvent: function () {
		var container = this.getContentsContainer();
		container.on('click', '.blockHeader', function (e) {
			var blockHeader = jQuery(e.currentTarget);
			var blockContents = blockHeader.next();
			var iconToggle = blockHeader.find('.iconToggle');
			if (blockContents.hasClass('hide')) {
				blockContents.removeClass('hide');
				iconToggle.removeClass(iconToggle.data('hide')).addClass(iconToggle.data('show'));
			} else {
				blockContents.addClass('hide');
				iconToggle.removeClass(iconToggle.data('show')).addClass(iconToggle.data('hide'));
			}
		});
	},
	registerColorEvent: function () {
		var container = this.getContentsContainer();
		var field = container.find('.colorPicker');
		var color = field.val();
		var addon = field.parent().find('.input-group-addon');

		field.ColorPicker({
			onChange: function (hsb, hex, rgb) {
				color = '#' + hex;
				field.val(color);
				addon.css('background-color', color);
			},
			onBeforeShow: function () {
				$(this).ColorPickerSetColor(this.value);
			}
		});
	},
	registerEvents: function () {
		this.registerIconEvents();
		this.registerCkEditorElement();
		this.registerBlockToggleEvent();
		this.registerColorEvent();
		var select2Element = Vtiger_CustomView_Js.columnListSelect2Element = Vtiger_CustomView_Js.registerSelect2ElementForColumnsSelection();
		var contentsContainer = Vtiger_CustomView_Js.getContentsContainer();
		jQuery('.stndrdFilterDateSelect').datepicker();
		jQuery('.chzn-select').chosen();

		var selectizeInstance = select2Element[0].selectize;
		var columnsList = JSON.parse(jQuery('input[name="columnslist"]').val());
		selectizeInstance.clear();
		for (i in columnsList) {
			selectizeInstance.addItem(columnsList[i]);
		}
		jQuery("#standardDateFilter").change(function () {
			Vtiger_CustomView_Js.loadDateFilterValues();
		});

		jQuery("#CustomView").submit(function (e) {
			var selectElement = Vtiger_CustomView_Js.getColumnSelectElement();
			if (jQuery('#viewname').val().length > 40) {
				var params = {
					title: app.vtranslate('JS_MESSAGE'),
					text: app.vtranslate('JS_VIEWNAME_ALERT')
				}
				Vtiger_Helper_Js.showPnotify(params);
				e.preventDefault();
				return;
			}

			//Mandatory Fields selection validation
			//Any one Mandatory Field should select while creating custom view.
			var mandatoryFieldsList = JSON.parse(jQuery('#mandatoryFieldsList').val());
			var selectedOptions = selectElement.val();
			var mandatoryFieldsMissing = true;
			if (selectedOptions) {
				for (var i = 0; i < selectedOptions.length; i++) {
					if (jQuery.inArray(selectedOptions[i], mandatoryFieldsList) >= 0) {
						mandatoryFieldsMissing = false;
						break;
					}
				}
			}
			if (mandatoryFieldsMissing) {
				var result = app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_MANDATORY_FIELD');
				select2Element.validationEngine('showPrompt', result, 'error', 'topLeft', true);
				e.preventDefault();
				return;
			} else {
				select2Element.validationEngine('hide');
			}
			//Mandatory Fields validation ends
			var result = jQuery(e.currentTarget).validationEngine('validate');
			if (result == true) {
				//handled standard filters saved values.
				var stdfilterlist = {};

				if ((jQuery('#standardFilterCurrentDate').val() != '') && (jQuery('#standardFilterEndDate').val() != '') && (jQuery('select.standardFilterColumn option:selected').val() != 'none')) {
					stdfilterlist['columnname'] = jQuery('select.standardFilterColumn option:selected').val();
					stdfilterlist['stdfilter'] = jQuery('select#standardDateFilter option:selected').val();
					stdfilterlist['startdate'] = jQuery('#standardFilterCurrentDate').val();
					stdfilterlist['enddate'] = jQuery('#standardFilterEndDate').val();
					jQuery('#stdfilterlist').val(JSON.stringify(stdfilterlist));
				}

				//handled advanced filters saved values.
				var advfilterlist = Vtiger_CustomView_Js.advanceFilterInstance.getValues();
				jQuery('#advfilterlist').val(JSON.stringify(advfilterlist));
				jQuery('input[name="columnslist"]', contentsContainer).val(JSON.stringify(Vtiger_CustomView_Js.getSelectedColumns()));
				Vtiger_CustomView_Js.saveAndViewFilter();
				return false;
			} else {
				app.formAlignmentAfterValidation(jQuery(e.currentTarget));
			}
		});
		jQuery('#CustomView').validationEngine(app.validationEngineOptions);
	}
}
