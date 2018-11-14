/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
'use strict';

class CustomView {

	constructor(url) {
		let progressIndicatorElement = $.progressIndicator();
		app.showModalWindow(null, url, () => {
			this.contentsCotainer = $('.js-filter-modal__container');
			this.advanceFilterInstance = new Vtiger_ConditionBuilder_Js(this.contentsCotainer.find('.js-condition-builder'), this.contentsCotainer.find('#sourceModule').val());
			this.advanceFilterInstance.registerEvents();
			//This will store the columns selection container
			this.columnSelectElement = false;
			this.registerEvents();
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
		});
	}

	loadDateFilterValues() {
		let selectedDateFilter = $('#standardDateFilter option:selected');
		let currentDate = selectedDateFilter.data('currentdate');
		let endDate = selectedDateFilter.data('enddate');
		$("#standardFilterCurrentDate").val(currentDate);
		$("#standardFilterEndDate").val(endDate);
	}

	/**
	 * Function to get the contents container
	 * @return : jQuery object of contents container
	 */
	getContentsContainer() {
		if (this.contentsCotainer == false) {
			this.contentsCotainer = $('.js-filter-modal__container');
		}
		return this.contentsCotainer;
	}

	/**
	 * Function to get the view columns selection element
	 * @return : jQuery object of view columns selection element
	 */
	getColumnSelectElement() {
		if (this.columnSelectElement == false) {
			this.columnSelectElement = $('#viewColumnsSelect');
		}
		return this.columnSelectElement;
	}

	/**
	 * Function which will get the selected columns
	 * @return : array of selected values
	 */
	getSelectedColumns() {
		let columnListSelectElement = this.getColumnSelectElement();
		return columnListSelectElement.val();
	}

	saveFilter() {
		let aDeferred = $.Deferred();
		let formData = $("#CustomView").serializeFormData();
		AppConnector.request(formData, true).done(function (data) {
			aDeferred.resolve(data);
		}).fail(function (error) {
			aDeferred.reject(error);
		});
		return aDeferred.promise();
	}

	saveAndViewFilter() {
		this.saveFilter().done(function (response) {
			if (response.success) {
				let url;
				if (app.getParentModuleName() == 'Settings') {
					url = 'index.php?module=CustomView&parent=Settings&view=Index&sourceModule=' + $('#sourceModule').val();
				} else {
					url = response['result']['listviewurl'];
				}
				window.location.href = url;
			} else {
				$.unblockUI();
				Vtiger_Helper_Js.showPnotify({
					title: app.vtranslate('JS_DUPLICATE_RECORD'),
					text: response.error['message']
				});
			}
		});
	}

	registerIconEvents() {
		this.getContentsContainer().find('.js-filter-preferences').on('change', '.js-filter-preference', (e) => {
			let currentTarget = $(e.currentTarget);
			let iconElement = currentTarget.next();
			if (currentTarget.prop('checked')) {
				iconElement.removeClass(iconElement.data('unchecked')).addClass(iconElement.data('check'));
			} else {
				iconElement.removeClass(iconElement.data('check')).addClass(iconElement.data('unchecked'));
			}
		});
	}

	registerBlockToggleEvent() {
		const container = this.getContentsContainer();
		container.on('click', '.blockHeader', function (e) {
			const target = $(e.target);
			if (target.is('input') || target.is('button') || target.parents().is('button') || target.hasClass('js-stop-propagation') || target.parents().hasClass('js-stop-propagation')) {
				return false;
			}
			const blockHeader = $(e.currentTarget);
			const blockContents = blockHeader.next();
			const iconToggle = blockHeader.find('.iconToggle');
			if (blockContents.hasClass('d-none')) {
				blockContents.removeClass('d-none');
				iconToggle.removeClass(iconToggle.data('hide')).addClass(iconToggle.data('show'));
			} else {
				blockContents.addClass('d-none');
				iconToggle.removeClass(iconToggle.data('show')).addClass(iconToggle.data('hide'));
			}
		});
	}

	registerColorEvent() {
		const container = this.getContentsContainer();
		container.find('.js-color-picker').colorpicker({
			format: 'hex',
			autoInputFallback: false
		});
	}

