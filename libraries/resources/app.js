/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
var app = {
	/**
	 * variable stores client side language strings
	 */
	languageString: [],
	weekDaysArray: {Sunday: 0, Monday: 1, Tuesday: 2, Wednesday: 3, Thursday: 4, Friday: 5, Saturday: 6},
	cacheParams: [],
	/**
	 * Function to get the module name. This function will get the value from element which has id module
	 * @return : string - module name
	 */
	getModuleName: function () {
		return this.getMainParams('module');
	},
	/**
	 * Function to get the module name. This function will get the value from element which has id module
	 * @return : string - module name
	 */
	getParentModuleName: function () {
		return this.getMainParams('parent');
	},
	/**
	 * Function returns the current view name
	 */
	getViewName: function () {
		return this.getMainParams('view');
	},
	/**
	 * Function returns the record id
	 */
	getRecordId: function () {
		var view = this.getViewName();
		var recordId;
		if (view == 'Edit' || 'PreferenceEdit') {
			recordId = this.getMainParams('recordId');
		} else if (view == 'Detail' || 'PreferenceDetail') {
			recordId = this.getMainParams('recordId');
		}
		return recordId;
	},
	/**
	 * Function to get language
	 */
	getLanguage: function () {
		return jQuery('body').data('language');
	},
	/**
	 * Function to get path to layout
	 */
	getLayoutPath: function () {
		return jQuery('body').data('layoutpath');
	},
	/**
	 * Function to get page title
	 */
	getPageTitle: function () {
		return document.title;
	},
	/**
	 * Function to set page title
	 */
	setPageTitle: function (title) {
		document.title = title;
	},
	/**
	 * Function to get the contents container
	 * @returns jQuery object
	 */
	getContentsContainer: function () {
		return jQuery('.bodyContents');
	},
	/**
	 * Function which will convert ui of select boxes.
	 * @params parent - select element
	 * @params view - select2
	 * @params viewParams - select2 params
	 * @returns jquery object list which represents changed select elements
	 */
	changeSelectElementView: function (parent, view, viewParams) {
		var thisInstance = this;
		var selectElement = jQuery();
		if (typeof parent == 'undefined') {
			parent = jQuery('body');
		}
		//If view is select2, This will convert the ui of select boxes to select2 elements.
		if (view == 'select2') {
			return app.showSelect2ElementView(parent, viewParams);
		}
		//If view is selectize, This will convert the ui of select boxes to selectize elements.
		if (view == 'selectize') {
			return app.showSelectizeElementView(parent, viewParams);
		}
		selectElement = jQuery('.chzn-select', parent);
		//parent itself is the element
		if (parent.is('select.chzn-select')) {
			selectElement = parent;
		}

		// generate random ID
		selectElement.each(function () {
			if ($(this).prop("id").length == 0) {
				$(this).attr('id', "sel" + thisInstance.generateRandomChar() + thisInstance.generateRandomChar() + thisInstance.generateRandomChar());
			}
		});

		//fix for multiselect error prompt hide when validation is success
		selectElement.filter('[multiple]').filter('[data-validation-engine*="validate"]').on('change', function (e) {
			jQuery(e.currentTarget).trigger('focusout');
		});

		var params = {
			no_results_text: app.vtranslate('JS_NO_RESULTS_FOUND') + ':'
		};

		var moduleName = app.getModuleName();
		if (selectElement.filter('[multiple]') && moduleName != 'Install') {
			params.placeholder_text_multiple = ' ' + app.vtranslate('JS_SELECT_SOME_OPTIONS');
		}
		if (moduleName != 'Install') {
			params.placeholder_text_single = ' ' + app.vtranslate('JS_SELECT_AN_OPTION');
		}
		selectElement.chosen(params);

		selectElement.each(function () {
			var select = $(this);
			// hide selected items in the chosen instance when item is hidden.
			if (select.hasClass('hideSelected')) {
				var ns = [];
				select.find('optgroup,option').each(function (n, e) {
					if (jQuery(this).hasClass('hide')) {
						ns.push(n);
					}
				});
				if (ns.length) {
					select.next().find('.search-choice-close').each(function (n, e) {
						var element = jQuery(this);
						var index = element.data('option-array-index');
						if (jQuery.inArray(index, ns) != -1) {
							element.closest('li').remove();
						}
					})
				}
			}
			if (select.attr('readonly') == 'readonly') {
				select.on('chosen:updated', function () {
					if (select.attr('readonly')) {
						var wasDisabled = select.is(':disabled');
						var selectData = select.data('chosen');
						select.attr('disabled', 'disabled');
						if (typeof selectData == 'object') {
							selectData.search_field_disabled();
						}
						if (wasDisabled) {
							select.attr('disabled', 'disabled');
						} else {
							select.removeAttr('disabled');
						}
					}
				});
				select.trigger('chosen:updated');
			}
		});

		// Improve the display of default text (placeholder)
		var chosenSelectConainer = jQuery('.chosen-container-multi .default').css('width', '100%');
		return chosenSelectConainer;
	},
	/**
	 * Function to destroy the chosen element and get back the basic select Element
	 */
	destroyChosenElement: function (parent) {
		var selectElement = jQuery();
		if (typeof parent == 'undefined') {
			parent = jQuery('body');
		}

		selectElement = jQuery('.chzn-select', parent);
		//parent itself is the element
		if (parent.is('select.chzn-select')) {
			selectElement = parent;
		}

		selectElement.css('display', 'block').removeClass("chzn-done").data("chosen", null).next().remove();

		return selectElement;

	},
	/**
	 * Function to destroy the selectize element
	 */
	destroySelectizeElement: function (parent) {
		var selectElements = jQuery();
		if (typeof parent == 'undefined') {
			parent = jQuery('body');
		}
		selectElements = jQuery('.selectized', parent);
		//parent itself is the element
		if (parent.is('select.selectized')) {
			selectElements = parent;
		}
		selectElements.each(function () {
			$(this)[0].selectize.destroy();
		});
	},
	/**
	 * Function which will show the select2 element for select boxes . This will use select2 library
	 */
	showSelect2ElementView: function (selectElement, params) {
		if (typeof params == 'undefined') {
			params = {};
		}

		var data = selectElement.data();
		if (data != null) {
			params = jQuery.extend(data, params);
		}
		params.language = {};
		params.theme = "bootstrap";
		params.width = "100%";
		params.language.noResults = function (msn) {
			return app.vtranslate('JS_NO_RESULTS_FOUND');
		};

		// Sort DOM nodes alphabetically in select box.
		if (typeof params['customSortOptGroup'] != 'undefined' && params['customSortOptGroup']) {
			jQuery('optgroup', selectElement).each(function () {
				var optgroup = jQuery(this);
				var options = optgroup.children().toArray().sort(function (a, b) {
					var aText = jQuery(a).text();
					var bText = jQuery(b).text();
					return aText < bText ? 1 : -1;
				});
				jQuery.each(options, function (i, v) {
					optgroup.prepend(v);
				});
			});
			delete params['customSortOptGroup'];
		}

		//formatSelectionTooBig param is not defined even it has the maximumSelectionLength,
		//then we should send our custom function for formatSelectionTooBig
		if (typeof params.maximumSelectionLength != "undefined" && typeof params.formatSelectionTooBig == "undefined") {
			var limit = params.maximumSelectionLength;
			//custom function which will return the maximum selection size exceeds message.
			var formatSelectionExceeds = function (limit) {
				return app.vtranslate('JS_YOU_CAN_SELECT_ONLY') + ' ' + limit.maximum + ' ' + app.vtranslate('JS_ITEMS');
			}
			params.language.maximumSelected = formatSelectionExceeds;
		}

		if (typeof selectElement.attr('multiple') != 'undefined' && !params.placeholder) {
			params.tags = "true";
			params.placeholder = app.vtranslate('JS_SELECT_SOME_OPTIONS');
		} else if (!params.placeholder) {
			params.placeholder = app.vtranslate('JS_SELECT_AN_OPTION');
		}
		if (typeof params.templateResult === 'undefined') {
			params.templateResult = function (data, container) {
				if (data.element && data.element.className) {
					$(container).addClass(data.element.className);
				}
				if (typeof data.name == 'undefined') {
					return data.text;
				}
				if (data.type == 'optgroup') {
					return '<strong>' + data.name + '</strong>';
				} else {
					return '<span>' + data.name + '</span>';
				}
			};
		}
		if (typeof params.templateSelection === 'undefined') {
			params.templateSelection = function (data, container) {
				if (data.element && data.element.className) {
					$(container).addClass(data.element.className);
				}
				if (data.text === '') {
					return data.name;
				}
				return data.text;
			};
		}
		if (selectElement.data('ajaxSearch') === 1) {
			params.tags = false;
			params.language.searching = function () {
				return app.vtranslate('JS_SEARCHING');
			}
			params.language.inputTooShort = function (args) {
				var remainingChars = args.minimum - args.input.length;
				return app.vtranslate('JS_INPUT_TOO_SHORT').replace("_LENGTH_", remainingChars);
			}
			params.language.errorLoading = function () {
				return app.vtranslate('JS_NO_RESULTS_FOUND');
			}
			params.placeholder = '';
			params.ajax = {
				url: selectElement.data('ajaxUrl'),
				dataType: 'json',
				delay: 250,
				data: function (params) {
					return {
						value: params.term, // search term
						page: params.page
					};
				},
				processResults: function (data, params) {
					var items = new Array;
					if (data.success == true) {
						selectElement.find('option').each(function () {
							var currentTarget = $(this);
							items.push({
								label: currentTarget.html(),
								value: currentTarget.val(),
							});
						});
						items = items.concat(data.result.items);
					}
					return {
						results: items,
						pagination: {
							more: false
						}
					};
				},
				cache: false
			};
			params.escapeMarkup = function (markup) {
				if (markup !== 'undefined')
					return markup;
			};
			var minimumInputLength = 3;
			if (selectElement.data('minimumInput') != 'undefined') {
				minimumInputLength = selectElement.data('minimumInput');
			}
			params.minimumInputLength = minimumInputLength;
			params.templateResult = function (data) {
				if (typeof data.name == 'undefined') {
					return data.text;
				}
				if (data.type == 'optgroup') {
					return '<strong>' + data.name + '</strong>';
				} else {
					return '<span>' + data.name + '</span>';
				}
			};
			params.templateSelection = function (data, container) {
				if (data.text === '') {
					return data.name;
				}
				return data.text;
			};
		}
		var selectElementNew = selectElement;
		selectElement.each(function (e) {
			var select = $(this);
			if (select.attr('readonly') == 'readonly' && !select.attr('disabled')) {
				var selectNew = select.clone().addClass('hide');
				select.parent().append(selectNew);
				select.prop('disabled', true);
			}
			select.select2(params)
					.on("select2:open", function (e) {
						if (select.data('unselecting')) {
							select.removeData('unselecting');
							setTimeout(function (e) {
								select.each(function () {
									jQuery(this).select2('close');
								});
							}, 1);
						}
						var element = jQuery(e.currentTarget);
						var instance = element.data('select2');
						instance.$dropdown.css('z-index', 1000002);
					}).on("select2:unselect", function (e) {
				select.data('unselecting', true);
			});
		})
		return selectElement;
	},
	/**
	 * Function which will show the selectize element for select boxes . This will use selectize library
	 */
	showSelectizeElementView: function (selectElement, params) {
		if (typeof params == 'undefined') {
			params = {plugins: ['remove_button']};
		}
		selectElement.selectize(params);
		return selectElement;
	},
	hidePopover: function (element) {
		if (typeof element == 'undefined') {
			element = jQuery('body .popoverTooltip');
		}
		element.popover('hide');
	},
	showPopoverElementView: function (selectElement, params) {
		if (typeof params == 'undefined') {
			params = {trigger: 'hover', placement: 'bottom', html: true};
		}
		params.container = 'body';
		var sparams;
		selectElement.each(function (index, domElement) {
			sparams = params;
			var element = jQuery(domElement);
			if (element.data('placement')) {
				sparams.placement = element.data('placement');
			}
			if (element.hasClass('delay0')) {
				sparams.delay = {show: 0, hide: 0}
			}
			var data = element.data();
			if (data != null) {
				sparams = jQuery.extend(data, sparams);
			}
			element.popover(sparams);
		});
		return selectElement;
	},
	/**
	 * Function to check the maximum selection size of multiselect and update the results
	 * @params <object> multiSelectElement
	 * @params <object> select2 params
	 */

	registerChangeEventForMultiSelect: function (selectElement, params) {
		if (typeof selectElement == 'undefined') {
			return;
		}
		var instance = selectElement.data('select2');
		var limit = params.maximumSelectionLength;
		selectElement.on('change', function (e) {
			var data = instance.data()
			if (jQuery.isArray(data) && data.length >= limit) {
				instance.updateResults();
			}
		});

	},
	/**
	 * Function to get data of the child elements in serialized format
	 * @params <object> parentElement - element in which the data should be serialized. Can be selector , domelement or jquery object
	 * @params <String> returnFormat - optional which will indicate which format return value should be valid values "object" and "string"
	 * @return <object> - encoded string or value map
	 */
	getSerializedData: function (parentElement, returnFormat) {
		if (typeof returnFormat == 'undefined') {
			returnFormat = 'string';
		}

		parentElement = jQuery(parentElement);

		var encodedString = parentElement.children().serialize();
		if (returnFormat == 'string') {
			return encodedString;
		}
		var keyValueMap = {};
		var valueList = encodedString.split('&')

		for (var index in valueList) {
			var keyValueString = valueList[index];
			var keyValueArr = keyValueString.split('=');
			var nameOfElement = keyValueArr[0];
			var valueOfElement = keyValueArr[1];
			keyValueMap[nameOfElement] = decodeURIComponent(valueOfElement);
		}
		return keyValueMap;
	},
	showModalWindow: function (data, url, cb, paramsObject) {
		var thisInstance = this;
		var id = 'globalmodal';
		//null is also an object
		if (typeof data == 'object' && data != null && !(data instanceof jQuery)) {
			if (data.id != undefined) {
				id = data.id;
			}
			paramsObject = data.css;
			cb = data.cb;
			url = data.url;
			if (data.sendByAjaxCb != 'undefined') {
				var sendByAjaxCb = data.sendByAjaxCb;
			}
			data = data.data;
		}
		if (typeof url == 'function') {
			if (typeof cb == 'object') {
				paramsObject = cb;
			}
			cb = url;
			url = false;
		} else if (typeof url == 'object') {
			cb = function () {
			};
			paramsObject = url;
			url = false;
		}
		if (typeof cb != 'function') {
			cb = function () {
			}
		}
		if (typeof sendByAjaxCb != 'function') {
			var sendByAjaxCb = function () {
			}
		}

		var container = jQuery('#' + id);
		if (container.length) {
			container.remove();
		}
		container = jQuery('<div></div>');
		container.attr('id', id).addClass('modalContainer');

		var showModalData = function (data) {
			var params = {
				'show': true,
			};
			if (jQuery('#backgroundClosingModal').val() != 1) {
				params.backdrop = 'static';
			}
			if (typeof paramsObject == 'object') {
				container.css(paramsObject);
				params = jQuery.extend(params, paramsObject);
			}
			container.html(data);
			if(container.find('.modal').hasClass('static')){
				params.backdrop = 'static';
			}
			// In a modal dialog elements can be specified which can receive focus even though they are not descendants of the modal dialog. 
			$.fn.modal.Constructor.prototype.enforceFocus = function (e) {
				$(document).off('focusin.bs.modal') // guard against infinite focus loop
						.on('focusin.bs.modal', $.proxy(function (e) {
							if ($(e.target).hasClass('select2-search__field')) {
								return true;
							}
						}, this))
			};
			var modalContainer = container.find('.modal:first');
			modalContainer.modal(params);
			jQuery('body').append(container);
			app.changeSelectElementView(modalContainer);
			//register all select2 Elements
			app.showSelect2ElementView(modalContainer.find('select.select2'));
			app.showSelectizeElementView(modalContainer.find('select.selectize'));
			//register date fields event to show mini calendar on click of element
			app.registerEventForDatePickerFields(modalContainer);

			thisInstance.registerModalEvents(modalContainer, sendByAjaxCb);
			thisInstance.showPopoverElementView(modalContainer.find('.popoverTooltip'));
			thisInstance.registerDataTables(modalContainer.find('.dataTable'));
			modalContainer.one('shown.bs.modal', function () {
				var backdrop = jQuery('.modal-backdrop');
				if (backdrop.length > 1) {
					jQuery('.modal-backdrop:not(:first)').remove();
				}
				cb(modalContainer);
			})
		}
		if (data) {
			showModalData(data)

		} else {
			jQuery.get(url).then(function (response) {
				showModalData(response);
			});
		}
		container.one('hidden.bs.modal', function () {
			container.remove();
			var backdrop = jQuery('.modal-backdrop');
			var modalContainers = jQuery('.modalContainer');
			if (modalContainers.length == 0 && backdrop.length) {
				backdrop.remove();
			}
			if (backdrop.length > 0) {
				$('body').addClass('modal-open');
			}
		});
		return container;
	},
	/**
	 * Function which you can use to hide the modal
	 * This api assumes that we are using block ui plugin and uses unblock api to unblock it
	 */
	hideModalWindow: function (callback, id) {
		if (id == undefined) {
			id = 'globalmodal';
		}
		var container = jQuery('#' + id);
		if (container.length <= 0) {
			return;
		}
		if (typeof callback != 'function') {
			callback = function () {
			};
		}
		var modalContainer = container.find('.modal');
		modalContainer.modal('hide');
		var backdrop = jQuery('.modal-backdrop:last');
		var modalContainers = jQuery('.modalContainer');
		if (modalContainers.length == 0 && backdrop.length) {
			backdrop.remove();
		}
		modalContainer.one('hidden.bs.modal', callback);
	},
	registerModalEvents: function (container, sendByAjaxCb) {
		var form = container.find('form');
		var validationForm = false;
		if (form.hasClass("validateForm")) {
			form.validationEngine(app.validationEngineOptions);
			validationForm = true;
		}
		if (form.hasClass("sendByAjax")) {
			form.submit(function (e) {
				var save = true;
				e.preventDefault();
				if (validationForm && form.data('jqv').InvalidFields.length > 0) {
					app.formAlignmentAfterValidation(form);
					save = false;
				}
				if (save) {
					var progressIndicatorElement = jQuery.progressIndicator({
						blockInfo: {'enabled': true}
					});
					var formData = form.serializeFormData();
					AppConnector.request(formData).then(function (data) {
						sendByAjaxCb(formData, data);
						app.hideModalWindow();
						progressIndicatorElement.progressIndicator({'mode': 'hide'});
					})
				}
			});
		}
	},
	isHidden: function (element) {
		if (element.css('display') == 'none') {
			return true;
		}
		return false;
	},
	isInvisible: function (element) {
		if (element.css('visibility') == 'hidden') {
			return true;
		}
		return false;
	},
	/**
	 * Default validation eninge options
	 */
	validationEngineOptions: {
		// Avoid scroll decision and let it scroll up page when form is too big
		// Reference: http://www.position-absolute.com/articles/jquery-form-validator-because-form-validation-is-a-mess/
		scroll: false,
		promptPosition: 'topLeft',
		//to support validation for chosen select box
		prettySelect: true,
		useSuffix: "_chosen",
		usePrefix: "s2id_",
	},
	validationEngineOptionsForRecord: {
		scroll: false,
		promptPosition: 'topLeft',
		//to support validation for chosen select box
		prettySelect: true,
		useSuffix: "_chosen",
		usePrefix: "s2id_",
		validateNonVisibleFields: true,
		onBeforePromptType: function (field) {
			var block = field.closest('.blockContainer');
			if (block.find('.blockContent').is(":hidden")) {
				block.find('.blockHeader').click();
			}
		},
	},
	/**
	 * Function to push down the error message size when validation is invoked
	 * @params : form Element
	 */
	formAlignmentAfterValidation: function (form) {
		// to avoid hiding of error message under the fixed nav bar
		var formError = form.find(".formError:not('.greenPopup'):first")
		if (formError.length > 0) {
			var destination = formError.offset().top;
			var resizedDestnation = destination - 105;
			jQuery('html').animate({
				scrollTop: resizedDestnation
			}, 'slow');
		}
	},
	convertToDatePickerFormat: function (dateFormat) {
		switch (dateFormat) {
			case 'yyyy-mm-dd':
				return 'Y-m-d';
				break;
			case 'mm-dd-yyyy':
				return 'm-d-Y';
				break;
			case 'dd-mm-yyyy':
				return 'd-m-Y';
				break;
			case 'yyyy.mm.dd':
				return 'Y.m.d';
				break;
			case 'mm.dd.yyyy':
				return 'm.d.Y';
				break;
			case 'dd.mm.yyyy':
				return 'd.m.Y';
				break;
			case 'yyyy/mm/dd':
				return 'Y/m/d';
				break;
			case 'mm/dd/yyyy':
				return 'm/d/Y';
				break;
			case 'dd/mm/yyyy':
				return 'd/m/Y';
				break;
		}
	},
	convertTojQueryDatePickerFormat: function (dateFormat) {
		var i = 0;
		var dotMode = '-';
		if (dateFormat.indexOf("-") != -1) {
			dotMode = '-';
		}
		if (dateFormat.indexOf(".") != -1) {
			dotMode = '.';
		}
		if (dateFormat.indexOf("/") != -1) {
			dotMode = '/';
		}
		var splitDateFormat = dateFormat.split(dotMode);
		for (var i in splitDateFormat) {
			var sectionDate = splitDateFormat[i];
			var sectionCount = sectionDate.length;
			if (sectionCount == 4) {
				var strippedString = sectionDate.substring(0, 2);
				splitDateFormat[i] = strippedString;
			}
		}
		var joinedDateFormat = splitDateFormat.join(dotMode);
		return joinedDateFormat;
	},
	getDateInVtigerFormat: function (dateFormat, dateObject) {
		var finalFormat = app.convertTojQueryDatePickerFormat(dateFormat);
		var date = jQuery.datepicker.formatDate(finalFormat, dateObject);
		return date;
	},
	/*
	 * Converts user formated date to database format yyyy-mm-dd
	 */
	getDateInDBInsertFormat: function (dateFormat, dateString) {
		var i = 0;
		var dotMode = '-';
		if (dateFormat.indexOf("-") != -1) {
			dotMode = '-';
		}
		if (dateFormat.indexOf(".") != -1) {
			dotMode = '.';
		}
		if (dateFormat.indexOf("/") != -1) {
			dotMode = '/';
		}

		var dateFormatParts = dateFormat.split(dotMode);
		var dateParts = dateString.split(dotMode);
		var day = '';
		var month = '';
		var year = '';

		for (i in dateFormatParts) {
			var sectionDate = dateFormatParts[i];

			switch (sectionDate) {
				case 'dd':
					day = dateParts[i];
					break;

				case 'mm':
					month = dateParts[i];
					break;

				case 'yyyy':
					year = dateParts[i];
					break;
			}
		}

		return year + '-' + month + '-' + day;
	},
	registerEventForTextAreaFields: function (parentElement) {
		if (typeof parentElement == 'undefined') {
			parentElement = jQuery('body');
		}

		parentElement = jQuery(parentElement);

		if (parentElement.is('textarea')) {
			var element = parentElement;
		} else {
			var element = jQuery('textarea', parentElement);
		}
		if (element.length == 0) {
			return;
		}
		element.autosize();
	},
	registerEventForDatePickerFields: function (parentElement, registerForAddon, customParams) {
		if (typeof parentElement == 'undefined') {
			parentElement = jQuery('body');
		}
		if (typeof registerForAddon == 'undefined') {
			registerForAddon = true;
		}

		parentElement = jQuery(parentElement);

		if (parentElement.hasClass('dateField')) {
			var element = parentElement;
		} else {
			var element = jQuery('.dateField', parentElement);
		}
		if (element.length == 0) {
			return;
		}
		if (registerForAddon == true) {
			var parentDateElem = element.closest('.date');
			jQuery('.input-group-addon:not(.notEvent)', parentDateElem).on('click', function (e) {
				var elem = jQuery(e.currentTarget);
				//Using focus api of DOM instead of jQuery because show api of datePicker is calling e.preventDefault
				//which is stopping from getting focus to input element
				elem.closest('.date').find('input.dateField').get(0).focus();
			});
		}
		var dateFormat = element.data('dateFormat');
		var vtigerDateFormat = app.convertToDatePickerFormat(dateFormat);
		var language = jQuery('body').data('language');
		var lang = language.split('_');

		//Default first day of the week
		var defaultFirstDay = jQuery('#start_day').val();
		if (defaultFirstDay == '' || typeof (defaultFirstDay) == 'undefined') {
			var convertedFirstDay = 1
		} else {
			convertedFirstDay = this.weekDaysArray[defaultFirstDay];
		}
		var params = {
			format: vtigerDateFormat,
			calendars: 1,
			locale: $.fn.datepicker.dates[lang[0]],
			starts: convertedFirstDay,
			eventName: 'focus',
			onChange: function (formated) {
				var element = jQuery(this).data('datepicker').el;
				element = jQuery(element);
				var datePicker = jQuery('#' + jQuery(this).data('datepicker').id);
				var viewDaysElement = datePicker.find('table.datepickerViewDays');
				//If it is in day mode and the prev value is not eqaul to current value
				//Second condition is manily useful in places where user navigates to other month
				if (viewDaysElement.length > 0 && element.val() != formated) {
					element.DatePickerHide();
					element.blur();
				}
				element.data('prevVal', element.val());
				element.val(formated).trigger('change').focusout();
			},
			onBeforeShow: function (formated) {
				element.each(function (index, domElement) {
					var jQelement = jQuery(domElement);
					if (jQelement[0] != document.activeElement) {
						jQelement.DatePickerHide();
						jQelement.blur();
					}
				});

			},
		}
		if (typeof customParams != 'undefined') {
			var params = jQuery.extend(params, customParams);
		}
		element.each(function (index, domElement) {
			var jQelement = jQuery(domElement);
			var dateObj = new Date();
			var selectedDate = app.getDateInVtigerFormat(dateFormat, dateObj);
			//Take the element value as current date or current date
			if (jQelement.val() != '') {
				selectedDate = jQelement.val();
			}
			params.date = selectedDate;
			params.current = selectedDate;
			jQelement.data('prevVal', jQelement.val());
			jQelement.DatePicker(params)
		});

	},
	registerEventForDateFields: function (parentElement) {
		if (typeof parentElement == 'undefined') {
			parentElement = jQuery('body');
		}

		parentElement = jQuery(parentElement);

		if (parentElement.hasClass('dateField')) {
			var element = parentElement;
		} else {
			var element = jQuery('.dateField', parentElement);
		}
		element.datepicker({'autoclose': true}).on('changeDate', function (ev) {
			var currentElement = jQuery(ev.currentTarget);
			var dateFormat = currentElement.data('dateFormat');
			var finalFormat = app.getDateInVtigerFormat(dateFormat, ev.date);
			var date = jQuery.datepicker.formatDate(finalFormat, ev.date);
			currentElement.val(date);
		});
	},
	registerEventForClockPicker: function (object) {
		if (typeof object === 'undefined') {
			var elementClockBtn = $('.clockPicker');
			var formatTime = app.getMainParams('userTimeFormat');
		} else {
			elementClockBtn = object;
			var formatTime = elementClockBtn.data('format');
		}
		formatTime = formatTime == 12 ? true : false;
		var params = {
			placement: 'bottom',
			autoclose: true,
			twelvehour: formatTime,
			minutestep: 5,
			ampmSubmit: false,
		};

		var parentTimeElem = elementClockBtn.closest('.time');
		jQuery('.input-group-addon', parentTimeElem).on('click', function (e) {
			var elem = jQuery(e.currentTarget);
			e.stopPropagation();
			var tempElement = elem.closest('.time').find('input.clockPicker');
			if (tempElement.attr('disabled') != 'disabled') {
				tempElement.clockpicker('show');
			}
		});
		elementClockBtn.clockpicker(params);
	},
	registerDataTables: function (table) {
		if ($.fn.dataTable == undefined) {
			return false;
		}
		if (table.length == 0) {
			return false;
		}
		$.extend($.fn.dataTable.defaults, {
			language: {
				sLengthMenu: app.vtranslate('JS_S_LENGTH_MENU'),
				sZeroRecords: app.vtranslate('JS_NO_RESULTS_FOUND'),
				sInfo: app.vtranslate('JS_S_INFO'),
				sInfoEmpty: app.vtranslate('JS_S_INFO_EMPTY'),
				sSearch: app.vtranslate('JS_SEARCH'),
				sEmptyTable: app.vtranslate('JS_NO_RESULTS_FOUND'),
				sInfoFiltered: app.vtranslate('JS_S_INFO_FILTERED'),
				sLoadingRecords: app.vtranslate('JS_LOADING_OF_RECORDS'),
				sProcessing: app.vtranslate('JS_LOADING_OF_RECORDS'),
				oPaginate: {
					sFirst: app.vtranslate('JS_S_FIRST'),
					sPrevious: app.vtranslate('JS_S_PREVIOUS'),
					sNext: app.vtranslate('JS_S_NEXT'),
					sLast: app.vtranslate('JS_S_LAST')
				},
				oAria: {
					sSortAscending: app.vtranslate('JS_S_SORT_ASCENDING'),
					sSortDescending: app.vtranslate('JS_S_SORT_DESCENDING')
				}
			}
		});
		return table.DataTable();
	},
	/**
	 * Function which will register time fields
	 *
	 * @params : container - jquery object which contains time fields with class timepicker-default or itself can be time field
	 *			 registerForAddon - boolean value to register the event for Addon or not
	 *			 params  - params for the  plugin
	 *
	 * @return : container to support chaining
	 */
	registerEventForTimeFields: function (container, registerForAddon, params) {

		if (typeof cotainer == 'undefined') {
			container = jQuery('body');
		}
		if (typeof registerForAddon == 'undefined') {
			registerForAddon = true;
		}

		container = jQuery(container);

		if (container.hasClass('timepicker-default')) {
			var element = container;
		} else {
			var element = container.find('.timepicker-default');
		}

		if (registerForAddon == true) {
			var parentTimeElem = element.closest('.time');
			jQuery('.input-group-addon', parentTimeElem).on('click', function (e) {
				var elem = jQuery(e.currentTarget);
				elem.closest('.time').find('.timepicker-default').focus();
			});
		}

		if (typeof params == 'undefined') {
			params = {};
		}

		var timeFormat = element.data('format');
		if (timeFormat == '24') {
			timeFormat = 'H:i';
		} else {
			timeFormat = 'h:i A';
		}
		var defaultsTimePickerParams = {
			'timeFormat': timeFormat,
			'className': 'timePicker'
		};
		var params = jQuery.extend(defaultsTimePickerParams, params);

		element.timepicker(params);

		return container;
	},
	/**
	 * Function to destroy time fields
	 */
	destroyTimeFields: function (container) {

		if (typeof cotainer == 'undefined') {
			container = jQuery('body');
		}

		if (container.hasClass('timepicker-default')) {
			var element = container;
		} else {
			var element = container.find('.timepicker-default');
		}
		element.data('timepicker-list', null);
		return container;
	},
	/**
	 * Function to get the chosen element from the raw select element
	 * @params: select element
	 * @return : chosenElement - corresponding chosen element
	 */
	getChosenElementFromSelect: function (selectElement) {
		var selectId = selectElement.attr('id');
		var chosenEleId = selectId + "_chosen";
		return jQuery('#' + chosenEleId);
	},
	/**
	 * Function to get the select2 element from the raw select element
	 * @params: select element
	 * @return : select2Element - corresponding select2 element
	 */
	getSelect2ElementFromSelect: function (selectElement) {
		var selectId = selectElement.attr('id');
		//since select2 will add s2id_ to the id of select element
		var select2EleId = 'select2-' + selectId + '-container';
		return jQuery('#' + select2EleId).closest('.select2-container');
	},
	/**
	 * Function to get the select element from the chosen element
	 * @params: chosen element
	 * @return : selectElement - corresponding select element
	 */
	getSelectElementFromChosen: function (chosenElement) {
		var chosenId = chosenElement.attr('id');
		var selectEleIdArr = chosenId.split('_chosen');
		var selectEleId = selectEleIdArr['0'];
		return jQuery('#' + selectEleId);
	},
	/**
	 * Function to set with of the element to parent width
	 * @params : jQuery element for which the action to take place
	 */
	setInheritWidth: function (elements) {
		jQuery(elements).each(function (index, element) {
			var parentWidth = jQuery(element).parent().width();
			jQuery(element).width(parentWidth);
		});
	},
	initGuiders: function (list) {
	},
	showScrollBar: function (element, options) {
		if (typeof options == 'undefined') {
			options = {};
		}
		if (typeof options.height == 'undefined') {
			options.height = element.css('height');
		}

		return element.slimScroll(options);
	},
	showHorizontalScrollBar: function (element, options) {
		if (typeof options == 'undefined') {
			options = {};
		}
		var params = {
			horizontalScroll: true,
			theme: "dark-thick",
			advanced: {
				autoExpandHorizontalScroll: true
			}
		}
		if (typeof options != 'undefined') {
			var params = jQuery.extend(params, options);
		}
		return element.mCustomScrollbar(params);
	},
	/**
	 * Function returns translated string
	 */
	vtranslate: function (key) {
		if (app.languageString[key] != undefined) {
			return app.languageString[key];
		} else {
			var strings = jQuery('#js_strings').text();
			if (strings != '') {
				app.languageString = JSON.parse(strings);
				if (key in app.languageString) {
					return app.languageString[key];
				}
			}
		}
		return key;
	},
	/**
	 * Function will return the current users layout + skin path
	 * @param <string> img - image name
	 * @return <string>
	 */
	vimage_path: function (img) {
		return app.getLayoutPath() + '/skins/images/' + img;
	},
	/*
	 * Cache API on client-side
	 */
	cacheNSKey: function (key) { // Namespace in client-storage
		return 'yf.' + key;
	},
	cacheGet: function (key, defvalue) {
		key = this.cacheNSKey(key);
		return jQuery.jStorage.get(key, defvalue);
	},
	cacheSet: function (key, value, ttl) {
		key = this.cacheNSKey(key);
		jQuery.jStorage.set(key, value);
		if (ttl) {
			jQuery.jStorage.setTTL(key, ttl);
		}
	},
	cacheClear: function (key) {
		key = this.cacheNSKey(key);
		return jQuery.jStorage.deleteKey(key);
	},
	moduleCacheSet: function (key, value, ttl) {
		if (ttl == undefined) {
			ttl = 12 * 60 * 60 * 1000;
		}
		var orgKey = key;
		key = this.getModuleName() + '_' + key;
		this.cacheSet(key, value, ttl);

		var cacheKey = 'mCache' + this.getModuleName();
		var moduleCache = this.cacheGet(cacheKey);
		if (moduleCache == null) {
			moduleCache = [];
		} else {
			moduleCache = moduleCache.split(',');
		}
		moduleCache.push(orgKey);
		this.cacheSet(cacheKey, Vtiger_Helper_Js.unique(moduleCache).join(','));
	},
	moduleCacheGet: function (key) {
		return this.cacheGet(this.getModuleName() + '_' + key);
	},
	moduleCacheKeys: function () {
		var cacheKey = 'mCache' + this.getModuleName();
		var modules = this.cacheGet(cacheKey)
		if (modules) {
			return modules.split(',');
		}
		return [];
	},
	moduleCacheClear: function (key) {
		var thisInstance = this;
		var moduleName = this.getModuleName();
		var cacheKey = 'mCache' + moduleName;
		var moduleCache = this.cacheGet(cacheKey);
		if (moduleCache == null) {
			moduleCache = [];
		} else {
			moduleCache = moduleCache.split(',');
		}
		$.each(moduleCache, function (index, value) {
			thisInstance.cacheClear(moduleName + '_' + value);
		});
		thisInstance.cacheClear(cacheKey);
	},
	htmlEncode: function (value) {
		if (value) {
			return jQuery('<div />').text(value).html();
		} else {
			return '';
		}
	},
	htmlDecode: function (value) {
		if (value) {
			return $('<div />').html(value).text();
		} else {
			return '';
		}
	},
	/**
	 * Function places an element at the center of the page
	 * @param <jQuery Element> element
	 */
	placeAtCenter: function (element) {
		element.css("position", "absolute");
		element.css("top", ((jQuery(window).height() - element.outerHeight()) / 2) + jQuery(window).scrollTop() + "px");
		element.css("left", ((jQuery(window).width() - element.outerWidth()) / 2) + jQuery(window).scrollLeft() + "px");
	},
	getvalidationEngineOptions: function (select2Status) {
		return app.validationEngineOptions;
	},
	/**
	 * Function to notify UI page ready after AJAX changes.
	 * This can help in re-registering the event handlers (which was done during ready event).
	 */
	notifyPostAjaxReady: function () {
		jQuery(document).trigger('postajaxready');
	},
	/**
	 * Listen to xready notiications.
	 */
	listenPostAjaxReady: function (callback) {
		jQuery(document).on('postajaxready', callback);
	},
	/**
	 * Form function handlers
	 */
	setFormValues: function (kv) {
		for (var k in kv) {
			jQuery(k).val(kv[k]);
		}
	},
	setRTEValues: function (kv) {
		for (var k in kv) {
			var rte = CKEDITOR.instances[k];
			if (rte)
				rte.setData(kv[k]);
		}
	},
	/**
	 * Function returns the javascript controller based on the current view
	 */
	getPageController: function () {
		var moduleName = app.getModuleName();
		var view = app.getViewName()
		var parentModule = app.getParentModuleName();

		var moduleClassName = parentModule + "_" + moduleName + "_" + view + "_Js";
		if (typeof window[moduleClassName] == 'undefined') {
			moduleClassName = parentModule + "_Vtiger_" + view + "_Js";
		}
		if (typeof window[moduleClassName] == 'undefined') {
			moduleClassName = moduleName + "_" + view + "_Js";
		}
		var extendModules = jQuery('#extendModules').val();
		if (typeof window[moduleClassName] == 'undefined' && extendModules != undefined) {
			moduleClassName = extendModules + "_" + view + "_Js";
		}
		if (typeof window[moduleClassName] == 'undefined') {
			moduleClassName = "Vtiger_" + view + "_Js";
		}
		if (typeof window[moduleClassName] != 'undefined') {
			return new window[moduleClassName]();
		}
	},
	/**
	 * Function to decode the encoded htmlentities values
	 */
	getDecodedValue: function (value) {
		return jQuery('<div></div>').html(value).text();
	},
	/**
	 * Function to check whether the color is dark or light
	 */
	getColorContrast: function (hexcolor) {
		var r = parseInt(hexcolor.substr(0, 2), 16);
		var g = parseInt(hexcolor.substr(2, 2), 16);
		var b = parseInt(hexcolor.substr(4, 2), 16);
		var yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
		return (yiq >= 128) ? 'light' : 'dark';
	},
	updateRowHeight: function () {
		var rowType = jQuery('#row_type').val();
		if (rowType.length <= 0) {
			//Need to update the row height
			var widthType = app.cacheGet('widthType', 'mediumWidthType');
			var serverWidth = widthType;
			switch (serverWidth) {
				case 'narrowWidthType' :
					serverWidth = 'narrow';
					break;
				case 'wideWidthType' :
					serverWidth = 'wide';
					break;
				default :
					serverWidth = 'medium';
			}
			var userid = jQuery('#current_user_id').val();
			var params = {
				'module': 'Users',
				'action': 'SaveAjax',
				'record': userid,
				'value': serverWidth,
				'field': 'rowheight'
			};
			AppConnector.request(params).then(function () {
				jQuery(rowType).val(serverWidth);
			});
		}
	},
	getCookie: function (c_name) {
		var c_value = document.cookie;
		var c_start = c_value.indexOf(" " + c_name + "=");
		if (c_start == -1)
		{
			c_start = c_value.indexOf(c_name + "=");
		}
		if (c_start == -1)
		{
			c_value = null;
		} else
		{
			c_start = c_value.indexOf("=", c_start) + 1;
			var c_end = c_value.indexOf(";", c_start);
			if (c_end == -1)
			{
				c_end = c_value.length;
			}
			c_value = unescape(c_value.substring(c_start, c_end));
		}
		return c_value;
	},
	setCookie: function (c_name, value, exdays) {
		var exdate = new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value = escape(value) + ((exdays == null) ? "" : "; expires=" + exdate.toUTCString());
		document.cookie = c_name + "=" + c_value;
	},
	getUrlVar: function (varName) {
		var getVar = function () {
			var vars = {};
			var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
				vars[key] = value;
			});
			return vars;
		};

		return getVar()[varName];
	},
	getStringDate: function (date) {
		var d = date.getDate();
		var m = date.getMonth() + 1;
		var y = date.getFullYear();

		d = (d <= 9) ? ("0" + d) : d;
		m = (m <= 9) ? ("0" + m) : m;
		return y + "-" + m + "-" + d;
	},
	formatDate: function (date) {
		var y = date.getFullYear(),
				m = date.getMonth() + 1,
				d = date.getDate(),
				h = date.getHours(),
				i = date.getMinutes(),
				s = date.getSeconds();
		return y + '-' + this.formatDateZ(m) + '-' + this.formatDateZ(d) + ' ' + this.formatDateZ(h) + ':' + this.formatDateZ(i) + ':' + this.formatDateZ(s);
	},
	formatDateZ: function (i) {
		return (i <= 9 ? '0' + i : i);
	},
	howManyDaysFromDate: function (time) {
		var fromTime = time.getTime();
		var today = new Date();
		var toTime = new Date(today.getFullYear(), today.getMonth(), today.getDate()).getTime();
		return Math.floor(((toTime - fromTime) / (1000 * 60 * 60 * 24))) + 1;
	},
	saveAjax: function (mode, param, addToParams) {
		var aDeferred = jQuery.Deferred();
		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'SaveAjax';
		params['mode'] = mode;
		params['param'] = param;
		if (addToParams != undefined) {
			for (var i in addToParams) {
				params[i] = addToParams[i];
			}
		}
		AppConnector.request(params).then(
				function (data) {
					aDeferred.resolve(data);
				},
				function (error) {
					aDeferred.reject();
				}
		);
		return aDeferred.promise();
	},
	showBtnSwitch: function (selectElement, params) {
		if (typeof params == 'undefined') {
			params = {};
		}
		selectElement.bootstrapSwitch(params);
		return selectElement;
	},
	generateRandomChar: function () {
		var chars, newchar, rand;
		chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZ";
		rand = Math.floor(Math.random() * chars.length);
		return newchar = chars.substring(rand, rand + 1);
	},
	getMainParams: function (param, json) {
		if (app.cacheParams[param] == undefined) {
			var value = $('#' + param).val();
			app.cacheParams[param] = value;
		}
		var value = app.cacheParams[param];
		if (json) {
			if (value != '') {
				value = $.parseJSON(value);
			} else {
				value = [];
			}
		}
		return value;
	},
	setMainParams: function (param, value) {
		app.cacheParams[param] = value;
		$('#' + param).val(value);
	},
	parseNumberToShow: function (val) {
		if (val == undefined) {
			val = 0;
		}
		var numberOfDecimal = parseInt(app.getMainParams('numberOfCurrencyDecimal'));
		var decimalSeparator = app.getMainParams('currencyDecimalSeparator');
		var groupSeparator = app.getMainParams('currencyGroupingSeparator');
		var groupingPattern = app.getMainParams('currencyGroupingPattern');
		val = parseFloat(val).toFixed(numberOfDecimal);
		var a = val.toString().split('.');
		var integer = a[0];
		var decimal = a[1];

		if (groupingPattern == '123,456,789') {
			integer = integer.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1" + groupSeparator);
		} else if (groupingPattern == '123456,789') {
			var t = integer.slice(-3);
			var o = integer.slice(0, -3);
			integer = o + groupSeparator + t;
		} else if (groupingPattern == '12,34,56,789') {
			var t = integer.slice(-3);
			var o = integer.slice(0, -3);
			integer = o.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1" + groupSeparator) + groupSeparator + t;
		}
		return integer + decimalSeparator + decimal;
	},
	parseNumberToFloat: function (val) {
		var numberOfDecimal = parseInt(app.getMainParams('numberOfCurrencyDecimal'));
		var groupSeparator = app.getMainParams('currencyGroupingSeparator');
		var decimalSeparator = app.getMainParams('currencyDecimalSeparator');
		if (val == undefined || val == '') {
			val = 0;
		}
		val = val.toString();
		val = val.split(groupSeparator).join("");
		val = val.replace(/\s/g, "").replace(decimalSeparator, ".");
		return parseFloat(val);
	},
	errorLog: function (error, err, errorThrown) {
		if (typeof error == 'object' && error.responseText) {
			error = error.responseText;
		}
		if (typeof error == 'object' && error.statusText) {
			error = error.statusText;
		}
		if (error) {
			console.error(error);
		}
		if (err) {
			console.error(err);
		}
		if (errorThrown) {
			console.error(errorThrown);
		}
		console.log('-----------------');
	},
	registerModal: function (container) {
		if (typeof container == 'undefined') {
			container = jQuery('body');
		}
		container.off('click', 'button.showModal, a.showModal').on('click', 'button.showModal, a.showModal', function (e) {
			e.preventDefault();
			var currentElement = jQuery(e.currentTarget);
			var url = currentElement.data('url');

			if (typeof url != 'undefined') {
				if (currentElement.hasClass('popoverTooltip')) {
					currentElement.popover('hide');
				}
				currentElement.attr("disabled", true);
				var modalWindowParams = {
					url: url,
					cb: function (container) {
						var call = currentElement.data('cb');
						if (typeof call !== 'undefined') {
							if(call.indexOf('.') != -1) {
								var callerArray = call.split('.');
								if(typeof window[callerArray[0]] === 'object') {
									window[callerArray[0]][callerArray[1]](container);
								}
							} else {
								if (typeof window[call] === 'function') {
									window[call](container);
								}
							}
							
						}
						currentElement.removeAttr("disabled");
					}
				}
				var id = 'globalmodal';
				if ($('#' + id).length) {
					var numberGlobalModal = 1;
					while($('#' + id).length) {
						id = 'globalmodal' + numberGlobalModal++;
					}
					modalWindowParams['id'] = id;
				}
				app.showModalWindow(modalWindowParams);
			}
			e.stopPropagation();
		});
	},
	playSound: function (action) {
		var soundsConfig = app.getMainParams('sounds');
		soundsConfig = JSON.parse(soundsConfig);
		if (soundsConfig['IS_ENABLED']) {
			var audio = new Audio(app.getLayoutPath() + '/sounds/' + soundsConfig[action]);
			audio.play();
		}
	},
	registerSticky: function () {
		var elements = jQuery('.stick');
		elements.each(function () {
			var currentElement = jQuery(this);
			var position = currentElement.data('position');
			if (position == 'top') {
				var offsetTop = currentElement.offset().top - 50;
				jQuery('.mainBody').scroll(function () {
					if ($(this).scrollTop() > offsetTop)
						currentElement.css({
							'position': 'fixed',
							'top': '50px',
							'width': currentElement.width()
						});
					else if ($(this).scrollTop() <= offsetTop)
						currentElement.css({
							'position': '',
							'top': '',
							'width': ''
						});
				});
			}
			if (position == 'bottom') {
				var offsetTop = currentElement.offset().top - jQuery(window).height();
				jQuery('.mainBody').scroll(function () {
					if ($(this).scrollTop() < offsetTop)
						currentElement.css({
							'position': 'fixed',
							'bottom': '33px',
							'width': currentElement.width()
						});
					else if ($(this).scrollTop() >= offsetTop)
						currentElement.css({
							'position': '',
							'bottom': '',
							'width': ''
						});
				});
			}
		});
	},
	registerMoreContent: function (container) {
		container.on('click', function (e) {
			var btn = jQuery(e.currentTarget);
			var content = btn.closest('.moreContent');
			content.find('.teaserContent').toggleClass('hide');
			content.find('.fullContent').toggleClass('hide');
			if (btn.text() == btn.data('on')) {
				btn.text(btn.data('off'));
			} else {
				btn.text(btn.data('on'));
			}
		});
	},
	getScreenHeight: function (percantage) {
		if (typeof percantage == 'undefined') {
			percantage = 100;
		}
		return jQuery(window).height() * percantage / 100;
	},
	registerCopyClipboard: function (key) {
		if (key == undefined) {
			key = '.clipboard';
		}
		new Clipboard(key, {
			text: function (trigger) {
				Vtiger_Helper_Js.showPnotify({
					text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
					type: 'success'
				});
				trigger = jQuery(trigger);
				var element = jQuery(trigger.data('copyTarget'));
				if (trigger.data('copyType') != undefined) {
					if (element.is("select")) {
						var val = element.find('option:selected').data(trigger.data('copyType'));
					} else {
						var val = element.data(trigger.data('copyType'));
					}
				} else {
					var val = element.val();
				}
				return val;
			}
		});
	},
}
jQuery(document).ready(function () {
	app.changeSelectElementView();
	//register all select2 Elements
	jQuery('body').find('select.select2').each(function (e) {
		app.showSelect2ElementView($(this));
	});
	app.showSelectizeElementView(jQuery('body').find('select.selectize'));
	app.showPopoverElementView(jQuery('body').find('.popoverTooltip'));
	app.showBtnSwitch(jQuery('body').find('.switchBtn'));
	app.registerSticky();
	app.registerMoreContent(jQuery('body').find('button.moreBtn'));
	app.registerModal();
	//Updating row height
	app.updateRowHeight();
	String.prototype.toCamelCase = function () {
		var value = this.valueOf();
		return  value.charAt(0).toUpperCase() + value.slice(1).toLowerCase()
	}
	// in IE resize option for textarea is not there, so we have to use .resizable() api
	if (/MSIE/.test(navigator.userAgent) || (/Trident/).test(navigator.userAgent)) {
		jQuery('textarea').resizable();
	}
	// Instantiate Page Controller
	var pageController = app.getPageController();
	if (pageController)
		pageController.registerEvents();
});
$.fn.getNumberFromValue = function () {
	return app.parseNumberToFloat($(this).val());
}
$.fn.getNumberFromText = function () {
	return app.parseNumberToFloat($(this).text());
}
