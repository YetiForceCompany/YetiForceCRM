/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

App.Fields = {
	'Date': {
		months: ["JS_JAN", "JS_FEB", "JS_MAR", "JS_APR", "JS_MAY", "JS_JUN", "JS_JUL", "JS_AUG", "JS_SEP", "JS_OCT", "JS_NOV", "JS_DEC"],
		monthsTranslated: ["JS_JAN", "JS_FEB", "JS_MAR", "JS_APR", "JS_MAY", "JS_JUN", "JS_JUL", "JS_AUG", "JS_SEP", "JS_OCT", "JS_NOV", "JS_DEC"].map((monthName) => app.vtranslate(monthName)),
		fullMonths: ["JS_JANUARY", "JS_FEBRUARY", "JS_MARCH", "JS_APRIL", "JS_MAY", "JS_JUNE", "JS_JULY", "JS_AUGUST", "JS_SEPTEMBER", "JS_OCTOBER", "JS_NOVEMBER", "JS_DECEMBER"],
		fullMonthsTranslated: ["JS_JANUARY", "JS_FEBRUARY", "JS_MARCH", "JS_APRIL", "JS_MAY", "JS_JUNE", "JS_JULY", "JS_AUGUST", "JS_SEPTEMBER", "JS_OCTOBER", "JS_NOVEMBER", "JS_DECEMBER"].map((monthName) => app.vtranslate(monthName)),
		days: ["JS_SUN", "JS_MON", "JS_TUE", "JS_WED", "JS_THU", "JS_FRI", "JS_SAT"],
		daysTranslated: ["JS_SUN", "JS_MON", "JS_TUE", "JS_WED", "JS_THU", "JS_FRI", "JS_SAT"].map((monthName) => app.vtranslate(monthName)),
		fullDays: ["JS_SUNDAY", "JS_MONDAY", "JS_TUESDAY", "JS_WEDNESDAY", "JS_THURSDAY", "JS_FRIDAY", "JS_SATURDAY"],
		fullDaysTranslated: ["JS_SUNDAY", "JS_MONDAY", "JS_TUESDAY", "JS_WEDNESDAY", "JS_THURSDAY", "JS_FRIDAY", "JS_SATURDAY"].map((monthName) => app.vtranslate(monthName)),

		/**
		 * Register DatePicker
		 * @param {$} parentElement
		 * @param {boolean} registerForAddon
		 * @param {object} customParams
		 */
		register(parentElement, registerForAddon, customParams) {
			if (typeof parentElement === "undefined") {
				parentElement = $('body');
			} else {
				parentElement = $(parentElement);
			}
			if (typeof registerForAddon === "undefined") {
				registerForAddon = true;
			}
			let elements = $('.dateField', parentElement);
			if (parentElement.hasClass('dateField')) {
				elements = parentElement;
			}
			if (elements.length === 0) {
				return;
			}
			if (registerForAddon === true) {
				const parentDateElem = elements.closest('.date');
				$('.js-date__btn', parentDateElem).on('click', function inputGroupAddonClickHandler(e) {
					// Using focus api of DOM instead of jQuery because show api of datePicker is calling e.preventDefault
					// which is stopping from getting focus to input element
					$(e.currentTarget).closest('.date').find('input.dateField').get(0).focus();
				});
			}
			let format = CONFIG.dateFormat;
			const elementDateFormat = elements.data('dateFormat');
			if (typeof elementDateFormat !== "undefined") {
				format = elementDateFormat;
			}
			if (typeof $.fn.datepicker.dates[CONFIG.langKey] === "undefined") {
				$.fn.datepicker.dates[CONFIG.langKey] = {
					days: App.Fields.Date.fullDaysTranslated,
					daysShort: App.Fields.Date.daysTranslated,
					daysMin: App.Fields.Date.daysTranslated,
					months: App.Fields.Date.fullMonthsTranslated,
					monthsShort: App.Fields.Date.monthsTranslated,
					today: app.vtranslate('JS_TODAY'),
					clear: app.vtranslate('JS_CLEAR'),
					format,
					titleFormat: 'MM yyyy', /* Leverages same syntax as 'format' */
					weekStart: CONFIG.firstDayOfWeekNo
				};
			}
			let params = {
				todayBtn: "linked",
				clearBtn: true,
				language: CONFIG.langKey,
				starts: CONFIG.firstDayOfWeekNo,
				autoclose: true,
				todayHighlight: true,
			};
			if (typeof customParams !== "undefined") {
				params = $.extend(params, customParams);
			}
			elements.each((index, element) => {
				$(element).datepicker($.extend(true, params, $(element).data('params')));
			});
		},

		/**
		 * Register dateRangePicker
		 * @param {jQuery} parentElement
		 * @param {object} customParams
		 */
		registerRange(parentElement, customParams = {}) {
			if (typeof parentElement === "undefined") {
				parentElement = $('body');
			} else {
				parentElement = $(parentElement);
			}
			let elements = $('.dateRangeField', parentElement);
			if (parentElement.hasClass('dateRangeField')) {
				elements = parentElement;
			}
			if (elements.length === 0) {
				return;
			}
			let format = CONFIG.dateFormat.toUpperCase();
			const elementDateFormat = elements.data('dateFormat');
			if (typeof elementDateFormat !== "undefined") {
				format = elementDateFormat.toUpperCase();
			}
			let ranges = {};
			ranges[app.vtranslate('JS_TODAY')] = [moment(), moment()];
			ranges[app.vtranslate('JS_YESTERDAY')] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
			ranges[app.vtranslate('JS_LAST_7_DAYS')] = [moment().subtract(6, 'days'), moment()];
			ranges[app.vtranslate('JS_CURRENT_MONTH')] = [moment().startOf('month'), moment().endOf('month')];
			ranges[app.vtranslate('JS_LAST_MONTH')] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];
			ranges[app.vtranslate('JS_LAST_3_MONTHS')] = [moment().subtract(3, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];
			ranges[app.vtranslate('JS_LAST_6_MONTHS')] = [moment().subtract(6, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];
			let params = {
				autoUpdateInput: false,
				autoApply: true,
				ranges: ranges,
				opens: "center",
				locale: {
					format: format,
					separator: ",",
					applyLabel: app.vtranslate('JS_APPLY'),
					cancelLabel: app.vtranslate('JS_CANCEL'),
					fromLabel: app.vtranslate('JS_FROM'),
					toLabel: app.vtranslate('JS_TO'),
					customRangeLabel: app.vtranslate('JS_CUSTOM'),
					weekLabel: app.vtranslate('JS_WEEK').substr(0, 1),
					firstDay: CONFIG.firstDayOfWeekNo,
					daysOfWeek: App.Fields.Date.daysTranslated,
					monthNames: App.Fields.Date.fullMonthsTranslated,
				},
			};

			if (typeof customParams !== "undefined") {
				params = $.extend(params, customParams);
			}
			$('.js-date__btn').off().on('click', (e) => {
				$(e.currentTarget).parent().next('.dateRangeField')[0].focus();
			});
			elements.each((index, element) => {
				let currentParams = $.extend(true, params, $(element).data('params'));
				$(element).daterangepicker(currentParams).on('apply.daterangepicker', function (ev, picker) {
					$(this).val(picker.startDate.format(currentParams.locale.format) + ',' + picker.endDate.format(currentParams.locale.format));
					$(this).trigger('change');
				});
			});
		},
	},
	DateTime: {
		/*
		 * Initialization datetime fields
		 * @param {jQuery} parentElement
		 * @param {object} customParams
		 */
		register: function (parentElement, customParams) {
			if (typeof parentElement === "undefined") {
				parentElement = $('body');
			} else {
				parentElement = $(parentElement);
			}
			let elements = $('.dateTimePickerField', parentElement);
			if (parentElement.hasClass('dateTimePickerField')) {
				elements = parentElement;
			}
			if (elements.length === 0) {
				return;
			}
			$('.input-group-text', elements.closest('.dateTime')).on('click', function (e) {
				$(e.currentTarget).closest('.dateTime').find('input.dateTimePickerField ').get(0).focus();
			});
			let dateFormat = CONFIG.dateFormat.toUpperCase();
			const elementDateFormat = elements.data('dateFormat');
			if (typeof elementDateFormat !== "undefined") {
				dateFormat = elementDateFormat.toUpperCase();
			}
			let hourFormat = CONFIG.hourFormat;
			const elementHourFormat = elements.data('hourFormat');
			if (typeof elementHourFormat !== "undefined") {
				hourFormat = elementHourFormat;
			}
			let timePicker24Hour = true;
			let timeFormat = 'hh:mm';
			if (hourFormat !== 24) {
				timePicker24Hour = false;
				timeFormat = 'hh:mm A';
			}
			const format = dateFormat + ' ' + timeFormat;
			let params = {
				parentEl: parentElement,
				singleDatePicker: true,
				showDropdowns: true,
				timePicker: true,
				timePicker24Hour: timePicker24Hour,
				timePickerIncrement: 1,
				autoUpdateInput: true,
				autoApply: true,
				opens: "left",
				locale: {
					format: format,
					separator: ",",
					applyLabel: app.vtranslate('JS_APPLY'),
					cancelLabel: app.vtranslate('JS_CANCEL'),
					fromLabel: app.vtranslate('JS_FROM'),
					toLabel: app.vtranslate('JS_TO'),
					customRangeLabel: app.vtranslate('JS_CUSTOM'),
					weekLabel: app.vtranslate('JS_WEEK').substr(0, 1),
					firstDay: CONFIG.firstDayOfWeekNo,
					daysOfWeek: App.Fields.Date.daysTranslated,
					monthNames: App.Fields.Date.fullMonthsTranslated,
				},
			};
			if (typeof customParams !== "undefined") {
				params = $.extend(params, customParams);
			}
			elements.daterangepicker(params).on('apply.daterangepicker', function applyDateRangePickerHandler(ev, picker) {
				$(this).val(picker.startDate.format(format));
			});
		},
	},
	Colors: {
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
		getRandomColor: function () {
			var letters = '0123456789ABCDEF'.split('');
			var color = '#';
			for (var i = 0; i < 6; i++) {
				color += letters[Math.floor(Math.random() * 16)];
			}
			return color;
		},
		getRandomColors: function (count) {
			const colors = [];
			for (var i = 0; i < count; i++) {
				colors.push(this.getRandomColor());
			}
			return colors;
		},
	},
	Text: {
		/**
		 * Register clip
		 * @param {HTMLElement|jQuery} container
		 * @param {string} key
		 * @returns {ClipboardJS|undefined}
		 */
		registerCopyClipboard: function (container, key = '.clipboard') {
			if (typeof container !== 'object') {
				return;
			}
			container = $(container).get(0);
			let elements = container.querySelectorAll(key);
			if (elements.length === 0) {
				elements = key;
				container = '';
			}
			return new ClipboardJS(elements, {
				container: container,
				text: function (trigger) {
					Vtiger_Helper_Js.showPnotify({
						text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
						type: 'success'
					});
					trigger = $(trigger);
					const element = $(trigger.data('copyTarget'), container);
					let val;
					if (typeof trigger.data('copyType') !== "undefined") {
						if (element.is("select")) {
							val = element.find('option:selected').data(trigger.data('copyType'));
						} else {
							val = element.data(trigger.data('copyType'));
						}
					} else if (typeof trigger.data('copy-attribute') !== "undefined") {
						val = trigger.data(trigger.data('copy-attribute'));
					} else {
						val = element.val();
					}
					return val;
				}
			});
		},
		Editor: class {
			constructor(parentElement, params) {
				let elements;
				if (typeof parentElement === "undefined") {
					parentElement = $('body');
				} else {
					parentElement = $(parentElement);
				}
				if (parentElement.hasClass('js-editor') && !parentElement.prop('disabled')) {
					elements = parentElement;
				} else {
					elements = $('.js-editor:not([disabled])', parentElement);
				}
				if (elements.length !== 0 || typeof elements !== "undefined") {
					this.loadEditor(elements, params);
				}
			}

			/*
			 *Function to set the textArea element
			 */
			setElement(element) {
				this.element = $(element);
				return this;
			}

			/*
			 *Function to get the textArea element
			 */
			getElement() {
				return this.element;
			}

			/*
			 * Function to return Element's id atrribute value
			 */
			getElementId() {
				return this.getElement().attr('id');
			}

			/*
			 * Function to get the instance of ckeditor
			 */
			getEditorInstanceFromName() {
				return CKEDITOR.instances[this.getElementId()];
			}

			/*
			 * Function to load CkEditor
			 * @param {HTMLElement|jQuery} element on which CkEditor has to be loaded
			 * @param {Object} customConfig custom configurations for ckeditor
			 */
			loadEditor(element, customConfig) {
				this.setElement(element);
				const instance = this.getEditorInstanceFromName();
				let config = {
					allowedContent: true,
					removeButtons: '',
					scayt_autoStartup: false,
					enterMode: CKEDITOR.ENTER_BR,
					shiftEnterMode: CKEDITOR.ENTER_P,
					on: {
						instanceReady: function (evt) {
							evt.editor.on('blur', function () {
								evt.editor.updateElement();
							});
						}
					},
					extraPlugins: 'colorbutton,colordialog,find,selectall,showblocks,div,print,font,justify,bidi',
					toolbar: 'Full',
					toolbar_Full: [
						{
							name: 'clipboard',
							items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
						},
						{name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt']},
						{name: 'links', items: ['Link', 'Unlink']},
						{name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak']},
						{name: 'tools', items: ['Maximize', 'ShowBlocks']},
						{name: 'paragraph', items: ['Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv']},
						{name: 'document', items: ['Source', 'Print']},
						'/',
						{name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize']},
						{
							name: 'basicstyles',
							items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript']
						},
						{name: 'colors', items: ['TextColor', 'BGColor']},
						{
							name: 'paragraph',
							items: ['NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl']
						},
						{name: 'basicstyles', items: ['CopyFormatting', 'RemoveFormat']},
					],
					toolbar_Min: [
						{
							name: 'basicstyles',
							items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript']
						},
						{name: 'colors', items: ['TextColor', 'BGColor']},
						{name: 'tools', items: ['Maximize']},
						{
							name: 'paragraph',
							items: ['NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl']
						},
						{name: 'basicstyles', items: ['CopyFormatting', 'RemoveFormat']},
					]
				};
				if (typeof customConfig !== "undefined") {
					config = $.extend(config, customConfig);
				}
				if (instance) {
					CKEDITOR.remove(instance);
				}
				element.ckeditor(config);
			}
		},
		/**
		 * Destroy ckEditor
		 * @param {jQuery} element
		 */
		destroyEditor(element) {
			if (typeof CKEDITOR !== "undefined" && CKEDITOR.instances && element.attr('id') in CKEDITOR.instances) {
				CKEDITOR.instances[element.attr('id')].destroy();
			}
		},
		/**
		 * Generate random character
		 * @returns {string}
		 */
		generateRandomChar() {
			const chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZ";
			const rand = Math.floor(Math.random() * chars.length);
			return chars.substring(rand, rand + 1);
		},
		/**
		 * generate random hash
		 * @returns {string}
		 */
		generateRandomHash(prefix = '') {
			prefix = prefix.toString();
			const hash = Math.random().toString(36).substr(2, 9) + '-' + Math.random().toString(36).substr(2, 9) + '-' + new Date().valueOf();
			return prefix ? prefix + '-' + hash : hash;
		}
	},
	Picklist: {
		/**
		 * Function which will convert ui of select boxes.
		 * @params parent - select element
		 * @params view - select2
		 * @params viewParams - select2 params
		 * @returns jquery object list which represents changed select elements
		 */
		changeSelectElementView: function (parent, view, viewParams) {
			if (typeof parent === "undefined") {
				parent = $('body');
			}
			if (typeof view === "undefined") {
				const select2Elements = $('select.select2', parent).toArray();
				const selectizeElements = $('select.selectize', parent).toArray();
				const choosenElements = $('.chzn-select', parent).toArray();
				select2Elements.forEach((elem) => {
					this.changeSelectElementView($(elem), 'select2', viewParams);
				});
				selectizeElements.forEach((elem) => {
					this.changeSelectElementView($(elem), 'selectize', viewParams);
				});
				choosenElements.forEach((elem) => {
					this.changeSelectElementView($(elem), 'choosen', viewParams);
				});
				return;
			}
			//If view is select2, This will convert the ui of select boxes to select2 elements.
			if (typeof view === 'string') {
				switch (view) {
					case 'select2':
						return App.Fields.Picklist.showSelect2ElementView(parent, viewParams);
					case 'selectize':
						return App.Fields.Picklist.showSelectizeElementView(parent, viewParams);
					case 'choosen':
						return App.Fields.Picklist.showChoosenElementView(parent, viewParams);
				}
				app.errorLog(new Error(`Unknown select type [${view}]`));
			}
		},
		/**
		 * Function which will show the select2 element for select boxes . This will use select2 library
		 */
		showSelect2ElementView: function (selectElement, params) {
			selectElement = $(selectElement);
			if (typeof params === "undefined") {
				params = {};
			}
			if ($(selectElement).length > 1) {
				return $(selectElement).each((index, element) => {
					this.showSelect2ElementView($(element).eq(0), params);
				});
			}
			if (typeof params.dropdownParent === 'undefined') {
				const modalParent = $(selectElement).closest('.modal-body');
				if (modalParent.length) {
					params.dropdownParent = modalParent;
				}
			}
			let data = selectElement.data();
			if (data != null) {
				params = $.extend(data, params);
			}
			params.language = {};
			params.theme = "bootstrap";
			const width = $(selectElement).data('width');
			if (typeof width !== "undefined") {
				params.width = width;
			} else {
				params.width = '100%';
			}
			params.containerCssClass = 'form-control w-100';
			const containerCssClass = selectElement.data('containerCssClass');
			if (typeof containerCssClass !== "undefined") {
				params.containerCssClass += " " + containerCssClass;
			}
			params.language.noResults = function (msn) {
				return app.vtranslate('JS_NO_RESULTS_FOUND');
			};

			// Sort DOM nodes alphabetically in select box.
			if (typeof params['customSortOptGroup'] !== "undefined" && params['customSortOptGroup']) {
				$('optgroup', selectElement).each(function () {
					var optgroup = $(this);
					var options = optgroup.children().toArray().sort(function (a, b) {
						var aText = $(a).text();
						var bText = $(b).text();
						return aText < bText ? 1 : -1;
					});
					$.each(options, function (i, v) {
						optgroup.prepend(v);
					});
				});
				delete params['customSortOptGroup'];
			}

			//formatSelectionTooBig param is not defined even it has the maximumSelectionLength,
			//then we should send our custom function for formatSelectionTooBig
			if (typeof params.maximumSelectionLength !== "undefined" && typeof params.formatSelectionTooBig === "undefined") {
				var limit = params.maximumSelectionLength;
				//custom function which will return the maximum selection size exceeds message.
				var formatSelectionExceeds = function (limit) {
					return app.vtranslate('JS_YOU_CAN_SELECT_ONLY') + ' ' + limit.maximum + ' ' + app.vtranslate('JS_ITEMS');
				}
				params.language.maximumSelected = formatSelectionExceeds;
			}
			if (typeof selectElement.attr('multiple') !== "undefined" && !params.placeholder) {
				params.placeholder = app.vtranslate('JS_SELECT_SOME_OPTIONS');
			} else if (!params.placeholder) {
				params.placeholder = app.vtranslate('JS_SELECT_AN_OPTION');
			}
			if (typeof params.templateResult === "undefined") {
				params.templateResult = function (data, container) {
					if (data.element && data.element.className) {
						$(container).addClass(data.element.className);
					}
					if (typeof data.name === "undefined") {
						return data.text;
					}
					if (data.type == 'optgroup') {
						return '<strong>' + data.name + '</strong>';
					} else {
						return '<span>' + data.name + '</span>';
					}
				};
			}
			if (typeof params.templateSelection === "undefined") {
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
					if (markup !== "undefined")
						return markup;
				};
				var minimumInputLength = 3;
				if (selectElement.data('minimumInput') !== "undefined") {
					minimumInputLength = selectElement.data('minimumInput');
				}
				params.minimumInputLength = minimumInputLength;
				params.templateResult = function (data) {
					if (typeof data.name === "undefined") {
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
					var selectNew = select.clone().addClass('d-none');
					select.parent().append(selectNew);
					select.prop('disabled', true);
				}
				if (select.hasClass('tags')) {
					params.tags = true;
				}
				select.select2(params)
					.on("select2:open", function (e) {
						if (select.data('unselecting')) {
							select.removeData('unselecting');
							setTimeout(function (e) {
								select.each(function () {
									$(this).select2('close');
								});
							}, 1);
						}
						var element = $(e.currentTarget);
						var instance = element.data('select2');
						instance.$dropdown.css('z-index', 1000002);
					}).on("select2:unselect", function (e) {
					select.data('unselecting', true);
				});
			})
			return selectElement;
		},
		/**
		 * Replace select with choosen
		 * @param {jQuery} parent
		 * @param {object} viewParams
		 */
		showChoosenElementView(parent, viewParams) {
			let selectElement = $('.chzn-select', parent);
			//parent itself is the element
			if (parent.is('select.chzn-select')) {
				selectElement = parent;
			}
			// generate random ID
			selectElement.each(function () {
				if ($(this).prop("id").length === 0) {
					$(this).attr('id', "sel" + App.Fields.Text.generateRandomChar() + App.Fields.Text.generateRandomChar() + App.Fields.Text.generateRandomChar());
				}
			});
			//fix for multiselect error prompt hide when validation is success
			selectElement.filter('[multiple]').filter('[data-validation-engine*="validate"]').on('change', function (e) {
				$(e.currentTarget).trigger('focusout');
			});
			let params = {
				no_results_text: app.vtranslate('JS_NO_RESULTS_FOUND') + ':'
			};
			const moduleName = app.getModuleName();
			if (selectElement.filter('[multiple]') && moduleName !== 'Install') {
				params.placeholder_text_multiple = ' ' + app.vtranslate('JS_SELECT_SOME_OPTIONS');
			}
			if (moduleName !== 'Install') {
				params.placeholder_text_single = ' ' + app.vtranslate('JS_SELECT_AN_OPTION');
			}
			selectElement.chosen(params);
			selectElement.each(function () {
				const select = $(this);
				// hide selected items in the chosen instance when item is hidden.
				if (select.hasClass('hideSelected')) {
					const ns = [];
					select.find('optgroup,option').each(function (n, e) {
						if ($(this).hasClass('d-none')) {
							ns.push(n);
						}
					});
					if (ns.length) {
						select.next().find('.search-choice-close').each(function (n, e) {
							if ($.inArray($(this).data('option-array-index'), ns) !== -1) {
								$(this).closest('li').remove();
							}
						})
					}
				}
				if (select.attr('readonly') === 'readonly') {
					select.on('chosen:updated', function () {
						if (select.attr('readonly')) {
							let selectData = select.data('chosen');
							select.attr('disabled', 'disabled');
							if (typeof selectData === 'object') {
								selectData.search_field_disabled();
							}
							if (select.is(':disabled')) {
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
			return $('.chosen-container-multi .default, .chosen-container').css('width', '100%');
		},
		/**
		 * Function to destroy the chosen element and get back the basic select Element
		 */
		destroyChosenElement: function (parent) {
			if (typeof parent === "undefined") {
				parent = $('body');
			}
			let selectElement = $('.chzn-select', parent);
			//parent itself is the element
			if (parent.is('select.chzn-select')) {
				selectElement = parent;
			}
			return selectElement.css('display', 'block').removeClass("chzn-done").data("chosen", null).next().remove();
		},

		/**
		 * Function which will show the selectize element for select boxes . This will use selectize library
		 */
		showSelectizeElementView: function (selectElement, params) {
			if (typeof params === "undefined") {
				params = {plugins: ['remove_button']};
			}
			selectElement.selectize(params);
			return selectElement;
		},
		/**
		 * Function to destroy the selectize element
		 */
		destroySelectizeElement: function (parent) {
			if (typeof parent === "undefined") {
				parent = $('body');
			}
			let selectElements = $('.selectized', parent);
			//parent itself is the element
			if (parent.is('select.selectized')) {
				selectElements = parent;
			}
			selectElements.each(function () {
				$(this)[0].selectize.destroy();
			});
		},
	},
	MultiImage: {
		currentFileUploads: 0,

		register(container) {
			$('.js-multi-image', container).toArray().forEach((fileUploadInput) => {
				new MultiImage(fileUploadInput);
			});
		}
	},
};