	/**
	 * Get list of fields to duplicates
	 * @returns {Array}
	 */
	getDuplicateFields(){
		let fields = [];
		const container = this.getContentsContainer();
		container.find('.js-duplicates-container .js-duplicates-row').each(function(){
			fields.push({
				fieldid: $(this).find('.js-duplicates-field').val(),
				ignore: $(this).find('.js-duplicates-ignore').is(':checked')
			})
		});
		return fields;
	}
	/**
	 * Register events for block "Find duplicates"
	 */
	registerDuplicatesEvents(){
		const container = this.getContentsContainer();
		App.Fields.Picklist.showSelect2ElementView(container.find('.js-duplicates-container .js-duplicates-field'));
		container.on('click', '.js-duplicates-remove', function(e) {
			$(this).closest('.js-duplicates-row').remove();
		});
		container.find('.js-duplicate-add-field').on('click', function(){
			let template = container.find('.js-duplicates-field-template').clone();
			template.removeClass('d-none');
			template.removeClass('js-duplicates-field-template');
			App.Fields.Picklist.showSelect2ElementView(template.find('.js-duplicates-field'));
			container.find('.js-duplicates-container').append(template);
		});
	}
	registerSubmitEvent(select2Element) {
		$("#CustomView").on('submit', (e) => {
			let selectElement = this.getColumnSelectElement();
			if ($('#viewname').val().length > 40) {
				Vtiger_Helper_Js.showPnotify({
					title: app.vtranslate('JS_MESSAGE'),
					text: app.vtranslate('JS_VIEWNAME_ALERT')
				});
				e.preventDefault();
				return;
			}
			//Mandatory Fields selection validation
			//Any one Mandatory Field should select while creating custom view.
			let mandatoryFieldsList = JSON.parse($('#mandatoryFieldsList').val());
			let selectedOptions = selectElement.val();
			let mandatoryFieldsMissing = true;
			if (selectedOptions) {
				for (let i = 0; i < selectedOptions.length; i++) {
					if ($.inArray(selectedOptions[i], mandatoryFieldsList) >= 0) {
						mandatoryFieldsMissing = false;
						break;
					}
				}
			}
			if (mandatoryFieldsMissing) {
				selectElement.validationEngine(
					'showPrompt',
					app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_MANDATORY_FIELD'),
					'error',
					'topLeft',
					true
				);
				e.preventDefault();
				return;
			} else {
				select2Element.validationEngine('hide');
			}
			//Mandatory Fields validation ends
			let result = $(e.currentTarget).validationEngine('validate');
			if (result == true) {
				//handled standard filters saved values.
				let stdfilterlist = {};

				if (($('#standardFilterCurrentDate').val() != '') && ($('#standardFilterEndDate').val() != '') && ($('select.standardFilterColumn option:selected').val() != 'none')) {
					stdfilterlist['columnname'] = $('select.standardFilterColumn option:selected').val();
					stdfilterlist['stdfilter'] = $('select#standardDateFilter option:selected').val();
					stdfilterlist['startdate'] = $('#standardFilterCurrentDate').val();
					stdfilterlist['enddate'] = $('#standardFilterEndDate').val();
					$('#stdfilterlist').val(JSON.stringify(stdfilterlist));
				}
				//handled advanced filters saved values.
				let advfilterlist = this.advanceFilterInstance.getConditions();
				$('#advfilterlist').val(JSON.stringify(advfilterlist));
				$('[name="duplicatefields"]').val(JSON.stringify(this.getDuplicateFields()));
				$('input[name="columnslist"]', this.getContentsContainer()).val(JSON.stringify(this.getSelectedColumns()));
				this.saveAndViewFilter();
				return false;
			} else {
				app.formAlignmentAfterValidation($(e.currentTarget));
			}
		});
	}


	/**
	 * Block submit on press enter key
	 */
	registerDisableSubmitOnEnter() {
		this.getContentsContainer().find('#viewname, [name="color"]').keydown(function (e) {
			if (e.keyCode === 13) {
				e.preventDefault();
			}
		});
	}

	registerEvents() {
		this.registerIconEvents();
		new App.Fields.Text.Editor(this.getContentsContainer().find('.js-editor'));
		this.registerBlockToggleEvent();
		this.registerColorEvent();
		this.registerDuplicatesEvents();
		let select2Element = App.Fields.Picklist.showSelect2ElementView(this.getColumnSelectElement());
		this.registerSubmitEvent(select2Element);
		$('.stndrdFilterDateSelect').datepicker();
		$("#standardDateFilter").on('change', () => {
			this.loadDateFilterValues();
		});
		$('#CustomView').validationEngine(app.validationEngineOptions);
		this.registerDisableSubmitOnEnter();
	}
};
