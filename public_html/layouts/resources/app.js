/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
App = {};
app = {
	/**
	 * variable stores client side language strings
	 */
	languageString: [],
	cacheParams: [],
	modalEvents: [],
	childFrame: false,
	event: new function () {
		this.el = $({});
		this.trigger = function () {
			this.el.trigger(arguments[0], Array.prototype.slice.call(arguments, 1));
		}
		this.on = function () {
			this.el.on.apply(this.el, arguments);
		}
		this.one = function () {
			this.el.one.apply(this.el, arguments);
		}
		this.off = function () {
			this.el.off.apply(this.el, arguments);
		}
	},
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
		return $('body').data('language');
	},
	/**
	 * Function to get page title
	 */
	getPageTitle: function () {
		return document.title;
	},
	/**
	 * Function gets current window parent
	 * @returns {object}
	 */
	getWindowParent() {
		if (typeof window.frames[0] !== "undefined" && typeof window.frames[0].app !== "undefined" && window.frames[0].app.childFrame) {
			return window.frames[0];
		} else {
			return window;
		}
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
		return $('.bodyContents');
	},
	hidePopover: function (element) {
		if (typeof element === "undefined") {
			element = $('body .js-popover-tooltip');
		}
		element.popover('hide');
	},
	showPopoverElementView: function (selectElement, params) {
		if (typeof params === "undefined") {
			params = {
				trigger: 'manual',
				placement: 'auto',
				html: true,
				template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
			};
		}
		params.container = 'body';
		params.delay = {"show": 300, "hide": 100};
		var sparams;
		selectElement.each(function (index, domElement) {
			sparams = params;
			var element = $(domElement);
			if (element.data('placement')) {
				sparams.placement = element.data('placement');
			}
			if (element.data('class')) {
				sparams.template = '<div class="popover ' + element.data('class') + '" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
			}
			if (element.hasClass('delay0')) {
				sparams.delay = {show: 0, hide: 0}
			}
			var data = element.data();
			if (data != null) {
				sparams = $.extend(sparams, data);
			}
			element.popover(sparams);
			element.hoverIntent({
				timeout: 150,
				over: function () {
					const self = this;
					$(this).popover("show");
					$(".popover").on("mouseleave", function () {
						$(self).popover('hide');
					});
				},
				out: function () {
					if (!$(".popover:hover").length) {
						$(this).popover('hide');
					}
				}
			});
		});
		return selectElement;
	},
	/**
	 * Function to check the maximum selection size of multiselect and update the results
	 * @params <object> multiSelectElement
	 * @params <object> select2 params
	 */

	registerChangeEventForMultiSelect: function (selectElement, params) {
		if (typeof selectElement === "undefined") {
			return;
		}
		var instance = selectElement.data('select2');
		var limit = params.maximumSelectionLength;
		selectElement.on('change', function (e) {
			var data = instance.data()
			if ($.isArray(data) && data.length >= limit) {
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
		if (typeof returnFormat === "undefined") {
			returnFormat = 'string';
		}

		parentElement = $(parentElement);

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
	/**
	 * Function animates bootstrap modal with animate.css
	 * @params: jQuery object with class .modal,
	 * @params: string with animation name,
	 * @params: string with animation name,
	 */
	animateModal(modal, openAnimation, closeAnimation) {
		modal.on('show.bs.modal', function (e) {
			modal.removeClass(`animated ${closeAnimation}`);
			modal.addClass(`animated ${openAnimation}`);
		});
		modal.on('hide.bs.modal', function (e) {
			modal.removeClass(`animated ${openAnimation}`);
			modal.addClass(`animated ${closeAnimation}`);
		});
	},
	showModalData(data, container, paramsObject, cb, url, sendByAjaxCb) {
		const thisInstance = this;
		let params = {
			show: true
		};
		if ($('#backgroundClosingModal').val() !== 1) {
			params.backdrop = true;
		}
		if (typeof paramsObject === 'object') {
			container.css(paramsObject);
			params = $.extend(params, paramsObject);
		}
		container.html(data);
		if (container.find('.modal').hasClass('static')) {
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
		const modalContainer = container.find('.modal:first');
		modalContainer.one('shown.bs.modal', function () {
			if ($('.modal-backdrop').length > 1) {
				$('.modal-backdrop:not(:first)').remove();
			}
			cb(modalContainer);
			App.Fields.Picklist.showSelect2ElementView(modalContainer.find('select.select2'));
			App.Fields.Picklist.showSelectizeElementView(modalContainer.find('select.selectize'));
			App.Fields.Picklist.showChoosenElementView(modalContainer.find('select.chzn-select'));
			App.Fields.Date.register(modalContainer);
			new App.Fields.Text.Editor(modalContainer.find('.js-editor'), {
				height: '5em',
				toolbar: 'Min'
			});
		});
		modalContainer.modal(params);
		$('body').append(container);
		thisInstance.registerModalEvents(modalContainer, sendByAjaxCb);
		thisInstance.showPopoverElementView(modalContainer.find('.js-popover-tooltip'));
		thisInstance.registerDataTables(modalContainer.find('.dataTable'));
	},
	showModalWindow: function (data, url, cb, paramsObject) {
		if (window.parent !== window) {
			this.childFrame = true;
			window.parent.app.showModalWindow(data, url, cb, paramsObject);
			return;
		}
		const thisInstance = this;
		Window.lastModalId = 'modal_' + Math.random().toString(36).substr(2, 9);
		//null is also an object
		if (typeof data === 'object' && data != null && !(data instanceof $)) {
			if (data.id != undefined) {
				Window.lastModalId = data.id;
			}
			paramsObject = data.css;
			cb = data.cb;
			url = data.url;
			if (data.sendByAjaxCb !== "undefined") {
				var sendByAjaxCb = data.sendByAjaxCb;
			}
			data = data.data;
		}
		if (typeof url === 'function') {
			if (typeof cb === 'object') {
				paramsObject = cb;
			}
			cb = url;
			url = false;
		} else if (typeof url === 'object') {
			cb = function () {
			};
			paramsObject = url;
			url = false;
		}
		if (typeof cb !== 'function') {
			cb = function () {
			}
		}
		if (typeof sendByAjaxCb !== 'function') {
			var sendByAjaxCb = function () {
			}
		}
		// prevent duplicate hash generation
		let container = $('#' + Window.lastModalId);
		if (container.length) {
			container.remove();
		}
		container = $('<div></div>');
		container.attr('id', Window.lastModalId).addClass('modalContainer js-modal-container');
		container.one('hidden.bs.modal', function () {
			container.remove();
			var backdrop = $('.modal-backdrop');
			var modalContainers = $('.modalContainer');
			if (modalContainers.length == 0 && backdrop.length) {
				backdrop.remove();
			}
			if (backdrop.length > 0) {
				$('body').addClass('modal-open');
			}
		});
		if (data) {
			thisInstance.showModalData(data, container, paramsObject, cb, url, sendByAjaxCb);
		} else {
			$.get(url).then(function (response) {
				thisInstance.showModalData(response, container, paramsObject, cb, url, sendByAjaxCb);
			});
		}
		return container;
	},
	/**
	 * Function which you can use to hide the modal
	 * This api assumes that we are using block ui plugin and uses unblock api to unblock it
	 */
	hideModalWindow: function (callback, id) {
		let container;
		if (typeof callback === 'object') {
			container = callback;
		} else if (id == undefined) {
			container = $('.modalContainer');
		} else {
			container = $('#' + id);
		}
		if (container.length <= 0) {
			return;
		}
		if (typeof callback !== 'function') {
			callback = function () {
			};
		}
		let modalContainer = container.find('.modal');
		modalContainer.modal('hide');
		let backdrop = $('.modal-backdrop:last');
		if ($('.modalContainer').length == 0 && backdrop.length) {
			backdrop.remove();
		}
		modalContainer.one('hidden.bs.modal', callback);
	},
	registerModalController: function () {
		let modalContainer = $('#' + Window.lastModalId + ' .js-modal-data');
		let modalClass = modalContainer.data('module') + '_' + modalContainer.data('view') + '_JS';
		if (typeof window[modalClass] !== "undefined") {
			let instance = new window[modalClass]();
			instance.registerEvents(modalContainer);
			if (app.modalEvents[Window.lastModalId]) {
				app.modalEvents[Window.lastModalId](modalContainer, instance);
			}
		}
		modalClass = 'Base_' + modalContainer.data('view') + '_JS';
		if (typeof window[modalClass] !== "undefined") {
			let instance = new window[modalClass]();
			instance.registerEvents(modalContainer);
			if (app.modalEvents[Window.lastModalId]) {
				app.modalEvents[Window.lastModalId](modalContainer, instance);
			}
		}
	},
	registerModalEvents: function (container, sendByAjaxCb) {
		var form = container.find('form');
		var validationForm = false;
		if (form.hasClass("validateForm")) {
			form.validationEngine(app.validationEngineOptions);
			validationForm = true;
		}
		if (form.hasClass("sendByAjax")) {
			form.on('submit', function (e) {
				var save = true;
				e.preventDefault();
				if (validationForm && form.data('jqv').InvalidFields.length > 0) {
					app.formAlignmentAfterValidation(form);
					save = false;
				}
				if (save) {
					var progressIndicatorElement = $.progressIndicator({
						blockInfo: {'enabled': true}
					});
					var formData = form.serializeFormData();
					AppConnector.request(formData).then(function (responseData) {
						sendByAjaxCb(formData, responseData);
						if (responseData.success && responseData.result) {
							if (responseData.result.notify) {
								Vtiger_Helper_Js.showMessage(responseData.result.notify);
							}
							if (responseData.result.procesStop) {
								progressIndicatorElement.progressIndicator({'mode': 'hide'});
								return false;
							}
						}
						app.hideModalWindow();
						progressIndicatorElement.progressIndicator({'mode': 'hide'});
					});
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
			var block = field.closest('.js-toggle-panel');
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
			$('html').animate({
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
		if (dateFormat.indexOf("-") !== -1) {
			dotMode = '-';
		}
		if (dateFormat.indexOf(".") !== -1) {
			dotMode = '.';
		}
		if (dateFormat.indexOf("/") !== -1) {
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
	/*
	 * Converts user formated date to database format yyyy-mm-dd
	 */
	getDateInDBInsertFormat: function (dateFormat, dateString) {
		var i = 0;
		var dotMode = '-';
		if (dateFormat.indexOf("-") !== -1) {
			dotMode = '-';
		}
		if (dateFormat.indexOf("-") !== -1) {
			dotMode = '.';
		}
		if (dateFormat.indexOf("/") !== -1) {
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
	registerEventForDateFields: function (parentElement) {
		if (typeof parentElement === "undefined") {
			parentElement = $('body');
		}

		parentElement = $(parentElement);

		if (parentElement.hasClass('dateField')) {
			var element = parentElement;
		} else {
			var element = $('.dateField', parentElement);
		}
		element.datepicker({'autoclose': true}).on('changeDate', function (ev) {
			var currentElement = $(ev.currentTarget);
			var dateFormat = currentElement.data('dateFormat').toUpperCase();
			var date = $.datepicker.formatDate(moment(ev.date).format(dateFormat), ev.date);
			currentElement.val(date);
		});
	},
	registerEventForClockPicker: function (object) {
		let elementClockBtn, formatTime;
		if (typeof object === "undefined") {
			elementClockBtn = $('.clockPicker');
			formatTime = CONFIG.hourFormat;
		} else {
			elementClockBtn = object;
			formatTime = elementClockBtn.data('format');
		}
		formatTime = formatTime === 12 ? true : false;
		let params = {
			placement: 'bottom',
			autoclose: true,
			twelvehour: formatTime,
			minutestep: 5,
			ampmSubmit: false
		};
		$('.js-clock__btn').on('click', (e) => {
			let elem = $(e.currentTarget);
			e.stopPropagation();
			let tempElement = elem.closest('.time').find('input.clockPicker');
			if (tempElement.attr('disabled') !== 'disabled') {
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
	 * Function to get the chosen element from the raw select element
	 * @params: select element
	 * @return : chosenElement - corresponding chosen element
	 */
	getChosenElementFromSelect: function (selectElement) {
		var selectId = selectElement.attr('id');
		var chosenEleId = selectId + '_chosen';
		return $('#' + chosenEleId);
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
		return $('#' + select2EleId).closest('.select2-container');
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
		return $('#' + selectEleId);
	},
	/**
	 * Function to set with of the element to parent width
	 * @params : jQuery element for which the action to take place
	 */
	setInheritWidth: function (elements) {
		$(elements).each(function (index, element) {
			var parentWidth = $(element).parent().width();
			$(element).width(parentWidth);
		});
	},
	showNewScrollbar: function (element, options) {
		if (typeof element === "undefined" || !element.length)
			return;
		if (typeof options === "undefined")
			options = {};

		return new PerfectScrollbar(element[0], options);
	},
	showNewBottomTopScrollbar: function (element) {
		if (typeof element === "undefined" || !element.length)
			return;
		var scrollbarTopInit = new PerfectScrollbar(element[0], {
			wheelPropagation: true,
			suppressScrollY: true
		});
		var scrollbarBottomInit = new PerfectScrollbar(element[0], {
			wheelPropagation: true,
			suppressScrollY: true
		});
		var scrollbarTopElement = element.find('.ps__rail-x').first();
		scrollbarTopElement.css({
			top: 0,
			bottom: 'auto'
		});
		scrollbarTopElement.find('.ps__thumb-x').css({
			top: 2,
			bottom: 'auto'
		});
	},
	showNewLeftScrollbar: function (element, options) {
		if (typeof element === "undefined" || !element.length)
			return;
		if (typeof options === "undefined")
			options = {};
		options.wheelPropagation = true;
		var scrollbarLeftInit = new PerfectScrollbar(element[0], options);
		var scrollbarLeftElement = element.children('.ps__rail-y').first();
		scrollbarLeftElement.css({
			left: 0,
			right: 'auto'
		});
		scrollbarLeftElement.find('.ps__thumb-y').css({
			left: 2,
			right: 'auto'
		});
	},
	showScrollBar: function (element, options) {
		if (typeof options === "undefined")
			options = {};
		if (typeof options.height === "undefined")
			options.height = element.css('height');
		return element.slimScroll(options);
	},
	showHorizontalScrollBar: function (element, options) {
		if (typeof options === "undefined")
			options = {};
		var params = {
			horizontalScroll: true,
			theme: "dark-thick",
			advanced: {
				autoExpandHorizontalScroll: true
			}
		}
		if (typeof options !== "undefined")
			var params = $.extend(params, options);
		return element.mCustomScrollbar(params);
	},
	/**
	 * Function returns translated string
	 */
	vtranslate: function (key) {
		if (key in LANG) {
			return LANG[key];
		}
		return key;
	},
	/*
	 * Cache API on client-side
	 */
	cacheNSKey: function (key) { // Namespace in client-storage
		return 'yf.' + key;
	},
	cacheGet: function (key, defvalue) {
		key = this.cacheNSKey(key);
		return $.jStorage.get(key, defvalue);
	},
	cacheSet: function (key, value, ttl) {
		key = this.cacheNSKey(key);
		$.jStorage.set(key, value);
		if (ttl) {
			$.jStorage.setTTL(key, ttl);
		}
	},
	cacheClear: function (key) {
		key = this.cacheNSKey(key);
		return $.jStorage.deleteKey(key);
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
			return $('<div />').text(value).html();
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
		element.css("top", (($(window).height() - element.outerHeight()) / 2) + $(window).scrollTop() + "px");
		element.css("left", (($(window).width() - element.outerWidth()) / 2) + $(window).scrollLeft() + "px");
	},
	getvalidationEngineOptions: function (select2Status) {
		return Object.assign({}, app.validationEngineOptions);
	},
	/**
	 * Function to notify UI page ready after AJAX changes.
	 * This can help in re-registering the event handlers (which was done during ready event).
	 */
	notifyPostAjaxReady: function () {
		$(document).trigger('postajaxready');
	},
	/**
	 * Listen to xready notiications.
	 */
	listenPostAjaxReady: function (callback) {
		$(document).on('postajaxready', callback);
	},
	/**
	 * Form function handlers
	 */
	setFormValues: function (kv) {
		for (var k in kv) {
			$(k).val(kv[k]);
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
		if (typeof window[moduleClassName] === "undefined") {
			moduleClassName = parentModule + "_Vtiger_" + view + "_Js";
		}
		if (typeof window[moduleClassName] === "undefined") {
			moduleClassName = moduleName + "_" + view + "_Js";
		}
		var extendModules = $('#extendModules').val();
		if (typeof window[moduleClassName] === "undefined" && extendModules != undefined) {
			moduleClassName = extendModules + "_" + view + "_Js";
		}
		if (typeof window[moduleClassName] === "undefined") {
			moduleClassName = "Vtiger_" + view + "_Js";
		}
		if (typeof window[moduleClassName] !== "undefined") {
			if (typeof window[moduleClassName] === 'function') {
				return new window[moduleClassName]();
			}
			if (typeof window[moduleClassName] === 'object') {
				return window[moduleClassName];
			}
		}
	},
	/**
	 * Function to decode the encoded htmlentities values
	 */
	getDecodedValue: function (value) {
		return $('<div></div>').html(value).text();
	},
	getCookie: function (c_name) {
		var c_value = document.cookie;
		var c_start = c_value.indexOf(" " + c_name + "=");
		if (c_start === -1) {
			c_start = c_value.indexOf(c_name + "=");
		}
		if (c_start === -1) {
			c_value = null;
		} else {
			c_start = c_value.indexOf("=", c_start) + 1;
			var c_end = c_value.indexOf(";", c_start);
			if (c_end === -1) {
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
		var aDeferred = $.Deferred();
		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'SaveAjax';
		if (mode) {
			params['mode'] = mode;
		}
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
	getMainParams: function (param, json) {
		if (param in CONFIG) {
			return CONFIG[param];
		}
		if (app.cacheParams[param] == undefined) {
			var value = $('#' + param).val();
			app.cacheParams[param] = value;
		}
		var value = app.cacheParams[param];
		if (json) {
			if (value != '') {
				value = JSON.parse(value);
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
		var numberOfDecimal = parseInt(CONFIG.noOfCurrencyDecimals);
		var decimalSeparator = CONFIG.currencyDecimalSeparator;
		var groupSeparator = CONFIG.currencyGroupingSeparator;
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
		var numberOfDecimal = parseInt(CONFIG.noOfCurrencyDecimals);
		var groupSeparator = CONFIG.currencyGroupingSeparator;
		var decimalSeparator = CONFIG.currencyDecimalSeparator;
		if (val == undefined || val == '') {
			val = 0;
		}
		val = val.toString();
		val = val.split(groupSeparator).join("");
		val = val.replace(/\s/g, "").replace(decimalSeparator, ".");
		return parseFloat(val);
	},
	errorLog: function (error, err, errorThrown) {
		if (typeof error === 'object' && error.responseText) {
			error = error.responseText;
		}
		if (typeof error === 'object' && error.statusText) {
			error = error.statusText;
		}
		if (error) {
			console.error(error);
		}
		if (err && err !== 'error') {
			console.error(err);
		}
		if (errorThrown) {
			console.error(errorThrown);
		}
	},
	registerModal: function (container) {
		if (typeof container === "undefined") {
			container = $('body');
		}
		container.off('click', 'button.showModal, a.showModal, .js-show-modal').on('click', 'button.showModal, a.showModal, .js-show-modal', function (e) {
			e.preventDefault();
			var currentElement = $(e.currentTarget);
			var url = currentElement.data('url');

			if (typeof url !== "undefined") {
				if (currentElement.hasClass('js-popover-tooltip')) {
					currentElement.popover('hide');
				}
				if (currentElement.hasClass('disabledOnClick')) {
					currentElement.attr("disabled", true);
				}
				var modalWindowParams = {
					url: url,
					cb: function (container) {
						var call = currentElement.data('cb');
						if (typeof call !== "undefined") {
							if (call.indexOf(".") !== -1) {
								var callerArray = call.split('.');
								if (typeof window[callerArray[0]] === 'object') {
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
				if (currentElement.data('modalid')) {
					modalWindowParams['id'] = currentElement.data('modalid');
				}
				app.showModalWindow(modalWindowParams);
			}
			e.stopPropagation();
		});
	},
	playSound: function (action) {
		var soundsConfig = app.getMainParams('sounds');
		if (soundsConfig['IS_ENABLED']) {
			var audio = new Audio(app.getMainParams('soundFilesPath') + soundsConfig[action]);
			audio.play();
		}
	},
	registerSticky: function () {
		var elements = $('.stick');
		elements.each(function () {
			var currentElement = $(this);
			var position = currentElement.data('position');
			if (position == 'top') {
				var offsetTop = currentElement.offset().top - 50;
				$('.mainBody').on('scroll', function () {
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
				var offsetTop = currentElement.offset().top - $(window).height();
				$('.mainBody').on('scroll', function () {
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
			var btn = $(e.currentTarget);
			var content = btn.closest('.moreContent');
			content.find('.teaserContent').toggleClass('d-none');
			content.find('.fullContent').toggleClass('d-none');
			if (btn.text() == btn.data('on')) {
				btn.text(btn.data('off'));
			} else {
				btn.text(btn.data('on'));
			}
		});
	},
	registerMenu: function () {
		const self = this;
		self.keyboard = {DOWN: 40, ESCAPE: 27, LEFT: 37, RIGHT: 39, SPACE: 32, UP: 38};
		self.sidebarBtn = $('.js-sidebar-btn').first();
		self.sidebar = $('.js-sidebar').first();
		self.sidebarBtn.on('click', self.toggleSidebar.bind(self));
		$('a[href],[tabindex],input,select,textarea,button,object').on('focus', (e) => {
			if (self.sidebarBtn[0] == e.target) return;
			if (self.sidebar.find(':focus').length) {
				self.openSidebar();
			} else if (self.sidebar.hasClass('js-expand')) {
				self.closeSidebar();
			}
		});
		self.sidebar.on('mouseenter', self.openSidebar.bind(self)).on('mouseleave', self.closeSidebar.bind(self));
		self.sidebar.find('.js-menu__content').on('keydown', self.sidebarKeyboard.bind(self));
		self.sidebar.on('keydown', (e) => {
			if (e.which == self.keyboard.ESCAPE) {
				self.closeSidebar();
				if (self.sidebarBtn.is(':tabbable')) self.sidebarBtn.focus();
				else $(':tabbable').eq(parseInt($(':tabbable').index(self.sidebar.find(':tabbable').last())) + 1).focus();
			}
		});
		self.sidebar.find('.js-submenu').on('shown.bs.collapse', (e) => {
			$(e.target).find(':tabbable').first().focus();
		});
		$('.js-submenu-toggler').on('click', (e) => {
			if (!$(e.currentTarget).hasClass('collapsed') && !$(e.target).closest('.toggler').length) {
				window.location = $(e.currentTarget).attr('href');
			}
		});

		this.registerPinEvent();
	},
	openSidebar: function () {
		this.sidebar.addClass('js-expand');
		this.sidebarBtn.attr('aria-expanded', true);
	},
	closeSidebar: function () {
		this.sidebar.removeClass('js-expand');
		this.sidebarBtn.attr('aria-expanded', false);
	},
	toggleSidebar: function () {
		if (this.sidebar.hasClass('js-expand')) {
			this.closeSidebar();
		} else {
			this.openSidebar();
			this.sidebar.find('.js-menu__content :tabbable').first().focus();
		}
	},
	registerPinEvent: function () {
		const self = this;
		let pinButton = self.sidebar.find('.js-menu--pin');
		let baseContainer = self.sidebar.closest('.js-base-container');
		pinButton.on('click', () => {
			let hideMenu = 0;
			baseContainer.removeClass('c-menu--animation');
			if (pinButton.attr('data-show') === '0') {
				hideMenu = 'on';
				pinButton.removeClass('u-opacity-muted');
				baseContainer.addClass('c-menu--open');
				self.sidebar.off('mouseleave mouseenter');
			} else {
				pinButton.addClass('u-opacity-muted');
				baseContainer.removeClass('c-menu--open');
				self.sidebar.on('mouseenter', self.openSidebar.bind(self)).on('mouseleave', self.closeSidebar.bind(self));
				self.closeSidebar.bind(self);
			}
			AppConnector.request({
				module: 'Users',
				action: 'SaveAjax',
				field: 'leftpanelhide',
				record: CONFIG.userId,
				value: hideMenu
			}).then(function (responseData) {
				if (responseData.success && responseData.result) {
					pinButton.attr('data-show', hideMenu);
				}
			});
			setTimeout(() => {baseContainer.addClass('c-menu--animation');}, 300);
		});
	},
	sidebarKeyboard: function (e) {
		let target = $(e.target);
		let toggler = $(e.target).closest('.js-submenu-toggler');
		if (e.which == this.keyboard.LEFT) {
			if (target.hasClass('js-submenu-toggler') && !target.hasClass('collapsed')) {
				target.click();
				return false;
			} else {
				let toggler = $(e.target).closest('.js-submenu').prev('.js-submenu-toggler');
				if (toggler.length && !toggler.hasClass('collapsed')) {
					toggler.click().focus();
					return false;
				}
			}
		} else if ((target.hasClass('js-submenu-toggler') && (e.which == this.keyboard.RIGHT) && target.hasClass('collapsed'))
			|| (target.hasClass('js-submenu-toggler') && e.which == this.keyboard.SPACE)) {
			target.click();
			return false;
		} else if (e.which == this.keyboard.UP) {
			this.sidebar.find('.js-menu__content :tabbable').eq(parseInt(this.sidebar.find('.js-menu__content :tabbable').index(target)) - 1).focus();
			return false;
		} else if (e.which == this.keyboard.DOWN) {
			this.sidebar.find('.js-menu__content :tabbable').eq(parseInt(this.sidebar.find('.js-menu__content :tabbable').index(target)) + 1).focus();
			return false;
		}
	},
	registerTabdrop: function () {
		let tabs = $('.js-tabdrop');
		if (!tabs.length) return;
		let tab = tabs.find('> li');
		tab.each(function () {
			$(this).removeClass('d-none');
		});
		tabs.tabdrop({
			text: app.vtranslate('JS_MORE'),
		});
		//change position to the last element (wcag keyboard navigation)
		let dropdown = tabs.find('> li.dropdown');
		dropdown.appendTo(tabs);
		//fix for toggle button text not changing
		tab.on('click', function (e) {
			setTimeout(function () {
				$(window).trigger('resize');
			}, 500);
		});

	},
	getScreenHeight: function (percantage) {
		if (typeof percantage === "undefined") {
			percantage = 100;
		}
		return $(window).height() * percantage / 100;
	},
	setCalendarHeight() {
		const container = $('.js-base-container');
		const paddingTop = 10;
		if ($(window).width() > 993) {
			let calendarH = $(window).height() - container.find('.o-calendar-container').offset().top - $('.js-footer').height() - paddingTop;
			new ResizeSensor(container.find('.contentsDiv'), () => {
				calendarH = $(window).height() - container.find('.o-calendar-container').offset().top - $('.js-footer').height() - paddingTop;
				$('#calendarview').fullCalendar('option', 'height', calendarH);
				$('#calendarview').height(calendarH + 10); // without this line calendar scroll stop working
			});
			return calendarH;
		} else if ($(window).width() < 993) {
			return 'auto';
		}
	},
	clearBrowsingHistory: function () {
		AppConnector.request({
			module: 'Home',
			action: 'BrowsingHistory',
		}).then(function (response) {
			$('.historyList').html(`<a class="item dropdown-item" href="#" role="listitem">${app.vtranslate('JS_NO_RECORDS')}</a>`);
		});
	},
	showConfirmation: function (data, element) {
		var params = {};
		if (data) {
			params = $.extend(params, data);
		}
		if (element) {
			element = $(element);
			if (!params.title) {
				params.title = element.html() + ' ' + (element.data('content') ? element.data('content') : '');
			}
			if (!params.message) {
				params.message = element.data('confirm');
			}
			if (!params.url) {
				params.url = element.data('url');
			}
		}
		Vtiger_Helper_Js.showConfirmationBox(params).then(function () {
			if (params.type == 'href') {
				AppConnector.request(params.url).then(function (data) {
					window.location.href = data.result;
				});
			} else if (params.type == 'reloadTab') {
				AppConnector.request(params.url).then(function (data) {
					Vtiger_Detail_Js.getInstance().reloadTabContent();
				});
			}
		});
	},
	formatToHourText: function (decTime, type = 'short', withSeconds = false, withMinutes = true) {
		const short = type === 'short';
		const hour = Math.floor(decTime);
		const min = Math.floor((decTime - hour) * 60);
		const sec = Math.round(((decTime - hour) * 60 - min) * 60);
		let result = '';
		if (hour) {
			result += short ? hour + app.vtranslate('JS_H') : `${hour} ` + app.vtranslate('JS_H_LONG');
		}
		if ((hour || min) && withMinutes) {
			result += short ? ` ${min}` + app.vtranslate('JS_M') : ` ${min} ` + app.vtranslate('JS_M_LONG');
		}
		if (withSeconds !== false) {
			result += short ? ` ${sec}` + app.vtranslate('JS_S') : ` ${sec} ` + app.vtranslate('JS_S_LONG');
		}
		if (!hour && !min && withSeconds === false && withMinutes) {
			result += short ? '0' + app.vtranslate('JS_M') : '0 ' + app.vtranslate('JS_M_LONG');
		}
		if (!hour && !min && withSeconds === false && !withMinutes) {
			result += short ? '0' + app.vtranslate('JS_H') : '0 ' + app.vtranslate('JS_H_LONG');
		}
		return result.trim();
	},
	showRecordsList: function (params, cb, afterShowModal) {
		if (!params.view) {
			params.view = "RecordsList";
		}
		AppConnector.request(params).then(function (requestData) {
			app.showModalWindow(requestData, function (data) {
				if (typeof afterShowModal === 'function') {
					afterShowModal(data);
				}
				if (typeof cb === 'function') {
					app.modalEvents[Window.lastModalId] = cb;
				}
			});
		});
	},
};
$(document).ready(function () {
	App.Fields.Picklist.changeSelectElementView();
	app.showPopoverElementView($('body').find('.js-popover-tooltip'));
	app.registerSticky();
	app.registerMoreContent($('body').find('button.moreBtn'));
	app.registerModal();
	app.registerMenu();
	app.registerTabdrop();
	String.prototype.toCamelCase = function () {
		var value = this.valueOf();
		return value.charAt(0).toUpperCase() + value.slice(1).toLowerCase()
	}
	// in IE resize option for textarea is not there, so we have to use .resizable() api
	if (/MSIE/.test(navigator.userAgent) || (/Trident/).test(navigator.userAgent)) {
		$('textarea').resizable();
	}
	// Instantiate Page Controller
	var pageController = app.getPageController();
	if (pageController) {
		window.pageController = pageController;
		window.pageController.registerEvents();
	}
});
(function ($) {
	$.fn.getNumberFromValue = function () {
		return app.parseNumberToFloat($(this).val());
	}
	$.fn.getNumberFromText = function () {
		return app.parseNumberToFloat($(this).text());
	}
	$.fn.disable = function () {
		this.attr('disabled', 'disabled');
	}
	$.fn.enable = function () {
		this.removeAttr('disabled');
	}
	$.fn.serializeFormData = function () {
		var form = $(this);
		for (var instance in CKEDITOR.instances) {
			CKEDITOR.instances[instance].updateElement();
		}
		var values = form.serializeArray();
		var data = {};
		if (values) {
			$(values).each(function (k, v) {
				if (v.name in data && (typeof data[v.name] !== 'object')) {
					var element = form.find('[name="' + v.name + '"]');
					//Only for muti select element we need to send array of values
					if (element.is('select') && element.attr('multiple') != undefined) {
						var prevValue = data[v.name];
						data[v.name] = [];
						data[v.name].push(prevValue)
					}
				}
				if (typeof data[v.name] === 'object') {
					data[v.name].push(v.value);
				} else {
					data[v.name] = v.value;
				}
			});
		}
		// If data-type="autocomplete", pickup data-value="..." set
		var autocompletes = $('[data-type="autocomplete"]', $(this));
		$(autocompletes).each(function (i) {
			var ac = $(autocompletes[i]);
			data[ac.attr('name')] = ac.data('value');
		});
		return data;
	}
	// Case-insensitive :icontains expression
	$.expr[':'].icontains = function (obj, index, meta, stack) {
		return (obj.textContent || obj.innerText || $(obj).text() || '').toLowerCase().indexOf(meta[3].toLowerCase()) !== -1;
	}
	$.fn.removeTextNode = function () {
		$(this).contents().filter(function () {
			return this.nodeType == 3; //Node.TEXT_NODE
		}).remove();
	}
	bootbox.setLocale(CONFIG.langKey);
})($);
