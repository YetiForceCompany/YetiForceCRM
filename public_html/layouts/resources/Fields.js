/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

window.App.Fields = {
	Date: {
		months: [
			'JS_JAN',
			'JS_FEB',
			'JS_MAR',
			'JS_APR',
			'JS_MAY_SHORT',
			'JS_JUN',
			'JS_JUL',
			'JS_AUG',
			'JS_SEP',
			'JS_OCT',
			'JS_NOV',
			'JS_DEC'
		],
		monthsTranslated: [
			'JS_JAN',
			'JS_FEB',
			'JS_MAR',
			'JS_APR',
			'JS_MAY_SHORT',
			'JS_JUN',
			'JS_JUL',
			'JS_AUG',
			'JS_SEP',
			'JS_OCT',
			'JS_NOV',
			'JS_DEC'
		].map((monthName) => app.vtranslate(monthName)),
		fullMonths: [
			'JS_JANUARY',
			'JS_FEBRUARY',
			'JS_MARCH',
			'JS_APRIL',
			'JS_MAY',
			'JS_JUNE',
			'JS_JULY',
			'JS_AUGUST',
			'JS_SEPTEMBER',
			'JS_OCTOBER',
			'JS_NOVEMBER',
			'JS_DECEMBER'
		],
		fullMonthsTranslated: [
			'JS_JANUARY',
			'JS_FEBRUARY',
			'JS_MARCH',
			'JS_APRIL',
			'JS_MAY',
			'JS_JUNE',
			'JS_JULY',
			'JS_AUGUST',
			'JS_SEPTEMBER',
			'JS_OCTOBER',
			'JS_NOVEMBER',
			'JS_DECEMBER'
		].map((monthName) => app.vtranslate(monthName)),
		days: ['JS_SUN', 'JS_MON', 'JS_TUE', 'JS_WED', 'JS_THU', 'JS_FRI', 'JS_SAT'],
		daysTranslated: ['JS_SUN', 'JS_MON', 'JS_TUE', 'JS_WED', 'JS_THU', 'JS_FRI', 'JS_SAT'].map((monthName) =>
			app.vtranslate(monthName)
		),
		fullDays: ['JS_SUNDAY', 'JS_MONDAY', 'JS_TUESDAY', 'JS_WEDNESDAY', 'JS_THURSDAY', 'JS_FRIDAY', 'JS_SATURDAY'],
		fullDaysTranslated: [
			'JS_SUNDAY',
			'JS_MONDAY',
			'JS_TUESDAY',
			'JS_WEDNESDAY',
			'JS_THURSDAY',
			'JS_FRIDAY',
			'JS_SATURDAY'
		].map((monthName) => app.vtranslate(monthName)),

		/**
		 * Register DatePicker
		 * @param {$} parentElement
		 * @param {boolean} registerForAddon
		 * @param {object} customParams
		 */
		register(parentElement, registerForAddon, customParams, className = 'dateField') {
			if (typeof parentElement === 'undefined') {
				parentElement = $('body');
			} else {
				parentElement = $(parentElement);
			}
			if (typeof registerForAddon === 'undefined') {
				registerForAddon = true;
			}
			let elements = $('.' + className, parentElement);
			if (parentElement.hasClass(className)) {
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
					$(e.currentTarget)
						.closest('.date')
						.find('input.' + className)
						.get(0)
						.focus();
				});
			}
			let format = CONFIG.dateFormat;
			const elementDateFormat = elements.data('dateFormat');
			if (typeof elementDateFormat !== 'undefined') {
				format = elementDateFormat;
			}
			if (typeof $.fn.datepicker.dates[CONFIG.langKey] === 'undefined') {
				$.fn.datepicker.dates[CONFIG.langKey] = {
					days: App.Fields.Date.fullDaysTranslated,
					daysShort: App.Fields.Date.daysTranslated,
					daysMin: App.Fields.Date.daysTranslated,
					months: App.Fields.Date.fullMonthsTranslated,
					monthsShort: App.Fields.Date.monthsTranslated,
					today: app.vtranslate('JS_TODAY'),
					clear: app.vtranslate('JS_CLEAR'),
					format: format,
					titleFormat: 'MM yyyy' /* Leverages same syntax as 'format' */,
					weekStart: CONFIG.firstDayOfWeekNo
				};
			}
			let params = {
				todayBtn: 'linked',
				clearBtn: true,
				language: CONFIG.langKey,
				weekStart: CONFIG.firstDayOfWeekNo,
				autoclose: true,
				todayHighlight: true,
				format: format
			};
			if (typeof customParams !== 'undefined') {
				params = $.extend(params, customParams);
			}
			elements.each((_index, element) => {
				$(element).datepicker(
					$.extend(
						true,
						Object.assign(params, { enableOnReadonly: !element.hasAttribute('readonly') }),
						$(element).data('params')
					)
				);
			});
			App.Fields.Utils.hideMobileKeyboard(elements);
			return elements;
		},

		/**
		 * Register dateRangePicker
		 * @param {jQuery} parentElement
		 * @param {object} customParams
		 */
		registerRange(parentElement, customParams = {}) {
			if (typeof parentElement === 'undefined') {
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
			if (typeof elementDateFormat !== 'undefined') {
				format = elementDateFormat.toUpperCase();
			}
			let ranges = {};
			ranges[app.vtranslate('JS_TODAY')] = [moment(), moment()];
			ranges[app.vtranslate('JS_TOMORROW')] = [moment().add(1, 'days'), moment().add(1, 'days')];
			ranges[app.vtranslate('JS_YESTERDAY')] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
			ranges[app.vtranslate('JS_LAST_7_DAYS')] = [moment().subtract(6, 'days'), moment()];
			ranges[app.vtranslate('JS_NEXT_7_DAYS')] = [moment(), moment().add(6, 'days')];
			ranges[app.vtranslate('JS_CURRENT_MONTH')] = [moment().startOf('month'), moment().endOf('month')];
			ranges[app.vtranslate('JS_NEXT_MONTH')] = [
				moment().add(1, 'month').startOf('month'),
				moment().add(1, 'month').endOf('month')
			];
			ranges[app.vtranslate('JS_LAST_MONTH')] = [
				moment().subtract(1, 'month').startOf('month'),
				moment().subtract(1, 'month').endOf('month')
			];
			ranges[app.vtranslate('JS_NEXT_MONTH')] = [
				moment().add(1, 'month').startOf('month'),
				moment().add(1, 'month').endOf('month')
			];
			ranges[app.vtranslate('JS_LAST_3_MONTHS')] = [
				moment().subtract(3, 'month').startOf('month'),
				moment().subtract(1, 'month').endOf('month')
			];
			ranges[app.vtranslate('JS_NEXT_3_MONTHS')] = [moment().startOf('month'), moment().add(3, 'month').endOf('month')];
			ranges[app.vtranslate('JS_LAST_6_MONTHS')] = [
				moment().subtract(6, 'month').startOf('month'),
				moment().subtract(1, 'month').endOf('month')
			];
			ranges[app.vtranslate('JS_NEXT_6_MONTHS')] = [moment().startOf('month'), moment().add(6, 'month').endOf('month')];

			let locale = App.Fields.DateTime.getDefaultLocale();
			locale.format = format;
			let params = {
				language: CONFIG.langKey,
				autoUpdateInput: false,
				autoApply: true,
				ranges: ranges,
				locale: locale
			};

			if (typeof customParams !== 'undefined') {
				params = $.extend(params, customParams);
			}
			parentElement
				.find('.js-date__btn')
				.off()
				.on('click', (e) => {
					$(e.currentTarget).parent().next('.dateRangeField')[0].focus();
				});
			elements.each((_index, element) => {
				let el = $(element);
				let currentParams = $.extend(true, params, el.data('params'));
				el.daterangepicker(currentParams)
					.on('apply.daterangepicker', function (_ev, picker) {
						$(this).val(
							picker.startDate.format(currentParams.locale.format) +
								',' +
								picker.endDate.format(currentParams.locale.format)
						);
						$(this).trigger('change');
					})
					.on('show.daterangepicker', (ev, picker) => {
						App.Fields.Utils.positionPicker(ev, picker);
					})
					.on('showCalendar.daterangepicker', (ev, picker) => {
						App.Fields.Utils.positionPicker(ev, picker);
						picker.container.addClass('js-visible');
					})
					.on('hide.daterangepicker', (_ev, picker) => {
						picker.container.removeClass('js-visible');
					});
				App.Fields.Utils.registerMobileDateRangePicker(el);
			});
		},
		/**
		 * Function to get Date Instance
		 * @param {string} dateTime
		 * @param {string} dateFormat user date format
		 * @returns {Date}
		 */
		getDateInstance: function (dateTime, dateFormat = CONFIG.dateFormat) {
			let dateTimeComponents = dateTime.split(' '),
				dateComponent = dateTimeComponents[0],
				timeComponent = dateTimeComponents[1],
				seconds = '00',
				dotMode = '-';
			if (dateFormat.indexOf('.') !== -1) {
				dotMode = '.';
			} else if (dateFormat.indexOf('/') !== -1) {
				dotMode = '/';
			}
			let splittedDate = dateComponent.split(dotMode),
				splittedDateFormat = dateFormat.split(dotMode),
				year = splittedDate[splittedDateFormat.indexOf('yyyy')],
				month = splittedDate[splittedDateFormat.indexOf('mm')],
				day = splittedDate[splittedDateFormat.indexOf('dd')],
				dateInstance = Date.parse(year + '/' + month + '/' + day);

			if (isNaN(dateInstance) || year.length !== 4 || month.length > 2 || day.length > 2 || dateInstance == null) {
				throw app.vtranslate('JS_INVALID_DATE');
			}
			//Before creating date object time is set to 00
			//because as while calculating date object it depends system timezone
			if (typeof timeComponent === 'undefined') {
				timeComponent = '00:00:00';
			}
			let timeSections = timeComponent.split(':');
			if (typeof timeSections[2] !== 'undefined') {
				seconds = timeSections[2];
			}
			//Am/Pm component exits
			if (typeof dateTimeComponents[2] !== 'undefined') {
				if (dateTimeComponents[2].toLowerCase() === 'pm' && timeSections[0] !== '12') {
					timeSections[0] = parseInt(timeSections[0], 10) + 12;
				}
				if (dateTimeComponents[2].toLowerCase() === 'am' && timeSections[0] === '12') {
					timeSections[0] = '00';
				}
			}
			month = month - 1;
			return new Date(year, month, day, timeSections[0], timeSections[1], seconds);
		},
		/**
		 * Format the Date object to a date in the format DB format, example: `2018-07-23`
		 * @param {Date} date
		 * @returns {string}
		 */
		dateToDbFormat: function (date) {
			let d = date.getDate();
			let m = date.getMonth() + 1;
			let y = date.getFullYear();
			d = d <= 9 ? '0' + d : d;
			m = m <= 9 ? '0' + m : m;
			return y + '-' + m + '-' + d;
		},
		/**
		 * Format the Date object to a date in the format user format, example: `2018/07/23`
		 * @param {Date} date
		 * @returns {string}
		 */
		dateToUserFormat: function (date, format = CONFIG.dateFormat) {
			if (typeof date === 'string') {
				date = new Date(date);
			}
			let m = date.getMonth() + 1,
				d = date.getDate();
			d = d <= 9 ? '0' + d : d;
			m = m <= 9 ? '0' + m : m;
			return format.replace('yyyy', date.getFullYear()).replace('mm', m).replace('dd', d);
		},
		/**
		 * Get last day of month
		 * @param {integer} year
		 * @param {integer} month
		 * @returns {integer}
		 */
		getLastMonthDay: function (year, month) {
			let date = new Date(year, month, 0);
			return date.getDate();
		},
		/**
		 * Get number of days from a given date to now
		 * @param {Date} dateTime
		 * @returns {integer}
		 */
		howManyDaysFromDate: function (dateTime) {
			let today = new Date();
			let toTime = new Date(today.getFullYear(), today.getMonth(), today.getDate()).getTime();
			return Math.floor((toTime - dateTime.getTime()) / (1000 * 60 * 60 * 24)) + 1;
		},
		/**
		 * Converting the date format to the format supported in the DatePicker, example: `yyyy-mm-dd` >> `Y-m-d`
		 * @param {string} dateFormat
		 * @returns {string}
		 */
		convertToDatePickerFormat: function (dateFormat) {
			switch (dateFormat) {
				case 'yyyy-mm-dd':
					return 'Y-m-d';
				case 'mm-dd-yyyy':
					return 'm-d-Y';
				case 'dd-mm-yyyy':
					return 'd-m-Y';
				case 'yyyy.mm.dd':
					return 'Y.m.d';
				case 'mm.dd.yyyy':
					return 'm.d.Y';
				case 'dd.mm.yyyy':
					return 'd.m.Y';
				case 'yyyy/mm/dd':
					return 'Y/m/d';
				case 'mm/dd/yyyy':
					return 'm/d/Y';
				case 'dd/mm/yyyy':
					return 'd/m/Y';
			}
		}
	},
	DateTime: class DateTime {
		constructor(container, params) {
			this.container = container;
			this.init(params);
		}
		/**
		 * Register function
		 * @param {jQuery} container
		 * @param {Object} params
		 */
		static register(container, params) {
			if (typeof container === 'undefined') {
				container = $('body');
			}
			if (container.hasClass('dateTimePickerField') && !container.prop('disabled')) {
				return new DateTime(container, params);
			}
			const instances = [];
			container.find('.dateTimePickerField:not([disabled])').each((_, e) => {
				let element = $(e);
				instances.push(new DateTime(element, params));
			});
			return instances;
		}
		/**
		 * Format the Date object to a date in the format user format, example: `2018/07/23 03:00`
		 * @param {Date} dateTime Date object
		 * @returns {string} `2018/07/23 03:00`
		 */
		static dateToUserFormat(dateTime, format = CONFIG.dateFormat) {
			format = format.toUpperCase();
			if (CONFIG.hourFormat == 24) {
				format += ' HH:mm';
			} else {
				format += ' hh:mm A';
			}
			return moment(dateTime).format(format);
		}
		/**
		 * Gets default locale data
		 * @returns {Object}
		 */
		static getDefaultLocale() {
			if (!this.locale) {
				this.locale = {
					separator: ',',
					applyLabel: app.vtranslate('JS_APPLY'),
					cancelLabel: app.vtranslate('JS_CANCEL'),
					fromLabel: app.vtranslate('JS_FROM'),
					toLabel: app.vtranslate('JS_TO'),
					customRangeLabel: app.vtranslate('JS_CUSTOM'),
					weekLabel: app.vtranslate('JS_WEEK').substr(0, 1),
					firstDay: CONFIG.firstDayOfWeekNo,
					daysOfWeek: App.Fields.Date.daysTranslated,
					monthNames: App.Fields.Date.fullMonthsTranslated
				};
			}
			return { ...this.locale };
		}

		/**
		 * Initialization datetime
		 */
		init(customParams) {
			$('.input-group-text', this.container.closest('.dateTime')).on('click', function (e) {
				$(e.currentTarget).closest('.dateTime').find('input.dateTimePickerField').get(0).focus();
			});
			let dateFormat = CONFIG.dateFormat.toUpperCase();
			const elementDateFormat = this.container.data('dateFormat');
			if (typeof elementDateFormat !== 'undefined') {
				dateFormat = elementDateFormat.toUpperCase();
			}
			let hourFormat = CONFIG.hourFormat;
			const elementHourFormat = this.container.data('hourFormat');
			if (typeof elementHourFormat !== 'undefined') {
				hourFormat = elementHourFormat;
			}
			let timePicker24Hour = true;
			let timeFormat = 'HH:mm';
			if (hourFormat != '24') {
				timePicker24Hour = false;
				timeFormat = 'hh:mm A';
			}
			const format = dateFormat + ' ' + timeFormat;
			let isDateRangePicker = this.container.data('calendarType') !== 'range';
			let locale = App.Fields.DateTime.getDefaultLocale();
			locale.format = format;
			let params = {
				language: CONFIG.langKey,
				parentEl: this.container.closest('.dateTime'),
				singleDatePicker: isDateRangePicker,
				showDropdowns: true,
				timePicker: true,
				autoUpdateInput: false,
				timePicker24Hour: timePicker24Hour,
				timePickerIncrement: 1,
				autoApply: true,
				opens: 'left',
				locale: locale
			};
			if (typeof customParams !== 'undefined') {
				params = $.extend(params, customParams);
			}
			this.container
				.daterangepicker(params)
				.on('apply.daterangepicker', function applyDateRangePickerHandler(_ev, picker) {
					if (isDateRangePicker) {
						$(this).val(picker.startDate.format(format));
					} else {
						$(this).val(picker.startDate.format(format) + ',' + picker.endDate.format(format));
					}
				})
				.on('showCalendar.daterangepicker', (ev, picker) => {
					App.Fields.Utils.positionPicker(ev, picker);
					picker.container.addClass('js-visible');
				});
			App.Fields.Utils.registerMobileDateRangePicker(this.container);
		}
	},
	Time: {
		/**
		 * Format the Date object to a date in the format user format, example: `2018/07/23`
		 * @param {Date} date
		 * @returns {string}
		 */
		dateToUserFormat: function (date, timeFormat) {
			if (typeof date === 'string') {
				date = new Date(date);
			}
			if (!timeFormat) {
				if (CONFIG.hourFormat == 24) {
					timeFormat = 'HH:mm';
				} else {
					timeFormat = 'hh:mm A';
				}
			}
			return moment(date).format(timeFormat);
		}
	},
	Colors: {
		/**
		 * Function to check whether the color is dark or light
		 */
		getColorContrast: function (hexcolor) {
			var r = parseInt(hexcolor.substr(0, 2), 16);
			var g = parseInt(hexcolor.substr(2, 2), 16);
			var b = parseInt(hexcolor.substr(4, 2), 16);
			var yiq = (r * 299 + g * 587 + b * 114) / 1000;
			return yiq >= 128 ? 'light' : 'dark';
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
		showPicker({ color, fieldToUpdate, bgToUpdate, cb }) {
			let registerPickerEvents = (modalContainer) => {
				let picker = window.ColorPicker.mount({
					el: modalContainer.find('.js-color-picker')[0],
					currentColor: color
				});
				modalContainer.find('.js-modal__save').on('click', (_) => {
					let newColor = picker.getColor().hex;
					cb && cb(newColor);
					bgToUpdate && bgToUpdate.css('background', newColor);
					fieldToUpdate && fieldToUpdate.val(newColor);
					app.hideModalWindow(false, modalContainer.closest('.js-modal-container')[0].id);
				});
			};
			let url = `index.php?module=AppComponents&view=ColorPickerModal${color ? '&color=' + color : ''}`;
			app.showModalWindow({ url, cb: registerPickerEvents.bind(this) });
		}
	},
	Text: {
		/**
		 * Register clip
		 * @param {HTMLElement|jQuery} container
		 * @param {string} key
		 * @returns {ClipboardJS|undefined}
		 */
		registerCopyClipboard: function (container, key = '.clipboard') {
			if (typeof container !== 'object' || $(container).length === 0) {
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
					app.showNotify({
						text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
						type: 'success'
					});
					trigger = $(trigger);
					const element = $(trigger.data('copyTarget'), container);
					let val;
					if (typeof trigger.data('copyType') !== 'undefined') {
						if (element.is('select')) {
							val = element.find('option:selected').data(trigger.data('copyType'));
						} else {
							val = element.data(trigger.data('copyType'));
						}
					} else if (typeof trigger.data('copy-attribute') !== 'undefined') {
						val = trigger.data(trigger.data('copy-attribute'));
					} else {
						val = element.val();
					}
					return val;
				}
			});
		},
		Editor: class {
			static initialization = false;
			constructor(container, params) {
				if (window.App.Fields.Text.Editor.initialization === false) {
					CKEDITOR.disableAutoInline = true;
					CKEDITOR.plugins.addExternal(
						'base64image',
						app.getMainParams('siteUrl') + 'layouts/resources/libraries/ckeditor/base64image/'
					);
					window.App.Fields.Text.Editor.initialization = true;
				}
				this.container = container;
				this.init(container, params);
			}
			/**
			 * Register function
			 * @param {jQuery} container
			 * @param {Object} params
			 */
			static register(container, params) {
				if (typeof container === 'undefined') {
					container = $('body');
				}
				if (container.hasClass('js-editor') && !container.prop('disabled')) {
					return new App.Fields.Text.Editor(container, $.extend(params, container.data()));
				}
				const instances = [];
				container.find('.js-editor:not([disabled])').each((_, e) => {
					let element = $(e);
					instances.push(new App.Fields.Text.Editor(element, $.extend(params, element.data())));
				});
				return instances;
			}
			/**
			 * Initiation
			 * @param {jQuery} element
			 * @param {Object} params
			 */
			init(element, params) {
				let config = {};
				if (element.hasClass('js-editor--basic')) {
					config.toolbar = 'Min';
				}
				if (element.data('height')) {
					config.height = element.data('height');
				}
				params = $.extend(config, params);
				this.isModal = element.closest('.js-modal-container').length;
				if (this.isModal && element.is(':visible')) {
					let self = this;
					this.progressInstance = $.progressIndicator({
						blockInfo: {
							enabled: true,
							onBlock: () => {
								self.loadEditor(element, params);
							}
						}
					});
				} else {
					App.Fields.Text.destroyEditor(element);
					this.loadEditor(element, params);
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
					language: CONFIG.langKey,
					allowedContent: true,
					disableNativeSpellChecker: false,
					extraAllowedContent: 'div{page-break-after*}',
					format_tags: 'p;h1;h2;h3;h4;h5;h6;pre;address;div',
					removeButtons: '',
					enterMode: CKEDITOR.ENTER_BR,
					shiftEnterMode: CKEDITOR.ENTER_P,
					emojiEnabled: false,
					mentionsEnabled: false,
					clipboard_handleImages: false,
					on: {
						instanceReady: (evt) => {
							evt.editor.on('blur', function () {
								evt.editor.updateElement();
							});
							if (this.isModal && this.progressInstance) {
								this.progressInstance.progressIndicator({ mode: 'hide' });
							}
						},
						beforeCommandExec: (e) => {
							if (e.editor.mode === 'source') {
								return this.validate(element, e);
							}
						}
					},
					removePlugins: 'scayt',
					extraPlugins:
						'colorbutton,pagebreak,colordialog,find,selectall,showblocks,div,print,font,justify,bidi,base64image',
					toolbar: 'Full',
					toolbar_Full: [
						{
							name: 'clipboard',
							items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
						},
						{ name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll'] },
						{ name: 'links', items: ['Link', 'Unlink'] },
						{
							name: 'insert',
							items: ['base64image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak']
						},
						{ name: 'tools', items: ['Maximize', 'ShowBlocks'] },
						{ name: 'paragraph', items: ['Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv'] },
						{ name: 'document', items: ['Source', 'Print'] },
						'/',
						{ name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
						{
							name: 'basicstyles',
							items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript']
						},
						{ name: 'colors', items: ['TextColor', 'BGColor'] },
						{
							name: 'paragraph',
							items: [
								'NumberedList',
								'BulletedList',
								'-',
								'JustifyLeft',
								'JustifyCenter',
								'JustifyRight',
								'JustifyBlock',
								'-',
								'BidiLtr',
								'BidiRtl'
							]
						},
						{ name: 'basicstyles', items: ['CopyFormatting', 'RemoveFormat'] }
					],
					toolbar_Min: [
						{
							name: 'basicstyles',
							items: ['Bold', 'Italic', 'Underline', 'Strike']
						},
						{ name: 'colors', items: ['TextColor', 'BGColor'] },
						{ name: 'tools', items: ['Maximize'] },
						{
							name: 'paragraph',
							items: [
								'NumberedList',
								'BulletedList',
								'-',
								'JustifyLeft',
								'JustifyCenter',
								'JustifyRight',
								'JustifyBlock',
								'-',
								'BidiLtr',
								'BidiRtl'
							]
						},
						{ name: 'basicstyles', items: ['CopyFormatting', 'RemoveFormat', 'Source'] }
					],
					toolbar_Micro: [
						{
							name: 'basicstyles',
							items: ['Bold', 'Italic', 'Underline', 'Strike']
						},
						{ name: 'colors', items: ['TextColor', 'BGColor'] },
						{
							name: 'paragraph',
							items: ['NumberedList', 'BulletedList', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
						},
						{ name: 'basicstyles', items: ['CopyFormatting', 'RemoveFormat'] }
					],
					toolbar_Clipboard: [
						{ name: 'document', items: ['Print'] },
						{ name: 'basicstyles', items: ['CopyFormatting', 'RemoveFormat'] },
						{
							name: 'clipboard',
							items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
						}
					],
					toolbar_PDF: [
						{
							name: 'clipboard',
							items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
						},
						{ name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll', '-'] },
						{ name: 'links', items: ['Link', 'Unlink'] },
						{
							name: 'insert',
							items: ['base64image', 'Table', 'HorizontalRule', 'PageBreak']
						},
						{ name: 'tools', items: ['Maximize', 'ShowBlocks'] },
						{ name: 'document', items: ['Source'] },
						'/',
						{ name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
						{
							name: 'basicstyles',
							items: ['Bold', 'Italic', 'Underline', 'Strike']
						},
						{ name: 'colors', items: ['TextColor', 'BGColor'] },
						{
							name: 'paragraph',
							items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight']
						},
						{ name: 'basicstyles', items: ['CopyFormatting', 'RemoveFormat'] }
					]
				};
				if (typeof customConfig !== 'undefined') {
					config = $.extend(config, customConfig);
				}
				config = Object.assign(config, element.data());
				if (config.emojiEnabled) {
					let emojiToolbar = { name: 'links', items: ['EmojiPanel'] };
					if (typeof config.toolbar === 'string') {
						config[`toolbar_${config.toolbar}`].push(emojiToolbar);
					} else if (Array.isArray(config.toolbar)) {
						config.toolbar.push(emojiToolbar);
					}
					config.extraPlugins = config.extraPlugins + ',emoji';
					config.outputTemplate = '{id}';
				}
				if (config.mentionsEnabled) {
					config.extraPlugins = config.extraPlugins + ',mentions';
					config.mentions = this.registerMentions();
				}
				if (instance) {
					CKEDITOR.remove(instance);
				}
				element.ckeditor(config);
			}

			/**
			 * Register mentions
			 * @returns {Array}
			 */
			registerMentions() {
				let minSerchTextLength = app.getMainParams('gsMinLength');
				return [
					{
						feed: this.getMentionUsersData.bind(this),
						itemTemplate: `<li data-id="{id}" class="row no-gutters">
											<div class="col-2 c-img__completion__container">
												<div class="{icon} m-auto u-w-fit u-fs-14px"></div>
												<img src="{image}" class="c-img__completion mr-2" alt="{label}" title="{label}">
											</div>
											<div class="col row-10 no-gutters u-overflow-x-hidden">
												<strong class="u-text-ellipsis--no-hover col-12">{label}</strong>
												<div class="fullname col-12 u-text-ellipsis--no-hover text-muted small">{category}</div>
											</div>
										</li>`,
						outputTemplate: '<a href="#" data-id="@{id}" data-module="{module}">{label}</a>',
						minChars: minSerchTextLength
					},
					{
						feed: App.Fields.Text.getMentionData,
						marker: '#',
						pattern: /#[wа-я]{1,}|#\w{3,}$/,
						itemTemplate: `<li data-id="{id}" class="row no-gutters">
											<div class="col-2 c-circle-icon">
												<span class="yfm-{module}"></span>
											</div>
											<div class="col-10 row no-gutters pl-1 u-overflow-x-hidden">
												<strong class="u-text-ellipsis--no-hover col-12">{label}</strong>
												<div class="fullname col-12 u-text-ellipsis--no-hover text-muted small">{category}</div>
											</div>
										</li>`,
						outputTemplate: '<a href="#" data-id="#{id}" data-module="{module}">{label}</a>',
						minChars: minSerchTextLength
					}
				];
			}

			/**
			 * Get mention Users data (invoked by ck editor mentions plugin)
			 * @param {object} opts
			 * @param {function} callback
			 */
			getMentionUsersData(opts, callback) {
				App.Fields.Text.getMentionData(opts, callback, 'owners');
			}

			/**
			 * Function to validate the field value
			 * @param {jQuery} element
			 * @param {object} e
			 */
			validate(element) {
				let status = true,
					params;
				if (element.data('purifyMode')) {
					params = {
						module: 'Users',
						action: 'Fields',
						mode: 'validateByMode',
						purifyMode: element.data('purifyMode'),
						value: element.val()
					};
				} else {
					params = {
						module: element.closest('form').find('[name="module"]').val(),
						action: 'Fields',
						mode: 'validateForField',
						fieldName: element.attr('name'),
						fieldValue: element.val()
					};
				}
				AppConnector.request({
					async: false,
					data: params
				})
					.done(function (data) {
						element.val(data.result.raw);
					})
					.fail(function () {
						app.showNotify({
							type: 'error',
							text: app.vtranslate('JS_UNEXPECTED_ERROR')
						});
						status = false;
					});
				return status;
			}
		},
		/**
		 * Completions class for contenteditable html element for records, users and emojis. Params can be passed in data-completions- of contenteditable element or as argument. Default params:
		 * {
					completionsCollection: {
						records: true,
						users: true,
						emojis: true
					}
			}
		 */
		Completions: class {
			/**
			 * Constructor
			 * @param {jQuery} inputDiv - contenteditable div
			 * @param params
			 */
			constructor(inputDiv = $('.js-completions').eq(0), params = {}) {
				if (typeof inputDiv === 'undefined' || inputDiv.length === 0) {
					return;
				} else if (inputDiv.length === undefined) {
					inputDiv = $(inputDiv);
				}
				let basicParams = {
					completionsCollection: {
						records: true,
						users: true,
						emojis: true
					},
					autolink: true
				};
				this.params = Object.assign(basicParams, inputDiv.data(), params);
				this.inputDiv = inputDiv;
				this.collection = [];
				if (this.params.completionsCollection.records) {
					this.collection.push(this.registerMentionCollection('#'));
				}
				if (this.params.completionsCollection.users) {
					this.collection.push(this.registerMentionCollection('@', 'owners'));
				}
				if (this.params.completionsCollection.emojis) {
					this.collection.push(this.registerEmojiCollection());
				}
				this.register(inputDiv);
			}

			/**
			 * Register mention collection for tribute.js
			 * @param {string} symbol
			 * @param {string} searchModule
			 * @returns {{trigger: *, selectTemplate: selectTemplate, values: values, menuItemTemplate: (function(*): string), lookup: string, fillAttr: string}}
			 */
			registerMentionCollection(symbol, searchModule = '-') {
				let self = this;
				return {
					trigger: symbol,
					selectTemplate: function (item) {
						if (this.range.isContentEditable(this.current.element)) {
							return `<a href="#" data-id="${symbol + item.original.id}" data-module="${
								item.original.module
							}">${item.original.label.split('(')[0].trim()}</a>`;
						}
						return symbol + item.original.label;
					},
					values: (text, cb) => {
						if (text.length >= CONFIG.globalSearchAutocompleteMinLength) {
							App.Fields.Text.getMentionData(text, (users) => cb(users), searchModule);
						}
					},
					menuItemTemplate: function (item) {
						return self.mentionTemplate({
							id: item.original.id,
							module: item.original.module,
							category: item.original.category,
							image: item.original.image,
							label: item.original.label,
							icon: item.original.icon
						});
					},
					lookup: 'label',
					fillAttr: 'label'
				};
			}

			/**
			 * Register emoji collection for tribute.js
			 * @returns {{trigger: string, selectTemplate: selectTemplate, menuItemTemplate: (function(*): string), lookup: string, fillAttr: string, values: Array}}
			 */
			registerEmojiCollection() {
				return {
					trigger: ':',
					selectTemplate: function (item) {
						if (this.range.isContentEditable(this.current.element)) {
							return `<span data-id="${item.original.id}">${item.original.symbol}</span>`;
						}
						return item.original.symbol;
					},
					menuItemTemplate: function (item) {
						return `<span data-id="${item.original.id}">${item.original.symbol} ${item.original.id}</span>`;
					},
					lookup: 'id',
					fillAttr: 'keywords',
					values: (text, cb) => {
						if (text.length >= 2) {
							cb(App.emoji);
						}
					}
				};
			}
			/*
			 * Mention template
			 */
			mentionTemplate(params) {
				let icon = '';
				if (params.module !== undefined) {
					icon = `yfm-${params.module}`;
				}
				if (params.icon !== undefined && params.icon !== '') {
					icon = params.icon;
				}
				let avatar = `<div class="col-2 c-circle-icon">
								<span class="${icon}"></span>
							</div>`;
				if (params.image !== undefined && params.image !== '') {
					avatar = `<div class="col-2 c-img__completion__container m-0"><img src="${params.image}" class="c-img__completion" alt=${params.label}" title="${params.label}"></div>`;
				}
				return `<div data-id="${params.id}" class="row no-gutters">
							${avatar}
							<div class="col-10 row no-gutters pl-1 u-overflow-x-hidden">
								<strong class="u-text-ellipsis--no-hover col-12">${params.label}</strong>
								<div class="fullname col-12 u-text-ellipsis--no-hover text-muted small">${params.category}</div>
							</div>
						</div>`;
			}
			/**
			 * Auto link
			 */
			autoLink() {
				let fillChar = '\u200B';
				let sel = window.getSelection(),
					range = sel.getRangeAt(0).cloneRange(),
					offset,
					charCode,
					getParentByTagName = function (node, tags) {
						if (node && !isBody(node)) {
							while (node) {
								if (tags[node.tagName] || isBody(node)) {
									return !tags[node.tagName] && isBody(node) ? null : node;
								}
								node = node.parentNode;
							}
						}
						return null;
					},
					isBody = function (node) {
						return node && node.nodeType == 1 && node.tagName.toLowerCase() == 'body';
					},
					html = function (str) {
						return str.replace(/&((g|l|quo)t|amp|#39);/g, function (m) {
							return { '&lt;': '<', '&amp;': '&', '&quot;': '"', '&gt;': '>', '&#39;': "'" }[m];
						});
					},
					isFillChar = function (node) {
						return node.nodeType == 3 && !node.nodeValue.replace(new RegExp('' + fillChar), '').length;
					};

				let start = range.startContainer;
				while (start.nodeType == 1 && range.startOffset > 0) {
					start = range.startContainer.childNodes[range.startOffset - 1];
					if (!start) break;
					range.setStart(start, start.nodeType == 1 ? start.childNodes.length : start.nodeValue.length);
					range.collapse(true);
					start = range.startContainer;
				}
				do {
					if (range.startOffset == 0) {
						start = range.startContainer.previousSibling;
						while (start && start.nodeType == 1) {
							start = start.lastChild;
						}
						if (!start || isFillChar(start)) break;
						offset = start.nodeValue.length;
					} else {
						start = range.startContainer;
						offset = range.startOffset;
					}
					range.setStart(start, offset - 1);
					charCode = range.toString().charCodeAt(0);
				} while (charCode != 160 && charCode != 32);
				if (
					range
						.toString()
						.replace(new RegExp(fillChar, 'g'), '')
						.match(/(?:https?:\/\/|ssh:\/\/|ftp:\/\/|file:\/|www\.)/i)
				) {
					while (range.toString().length) {
						if (/^(?:https?:\/\/|ssh:\/\/|ftp:\/\/|file:\/|www\.)/i.test(range.toString())) break;
						try {
							range.setStart(range.startContainer, range.startOffset + 1);
						} catch (e) {
							let startCont = range.startContainer,
								next;
							while (!(next = startCont.nextSibling)) {
								if (isBody(startCont)) return;
								startCont = startCont.parentNode;
							}
							range.setStart(next, 0);
						}
					}
					if (getParentByTagName(range.startContainer, { a: 1, A: 1 })) return;
					let href = range
							.toString()
							.replace(/<[^>]+>/g, '')
							.replace(new RegExp(fillChar, 'g'), ''),
						hrefFull = /^(?:https?:\/\/)/gi.test(href) ? href : 'http://' + href,
						url = new URL(hrefFull);
					let allowedHosts = CONFIG.purifierAllowedDomains;
					if (allowedHosts !== false && allowedHosts.indexOf(url.host) === -1) {
						return;
					}
					let a = document.createElement('a'),
						text = document.createTextNode(' ');
					a.appendChild(range.extractContents());
					a.innerHTML = href;
					a.href = hrefFull ? html(hrefFull) : '';
					a.setAttribute('rel', 'noopener noreferrer');
					a.setAttribute('target', '_blank');

					range.insertNode(a);
					a.parentNode.insertBefore(text, a.nextSibling);
					range.setStart(text.nextSibling, 0);
					range.collapse(true);
					sel.removeAllRanges();
					sel.addRange(range);
				}
			}
			/**
			 * Register
			 * @param {jQuery} inputDiv - contenteditable div
			 */
			register(inputDiv) {
				const self = this;
				this.completionsCollection = new Tribute({
					collection: self.collection,
					allowSpaces: true
				});
				this.completionsCollection.attach(inputDiv[0]);
				if (this.params.completionsTextarea !== undefined) {
					this.registerCompletionsTextArea(inputDiv);
				}
				if (this.params.completionsButtons !== undefined) {
					this.registerCompletionsButtons();
				}
				if (this.params.autolink) {
					this.registerAutoLinker(inputDiv);
				}
				if (App.emoji === undefined) {
					fetch(`${CONFIG.siteUrl}/vendor/ckeditor/ckeditor/plugins/emoji/emoji.json`)
						.then((response) => response.json())
						.then((response) => {
							App.emoji = response;
						})
						.catch((error) => console.error('Error:', error));
				}
				this.registerTagClick(inputDiv);
			}

			/**
			 * Register autolink
			 * @param {jQuery} inputDiv - contenteditable div
			 */
			registerAutoLinker(inputDiv) {
				inputDiv.on('keypress', (e) => {
					if (e.keyCode === 32 || e.keyCode === 13) {
						this.autoLink();
					}
				});
			}

			/**
			 * Register completons hidden textarea - useful with forms
			 * @param {jQuery} inputDiv - contenteditable div
			 */
			registerCompletionsTextArea(inputDiv) {
				let textarea = inputDiv.siblings(`[name=${inputDiv.attr('id')}]`);
				inputDiv
					.on('focus', function () {
						textarea.val(inputDiv.html());
					})
					.on('blur keyup paste input', function () {
						textarea.val(inputDiv.html());
					});
			}

			/**
			 * Register tag click
			 * @param inputDiv
			 */
			registerTagClick(inputDiv) {
				inputDiv
					.closest('.js-completions__container')
					.find('.js-completions__messages')
					.on('click', '.js-completions__tag', (e) => {
						e.preventDefault();
						inputDiv.append($(e.target).clone());
					});
			}

			/**
			 * Register completions buttons
			 */
			registerCompletionsButtons() {
				let completionsContainer = this.inputDiv.parents().eq(3);
				completionsContainer.find('.js-completions__users').on('click', (e) => {
					this.completionsCollection.showMenuForCollection(this.inputDiv[0], 1);
				});
				completionsContainer.find('.js-completions__records').on('click', (e) => {
					this.completionsCollection.showMenuForCollection(this.inputDiv[0], 0);
				});
			}
		},

		/**
		 * Get mention data (invoked by ck editor mentions plugin and tribute.js)
		 * @param {object} opts
		 * @param {function} callback
		 * @param {string} searchModule
		 */
		getMentionData(text, callback, searchModule = '-') {
			let basicSearch = new Vtiger_BasicSearch_Js();
			basicSearch.reduceNumberResults = app.getMainParams('gsAmountResponse');
			basicSearch.returnHtml = false;
			basicSearch.searchModule = searchModule;
			if (typeof text === 'object') {
				text = text.query.toLowerCase();
			}
			if (searchModule === 'owners') {
				AppConnector.request({
					action: 'Search',
					mode: 'owners',
					value: text
				}).done((data) => {
					callback(data.result);
				});
			} else {
				basicSearch.search(text).done(function (data) {
					data = JSON.parse(data);
					let serverDataFormat = data.result,
						reponseDataList = [];
					for (let id in serverDataFormat) {
						let responseData = serverDataFormat[id];
						reponseDataList.push(responseData);
					}
					callback(reponseDataList);
				});
			}
		},

		/**
		 * Destroy ckEditor
		 * @param {jQuery} element
		 */
		destroyEditor(element) {
			if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances && element.attr('id') in CKEDITOR.instances) {
				CKEDITOR.instances[element.attr('id')].destroy();
			}
		},

		/**
		 * Generate random character
		 * @returns {string}
		 */
		generateRandomChar() {
			const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZ';
			const rand = Math.floor(Math.random() * chars.length);
			return chars.substring(rand, rand + 1);
		},

		/**
		 * generate random hash
		 * @returns {string}
		 */
		generateRandomHash(prefix = '') {
			prefix = prefix.toString();
			const hash =
				Math.random().toString(36).substr(2, 10) +
				Math.random().toString(36).substr(2, 10) +
				new Date().valueOf() +
				Math.random().toString(36).substr(2, 6);
			return prefix ? prefix + hash : hash;
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
			if (typeof parent === 'undefined') {
				parent = $('body');
			}
			if (typeof view === 'undefined') {
				const select2Elements = $('select.select2', parent).toArray();
				select2Elements.forEach((elem) => {
					this.changeSelectElementView($(elem), 'select2', viewParams);
				});
				return;
			}
			//If view is select2, This will convert the ui of select boxes to select2 elements.
			if (view === 'select2') {
				return App.Fields.Picklist.showSelect2ElementView(parent, viewParams);
			} else {
				app.errorLog(new Error(`Unknown select type [${view}]`));
			}
		},
		/**
		 * Function which will show the select2 element for select boxes . This will use select2 library
		 */
		showSelect2ElementView(selectElement, params) {
			let self = this;
			selectElement = $(selectElement);
			if (typeof params === 'undefined') {
				params = {};
			}
			if ($(selectElement).length > 1) {
				return $(selectElement).each((_, element) => {
					this.showSelect2ElementView($(element).eq(0), params);
				});
			}
			params = this.registerParams(selectElement, params);
			if (params.selectLazy && !selectElement.hasClass('js-lazy-select-active')) {
				return App.Fields.Picklist.showLazySelect(selectElement, {
					lazyElements: app.getMainParams('picklistLimit'),
					data: this.registerLazySelectOptions(selectElement),
					selectParams: params
				});
			}
			const computeDropdownHeight = (e, dropdownContainer) => {
				setTimeout(() => {
					if (!dropdownContainer.find('.select2-dropdown--above').length) {
						const dropdownList = dropdownContainer.find('.select2-results > .select2-results__options');
						const marginBottom = 35;
						const selectOffsetTop = $(e.currentTarget).offset().top;
						dropdownList.css({
							'max-height':
								$(window).height() - selectOffsetTop - marginBottom - (dropdownList.offset().top - selectOffsetTop)
						});
					}
				}, 100);
			};
			selectElement.each(function () {
				let select = $(this);
				let htmlBoolParams = select.data('select');
				if (htmlBoolParams === 'tags') {
					params.tags = true;
					params.tokenSeparators = [','];
				} else {
					params[htmlBoolParams] = true;
				}
				select
					.select2(params)
					.on('select2:open', (e) => {
						computeDropdownHeight(e, $('.select2-container--open:not(.select2-container--below)'));
						if (select.data('unselecting')) {
							select.removeData('unselecting');
							setTimeout(function () {
								select.each(function () {
									$(this).select2('close');
								});
							}, 1);
						}
						let instance = $(e.currentTarget).data('select2');
						instance.$dropdown.css('z-index', 1000002);
						/**
						 * Fix auto focusing in select2 with jQuery 3.6.0
						 * see: https://github.com/select2/select2/issues/5993
						 */
						if (instance.dropdown.$search) {
							instance.dropdown.$search.get(0).focus();
						}
					})
					.on('select2:unselect', () => {
						select.data('unselecting', true);
					});
				if (typeof self[params.selectCb] === 'function') {
					self[params.selectCb](select, params);
				}
			});
			return selectElement;
		},
		/**
		 * Register params
		 * @param selectElement
		 * @param params
		 * @returns {*}
		 */
		registerParams(selectElement, params) {
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
			params.theme = 'bootstrap';
			const width = $(selectElement).data('width');
			if (typeof width !== 'undefined') {
				params.width = width;
			} else {
				params.width = '100%';
			}
			params.containerCssClass = 'form-control w-100';
			const containerCssClass = selectElement.data('containerCssClass');
			if (typeof containerCssClass !== 'undefined') {
				params.containerCssClass += ' ' + containerCssClass;
			}
			params.language.noResults = function () {
				return app.vtranslate('JS_NO_RESULTS_FOUND');
			};
			params.language.removeAllItems = function () {
				return app.vtranslate('JS_REMOVE_ALL_ITEMS');
			};
			// Sort DOM nodes alphabetically in select box.
			if (typeof params['customSortOptGroup'] !== 'undefined' && params['customSortOptGroup']) {
				$('optgroup', selectElement).each(function () {
					let optgroup = $(this);
					let options = optgroup
						.children()
						.toArray()
						.sort(function (a, b) {
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
			if (typeof params.maximumSelectionLength !== 'undefined' && typeof params.formatSelectionTooBig === 'undefined') {
				//custom function which will return the maximum selection size exceeds message.
				var formatSelectionExceeds = function (limit) {
					return app.vtranslate('JS_YOU_CAN_SELECT_ONLY') + ' ' + limit.maximum + ' ' + app.vtranslate('JS_ITEMS');
				};
				params.language.maximumSelected = formatSelectionExceeds;
			}
			if (typeof selectElement.attr('multiple') !== 'undefined' && !params.placeholder) {
				params.placeholder = app.vtranslate('JS_SELECT_SOME_OPTIONS');
			} else if (!params.placeholder) {
				params.placeholder = app.vtranslate('JS_SELECT_AN_OPTION');
			}
			if (typeof params.templateResult === 'undefined') {
				params.templateResult = function (data, container) {
					if (data.element && data.element.className) {
						$(container).addClass(data.element.className);
					}
					let actualElement = $(data.element);
					if (typeof selectElement.data('showAdditionalIcons') !== 'undefined' && actualElement.is('option')) {
						return (
							'<div class="js-element__title d-flex justify-content-between" data-js="appendTo"><div class="u-text-ellipsis--no-hover">' +
							actualElement.text() +
							'</div></div>'
						);
					}
					if (typeof data.name === 'undefined') {
						return data.text;
					}
					if (data.type == 'optgroup') {
						return '<strong>' + data.name + '</strong>';
					} else {
						return '<span>' + data.name + '</span>';
					}
				};
				params.escapeMarkup = function (markup) {
					return markup;
				};
			} else if (typeof this[params.templateResult] === 'function') {
				params.templateResult = this[params.templateResult];
			}
			if (typeof params.templateSelection === 'undefined') {
				params.templateSelection = function (item, container) {
					if (item.element && item.element.className) {
						$(container).addClass(item.element.className);
					}
					if (item.text === '') {
						return item.name;
					}
					return item.text;
				};
			} else if (typeof this[params.templateSelection] === 'function') {
				params.templateSelection = this[params.templateSelection];
			}
			if (selectElement.data('ajaxSearch') === 1) {
				params = this.registerAjaxParams(selectElement, params);
			}
			return params;
		},
		/**
		 * Register ajax params
		 * @param {jQuery} selectElement
		 * @param {Object} params
		 * @returns {Object}
		 */
		registerAjaxParams(selectElement, params) {
			params.tags = false;
			params.language.searching = function () {
				return app.vtranslate('JS_SEARCHING');
			};
			params.language.inputTooShort = function (args) {
				var remainingChars = args.minimum - args.input.length;
				return app.vtranslate('JS_INPUT_TOO_SHORT').replace('_LENGTH_', remainingChars);
			};
			params.language.errorLoading = function () {
				return app.vtranslate('JS_NO_RESULTS_FOUND');
			};
			params.placeholder = '';
			params.ajax = {
				url: selectElement.data('ajaxUrl'),
				dataType: 'json',
				delay: 250,
				method: 'POST',
				data:
					params['ajax'] && params['ajax']['data']
						? params['ajax']['data']
						: function (item) {
								console.log(item);
								return {
									value: item.term, // search term
									page: item.page
								};
						  },
				processResults:
					params['ajax'] && params['ajax']['processResults']
						? params['ajax']['processResults']
						: function (data, _params) {
								var items = new Array();
								if (data.success == true) {
									selectElement.find('option').each(function () {
										var currentTarget = $(this);
										items.push({
											label: currentTarget.html(),
											value: currentTarget.val()
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
				if (markup !== 'undefined') return markup;
			};
			params.minimumInputLength = 3;
			if (selectElement.data('minimumInput') !== 'undefined') {
				params.minimumInputLength = selectElement.data('minimumInput');
			}
			params.templateResult = function (data) {
				if (typeof data.name === 'undefined') {
					return data.text;
				}
				if (data.type == 'optgroup') {
					return '<strong>' + data.name + '</strong>';
				} else {
					return '<span>' + data.name + '</span>';
				}
			};
			params.templateSelection = function (data, _container) {
				if (data.text === '') {
					return data.name;
				}
				return data.text;
			};
			return params;
		},
		/**
		 * Prepend template with a flag, function is called select2
		 * @param optionData
		 * @returns {Mixed|jQuery|HTMLElement}
		 */
		prependDataTemplate(optionData) {
			let template = optionData.text;
			if (optionData.id !== undefined && optionData.id !== '') {
				template = $(optionData.element.dataset.template);
				if (optionData.element.dataset.state !== undefined) {
					//check if element has icons with different states
					if (optionData.element.dataset.state === 'active') {
						template
							.find('.js-select-option-event')
							.removeClass(optionData.element.dataset.iconInactive)
							.addClass(optionData.element.dataset.iconActive);
					} else {
						template
							.find('.js-select-option-event')
							.removeClass(optionData.element.dataset.iconActive)
							.addClass(optionData.element.dataset.iconInactive);
					}
				}
			}
			return template;
		},
		/**
		 * Register select sortable
		 * @param select
		 * @param params
		 */
		registerSelectSortable(select, params) {
			this.sortSelectOptions(select);
			this.registerSortEvent(select, params.sortableCb);
		},
		/**
		 * Sort elements (options) in select by data-sort-index
		 * @param {jQuery} select2 element
		 */
		sortSelectOptions(select) {
			select
				.find('option[data-sort-index]')
				.sort((a, b) => {
					return $(b).data('sort-index') < $(a).data('sort-index') ? 1 : -1;
				})
				.appendTo(select);
		},
		/**
		 * Register select drag and drop sorting
		 * @param {jQuery} select2 element
		 * @param {function} callback function
		 */
		registerSortEvent(select, cb = () => {}) {
			let ul = select.next('.select2-container').first('ul.select2-selection__rendered');
			ul.sortable({
				items: 'li:not(.select2-search__field)',
				tolerance: 'pointer',
				stop: function () {
					$(ul.find('.select2-selection__choice').get().reverse()).each(function () {
						let optionTitle = $(this).attr('title');
						select.find('option').each(function () {
							if ($(this).text() === optionTitle) {
								select.prepend($(this));
							}
						});
					});
					cb(select);
					select.trigger('sortable:change');
				}
			});
		},
		/**
		 * Register icons events in select2 options
		 * @param selectElement
		 */
		registerIconsEvents(selectElement) {
			selectElement.on('select2:selecting', (event) => {
				let currentTarget = $(event.params.args.originalEvent.target);
				if (!currentTarget.hasClass('js-select-option-event') && !currentTarget.is('path')) {
					return;
				}
				event.preventDefault();
				if (currentTarget.is('path')) {
					//svg target fix
					currentTarget = currentTarget.closest('.js-select-option-event');
				}
				let currentElementData = $(event.params.args.data.element).data(),
					optionElement = $(event.params.args.data.element),
					progressIndicatorElement = $.progressIndicator({ blockInfo: { enabled: true } });
				AppConnector.request(currentElementData.url)
					.done((data) => {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						let response = data.result;
						if (response && response.result) {
							if (optionElement.attr('data-state') === 'active') {
								optionElement.attr('data-state', 'inactive');
								currentTarget.toggleClass(currentElementData.iconActive + ' ' + currentElementData.iconInactive);
							} else {
								optionElement.attr('data-state', 'active');
								currentTarget.toggleClass(currentElementData.iconInactive + ' ' + currentElementData.iconActive);
							}
							if (response.message) {
								app.showNotify({ text: response.message, type: 'success' });
							}
						} else if (response && response.message) {
							app.showNotify({
								text: response.message,
								type: 'error'
							});
						}
					})
					.fail(function () {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
					});
			});
		},
		/**
		 * Show lazy select based on data passed in js.
		 *
		 * @param   {object}  selectElement  jQuery
		 * @param   {object}  params         contains selectParams object, lazyElements number, data array
		 */
		showLazySelect(selectElement, params) {
			$.fn.select2.amd.require(['select2/data/array', 'select2/utils'], (ArrayData, Utils) => {
				function CustomData($element, params) {
					CustomData.__super__.constructor.call(this, $element, params);
				}
				Utils.Extend(CustomData, ArrayData);
				CustomData.prototype.query = (options, callback) => {
					let results = [];
					if (options.term && options.term !== '') {
						results = params.data.filter((e) => {
							return e.text.toUpperCase().indexOf(options.term.toUpperCase()) >= 0;
						});
					} else {
						results = params.data;
					}
					if (!('page' in options)) {
						options.page = 1;
					}
					let data = {};
					data.results = results.slice((options.page - 1) * params.lazyElements, options.page * params.lazyElements);
					data.pagination = {};
					data.pagination.more = options.page * params.lazyElements < results.length;
					callback(data);
				};
				params.selectParams = Object.assign(params.selectParams, {
					ajax: {},
					dataAdapter: CustomData
				});
				selectElement.addClass('js-lazy-select-active');
				this.showSelect2ElementView(selectElement, params.selectParams);
				let selectedOption = selectElement.data('selected-value');
				if (selectedOption) {
					let text = selectedOption;
					if (
						selectElement.data('fieldinfo').picklistvalues.hasOwnProperty(selectedOption) &&
						!selectElement.get(0).dataset.templateResult
					) {
						text = selectElement.data('fieldinfo').picklistvalues[selectedOption];
					}
					this.createSelectedOption(selectElement, text, selectedOption);
				}
			});
		},
		/**
		 * Register lazy select options
		 *
		 * @param   {object}  selectElement  [selectElement description]
		 *
		 * @return  {object}                 [return description]
		 */
		registerLazySelectOptions(selectElement) {
			let options = [];
			if (
				selectElement.data('fieldinfo') &&
				selectElement.data('fieldinfo').picklistvalues &&
				!selectElement.get(0).dataset.templateResult
			) {
				options = $.map(selectElement.data('fieldinfo').picklistvalues, function (val, key) {
					return { id: key, text: val };
				});
			} else {
				options = $.map(selectElement.find('option'), (item) => {
					return {
						id: item.value,
						element: item,
						text: item.text,
						selected: item.selected,
						disabled: item.disabled
					};
				});
			}
			return options;
		},
		/**
		 * Set a value for the field
		 *
		 * @param   {jQuery}  field Field element
		 * @param   {mixed}  value The value to set
		 * @param   {object}  params Additional parameters [optional]
		 *
		 * @return  {mixed} The value that has been set
		 */
		setValue(field, value, params) {
			let type = 'value';
			if (params && params['type']) {
				type = params['type'];
			}
			const option = this.findOption(field, value, type);
			if (!option) {
				return false;
			}
			if (field.hasClass('js-lazy-select-active')) {
				this.createSelectedOption(field, option.text, option.value);
			} else {
				field.val(option.value).trigger('change');
			}
			return option.value;
		},
		/**
		 * Find option.
		 *
		 * @param   {object}  selectElement  [selectElement description]
		 * @param   {string}  searchValue
		 * @param   {string}  type           value|text|all
		 *
		 * @return  {boolean|object}         false or option object
		 */
		findOption(selectElement, searchValue, type = 'value') {
			let foundOption = false;
			const selectValues = this.getSelectOptions(selectElement);
			const getFieldValueFromText = () => Object.keys(selectValues).find((key) => selectValues[key] === searchValue);
			const valueExists = () => selectValues.hasOwnProperty(searchValue);
			const createOption = () => {
				return { text: selectValues[foundOption], value: foundOption };
			};
			switch (type) {
				case 'value':
					if (valueExists()) {
						foundOption = searchValue;
					}
					break;
				case 'text':
					foundOption = getFieldValueFromText();
					break;
				case 'all':
					if (valueExists()) {
						foundOption = searchValue;
					} else {
						foundOption = getFieldValueFromText();
					}
					break;
			}
			return foundOption ? createOption() : false;
		},
		/**
		 * Get select options
		 *
		 * @param   {object}  selectElement  jQuery
		 *
		 * @return  {object}                 [return description]
		 */
		getSelectOptions(selectElement) {
			if (selectElement.data('fieldinfo') && selectElement.data('fieldinfo').picklistvalues) {
				return selectElement.data('fieldinfo').picklistvalues;
			} else {
				let optionsObject = {};
				selectElement.find('option').each((_i, element) => {
					optionsObject[element.value] = element.text;
				});
				return optionsObject;
			}
		},
		/**
		 * Create selected option
		 *
		 * @param   {object}  selectElement  jQuery
		 * @param   {string}  text
		 * @param   {string}  value
		 */
		createSelectedOption(selectElement, text, value) {
			const newOption = new Option(text, value, true, true);
			selectElement.append(newOption).trigger('change');
		}
	},
	MultiImage: {
		currentFileUploads: 0,
		register(container) {
			$('.js-multi-image', container).each(function () {
				new MultiImage($(this));
			});
		}
	},
	MultiEmail: {
		register($container) {
			$('.js-multi-email', $container).each((idx, multiEmailField) => {
				let $multiEmailField = $(multiEmailField);
				$multiEmailField.on('change', '.js-multi-email', (e) => {
					App.Fields.MultiEmail.parseToJSON($multiEmailField);
				});
				$multiEmailField.on('click', '.js-multi-email-consenticon', (e) => {
					App.Fields.MultiEmail.toggleConsent($(e.target));
					App.Fields.MultiEmail.parseToJSON($multiEmailField);
				});
				$multiEmailField.on('click', '.js-multi-email-add', (e) => {
					App.Fields.MultiEmail.addItem($multiEmailField);
				});
				$multiEmailField.on('click', '.js-multi-email-remove', (e) => {
					App.Fields.MultiEmail.removeItem($(e.target));
					App.Fields.MultiEmail.parseToJSON($multiEmailField);
				});
			});
		},
		/**
		 * Converts data to json and set MultiEmail field value
		 * @param $multiEmailField
		 */
		parseToJSON($multiEmailField) {
			let value = [];
			$('.js-multi-email-item', $multiEmailField).each((idx, item) => {
				let $item = $(item);
				let email = $('.js-multi-email', $item).val();
				let consent = $('.js-multi-email-consent', $item).is(':visible') ? 1 : 0;
				if (email) {
					value.push({
						e: email,
						o: consent
					});
				}
			});
			$('.js-multi-email-value', $multiEmailField).val(JSON.stringify(value));
		},
		/**
		 * Adds a new item: email box and consent checkbox
		 * @param $multiEmailField
		 */
		addItem($multiEmailField) {
			let $newItem = $('.js-multi-email-item', $multiEmailField).first().clone(false, false);
			if ($newItem) {
				$('.js-multi-email', $newItem).attr('value', '').val('');
				$('.js-multi-email-consent', $newItem).val('');
				$('.js-multi-email-consenticon', $newItem).hide();
				$('.js-multi-email-consenticon', $newItem).first().show();
				$('.js-multi-email-items', $multiEmailField).append($newItem);
				$('.js-multi-email-remove', $multiEmailField).show();
			}
		},
		/**
		 * Removes an item: email box and consent checkbox
		 * @param $deleteBtn
		 */
		removeItem($deleteBtn) {
			let $multiEmailField = $deleteBtn.closest('.js-multi-email');
			if (1 < $('.js-multi-email-item', $multiEmailField).length) {
				$deleteBtn.closest('.js-multi-email-item').remove();
			}
			if (1 == $('.js-multi-email-item', $multiEmailField).length) {
				$('.js-multi-email-remove', $multiEmailField).hide();
			}
		},
		/**
		 * Toggle consent boxes
		 * @param $consentBox
		 */
		toggleConsent($consentBox) {
			let $item = $consentBox.closest('.js-multi-email-item');
			$('.js-multi-email-consenticon', $item).toggle();
		}
	},
	MultiDependField: {
		/**
		 * Register function
		 * @param {jQuery} container
		 */
		register(container) {
			container.find('.js-multi-field').each((index, element) => {
				const inputElement = $(element);
				const fields = inputElement.find('.js-multi-field-val').data('fields');
				inputElement.find('.js-multi-field-add-item').on('click', (e) => {
					App.Fields.MultiDependField.addRow(inputElement, fields);
				});
				App.Fields.MultiDependField.registerRow(inputElement, fields);
			});
		},
		/**
		 * Register row
		 * @param {jQuery} inputElement
		 * @param {Object} fields
		 */
		registerRow(inputElement, fields) {
			for (let i in fields) {
				inputElement.find('[name="' + fields[i] + '"]').on('change', (e) => {
					App.Fields.MultiDependField.parseToJson(inputElement, fields);
				});
			}
			inputElement.find('.js-remove-item').on('click', (e) => {
				App.Fields.MultiDependField.removeRow($(e.target), inputElement);
				App.Fields.MultiDependField.parseToJson(inputElement.closest('.js-multi-field'), fields);
			});
		},
		/**
		 * Invoked after clicking the remove button
		 * @param {jQuery} element
		 * @param {jQuery} container
		 */
		removeRow(element, container) {
			if (container.find('.js-multi-field-row').length > 1) {
				element.closest('.js-multi-field-row').remove();
			}
		},
		/**
		 * Convert data to json
		 * @param {jQuery} element
		 * @param {Object} fields
		 */
		parseToJson(element, fields) {
			let arr = [];
			let allFields = $(element).find('.js-multi-field-row');
			let arrayLength = allFields.length;
			for (let i = 0; i < arrayLength; ++i) {
				let partData = {},
					skip = false;
				for (let k in fields) {
					partData[fields[k]] = $(allFields[i])
						.find('[name="' + fields[k] + '"]')
						.val();
					if (k == 0 && partData[fields[k]] === '') {
						skip = true;
						break;
					}
				}
				if (!skip) {
					arr.push(partData);
				}
			}
			$(element).find('input.js-multi-field-val').val(JSON.stringify(arr));
		},
		/**
		 * Invoked after clicking the add button
		 * @param {jQuery} container
		 * @param {Object} fields
		 */
		addRow(container, fields) {
			let newField;
			let lastField = container.find('.js-multi-field-row').last();
			let selectFields = lastField.find('select.select2');
			if (selectFields.length) {
				selectFields.select2('destroy').removeAttr('data-select2-id').find('option').removeAttr('data-select2-id');
				newField = lastField.clone(false, false);
				App.Fields.Picklist.showSelect2ElementView(lastField.find('select.select2'));
			} else {
				newField = lastField.clone(false, false);
			}
			for (let i in fields) {
				newField.find('[name="' + fields[i] + '"]').val('');
			}
			newField.insertAfter(container.find('.js-multi-field-row').last());
			App.Fields.Picklist.showSelect2ElementView(newField.find('select.select2'));
			App.Fields.Date.register(newField);
			App.Fields.MultiDependField.registerRow(container, fields);
		}
	},
	DependentSelect: {
		/**
		 * Get options for select from array of items (exclude children)
		 * @param {Array} data {value,text,selected, children => data[]}
		 * @returns {string}
		 */
		generateOptionsFromData(data) {
			let html = '';
			for (let item of data) {
				let selected = false;
				if (typeof item.selected !== 'undefined' && item.selected) {
					selected = true;
				}
				html += `<option value=${item.value}${selected ? ' selected' : ''}>${item.text}</option>`;
			}
			return html;
		},
		/**
		 * Register dependent selects
		 *
		 * @param {jQuery} container with data- options:
		 * data-slave: selector for slave element
		 * data-data: array of options with children elements for slave select (see getOptions for data format)
		 * data-sort: do we want to sort slave options by text when master has two items selected? if not - just append options to slave
		 */
		register(container) {
			if (typeof container === 'undefined' || typeof container.length === 'undefined' || !container.length) {
				return app.errorLog('Dependend select field container is missing.');
			}
			container.each(function () {
				const masterSelect = $(this),
					slaveSelect = $(masterSelect.data('slave')),
					data = masterSelect.data('data');
				if (!slaveSelect.length) {
					return app.errorLog('Could not find slave select element (data-slave attribute)');
				}
				if (!data) {
					return app.errorLog('Could not load data (data-data attribute)');
				}
				masterSelect.on('change', (e) => {
					let values = $(e.target).val();
					if (!Array.isArray(values)) {
						values = [values];
					}
					let children = [];
					for (let value of values) {
						for (let item of data) {
							if (item.value === value) {
								if (typeof item.children !== 'undefined') {
									item.children.forEach((child) => {
										children.push(child);
									});
								}
							}
						}
					}
					if (masterSelect.data('sort')) {
						children.sort((a, b) => {
							return a.text.localeCompare(b.text);
						});
					}
					slaveSelect.html(App.Fields.DependentSelect.generateOptionsFromData(children));
				});
				masterSelect.html(App.Fields.DependentSelect.generateOptionsFromData(data));
			});
		}
	},
	Gantt: {
		register(container, data) {
			return new GanttField(container, data);
		}
	},
	Integer: {
		/**
		 * Function returns the integer in user specified format.
		 * @param {number} value
		 * @param {int} numberOfDecimal
		 * @returns {string}
		 */
		formatToDisplay(value) {
			if (!value) {
				value = 0;
			}
			let groupSeparator = CONFIG.currencyGroupingSeparator;
			let groupingPattern = CONFIG.currencyGroupingPattern;
			value = parseFloat(value).toFixed(1);
			let integer = value.toString().split('.')[0];
			if (integer.length > 3) {
				if (groupingPattern === '123,456,789') {
					integer = integer.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1' + groupSeparator);
				} else if (groupingPattern === '123456,789') {
					integer = integer.slice(0, -3) + groupSeparator + integer.slice(-3);
				} else if (groupingPattern === '12,34,56,789') {
					integer =
						integer.slice(0, -3).replace(/(\d)(?=(\d\d)+(?!\d))/g, '$1' + groupSeparator) +
						groupSeparator +
						integer.slice(-3);
				}
			}
			return integer;
		}
	},
	Double: {
		/**
		 * Function returns the currency in user specified format.
		 * @param {number} value
		 * @param {boolean} numberOfDecimal
		 * @param {int} numberOfDecimal
		 * @returns {string}
		 */
		formatToDisplay(value, fixed = true, numberOfDecimal = CONFIG.noOfCurrencyDecimals) {
			if (!value) {
				value = 0;
			}
			let strDecimal = value.toString().split('.')[1];
			let numberOfZerosAtTheEnd = 0;
			if (typeof strDecimal !== 'undefined') {
				for (let i = strDecimal.length - 1; i > 0; --i) {
					if (strDecimal[i] == '0') {
						numberOfZerosAtTheEnd++;
					} else {
						break;
					}
				}
			}
			value = parseFloat(value);
			if (fixed) {
				let base = 10 ** numberOfDecimal;
				value =
					Math.round(
						value * base + Math.sign(value) * 0.1 ** (17 - 2 - (Math.round(value * base) / base).toString().length)
					) / base;
			}
			let splittedFloat = value.toString().split('.');
			let integer = splittedFloat[0];
			if (integer !== '-0' && integer !== '0') {
				integer = App.Fields.Integer.formatToDisplay(integer);
			}
			let decimal = splittedFloat[1];
			if (numberOfDecimal) {
				if (!CONFIG.truncateTrailingZeros && decimal) {
					for (let i = 0; i < numberOfZerosAtTheEnd && decimal.length < numberOfDecimal; ++i) {
						decimal += '0';
					}
				}
				if (decimal) {
					return integer + CONFIG.currencyDecimalSeparator + decimal;
				}
			}
			return integer;
		},
		/**
		 * Function to get value for db format.
		 * @param {string} value
		 * @returns {number}
		 */
		formatToDb(value) {
			if (value == undefined || value == '') {
				value = 0;
			}
			value = value.toString();
			value = value.split(CONFIG.currencyGroupingSeparator).join('');
			value = value.replace(/\s/g, '').replace(CONFIG.currencyDecimalSeparator, '.');
			return parseFloat(value);
		}
	},
	/**
	 * Tree
	 */
	Tree: class Tree {
		constructor(container) {
			this.container = container;
			this.init();
		}
		/**
		 * Register function
		 * @param {jQuery} container
		 */
		static register(container) {
			if (container.hasClass('js-tree-container')) {
				return new Tree(container);
			}
			const instances = [];
			container.find('.js-tree-container').each((n, e) => {
				instances.push(new Tree($(e)));
			});
			return instances;
		}
		/**
		 * Initiation
		 */
		init() {
			this.modalEvent();
			this.autoCompleteEvent();
			this.clearSelectionEvent();
		}
		/**
		 * Function which will handle modal view with tree
		 */
		modalEvent() {
			$('.js-tree-modal', this.container)
				.off('click')
				.on('click', (_) => {
					let sourceFieldElement = this.container.find('input.sourceField'),
						fieldDisplayElement = this.container.find('input[name="' + sourceFieldElement.attr('name') + '_display"]');
					AppConnector.request({
						module: sourceFieldElement.data('module-name'),
						view: 'TreeModal',
						template: sourceFieldElement.data('treetemplate'),
						fieldName: sourceFieldElement.attr('name'),
						multiple: sourceFieldElement.data('multiple'),
						value: sourceFieldElement.val()
					}).done(function (requestData) {
						app.modalEvents['treeModal'] = function (modal, instance) {
							instance.setSelectEvent((responseData) => {
								sourceFieldElement.val(responseData.id);
								fieldDisplayElement.val(responseData.name).attr('readonly', true);
								sourceFieldElement.trigger('change');
							});
						};
						app.showModalWindow(requestData, { modalId: 'treeModal' });
					});
				});
		}
		/**
		 * Function which will handle the reference auto complete event registrations
		 */
		autoCompleteEvent() {
			let autoCompleteElement = $('input.treeAutoComplete', this.container);
			if (autoCompleteElement.hasClass('ui-autocomplete-input')) {
				autoCompleteElement.autocomplete('destroy');
			}
			autoCompleteElement.autocomplete({
				delay: '600',
				minLength: '3',
				source: function (request, response) {
					let inputElement = $(this.element[0]);
					let searchValue = request.term.toLowerCase();
					let parentElem = inputElement.closest('.js-tree-container');
					let sourceFieldElement = $('input.sourceField', parentElem);
					let fieldInfo = sourceFieldElement.data('fieldinfo');
					let allValues = fieldInfo.picklistvalues;
					let responseDataList = [];
					for (let id in allValues) {
						if (allValues[id].toLowerCase().indexOf(searchValue) >= 0) {
							responseDataList.push({ label: allValues[id], value: id, id: id });
						}
					}
					if (responseDataList.length <= 0) {
						$(inputElement).val('');
						responseDataList.push({
							label: app.vtranslate('JS_NO_RESULTS_FOUND'),
							type: 'no results'
						});
					}
					response(responseDataList);
				},
				select: function (event, ui) {
					let selectedItemData = ui.item;
					if (typeof selectedItemData.type !== 'undefined' && selectedItemData.type == 'no results') {
						return false;
					}
					selectedItemData.name = selectedItemData.value;
					this.value = selectedItemData.label;
					let element = $(this).attr('readonly', true);
					element.closest('.js-tree-container').find('input.sourceField').val(selectedItemData.id).trigger('change');
					return false;
				},
				change: function (event, ui) {},
				open: function (event, ui) {
					//To Make the menu come up in the case of quick create
					$(this).data('ui-autocomplete').menu.element.css('z-index', '100001');
				}
			});
		}
		/**
		 * Function which will register reference field clear event
		 */
		clearSelectionEvent() {
			$('.clearTreeSelection', this.container)
				.off('click')
				.on('click', (e) => {
					let fieldElement = this.container.find('.sourceField');
					$('input[name="' + fieldElement.attr('name') + '_display"]', this.container)
						.removeAttr('readonly')
						.val('');
					fieldElement.val('').trigger('change');
					e.preventDefault();
				});
		}
	},
	/**
	 * TimePeriod class
	 *
	 * Save value as time period in 00:m format where '0' is a number of units
	 * ':' is just separator
	 * and 'm' is time scale/period in php date format - available formats are [m, d, H, i, s]
	 * @example 10:i = 10 minutes, 2:m = 2 months, 20:H = 20 hours and so on...
	 */
	TimePeriod: class TimePeriod {
		constructor(container) {
			this.container = container;
			this.value = container.val();
			if (this.value) {
				const split = this.value.split(':');
				this.time = Number(split[0]);
				this.period = split[1];
			} else {
				this.time = 0;
				this.period = 'H';
				this.value = '0:H';
				container.val(this.value);
			}
			this.injectContent();
		}

		/**
		 * Register time period field/s
		 *
		 * @param {jQuery} container it could be input type hidden with js-time-period class
		 *                           or container that contains multiple js-time-period inputs
		 *
		 * @example <input type="hidden" name="field_name" class="js-time-period">
		 *
		 * @returns {TimePeriod|TimePeriod[]} instance/s
		 */
		static register(container) {
			if (container.hasClass('c-time-period')) {
				return new TimePeriod(container);
			}
			const instances = [];
			container.find('.c-time-period').each((index, value) => {
				instances.push(new TimePeriod($(value)));
			});
			return instances;
		}

		/**
		 * Inject content next to container
		 *
		 * @returns  {jQuery}  created element with input and select
		 */
		injectContent() {
			let content = `<div class="input-group c-time-period" data-js="container">
				<div class="input-group-prepend">
					<a href class="btn btn-default c-time-period-input-modifier c-time-period-input-modifier--minus-1"><span class="fas fa-minus"></span></a>
				</div>
				<input type="number" class="form-control c-time-period-input" min="0" value="${this.time}"
					data-validation-engine="validate[required,funcCall[Vtiger_Integer_Validator_Js.invokeValidation]]">
				<div class="input-group-append">
					<a href class="btn btn-default c-time-period-input-modifier c-time-period-input-modifier--plus-1"><span class="fas fa-plus"></span></a>
					<select class="select2 js-time-period-select time-period-${this.container.attr('name')}">
						<option value="d"${this.period === 'd' ? ' selected="selected"' : ''}>${app.vtranslate('JS_DAYS_FULL')}</option>
						<option value="H"${this.period === 'H' ? ' selected="selected"' : ''}>${app.vtranslate('JS_HOURS_FULL')}</option>
						<option value="i"${this.period === 'i' ? ' selected="selected"' : ''}>${app.vtranslate('JS_MINUTES_FULL')}</option>
					</select>
				</div>
			</div>`;
			this.element = this.container.parent().append(content);
			this.input = this.element.find('.c-time-period-input').eq(0);
			this.select = this.element.find('.select2').eq(0);
			this.plus1btn = this.element.find('.c-time-period-input-modifier--plus-1').eq(0);
			this.minus1btn = this.element.find('.c-time-period-input-modifier--minus-1').eq(0);
			App.Fields.Picklist.showSelect2ElementView(this.select, { width: '100px' });
			this.registerEvents();
			return this.element;
		}

		/**
		 * Register events
		 */
		registerEvents() {
			this.input.on('input', this.onChange.bind(this));
			this.select.on('change', this.onChange.bind(this));
			this.plus1btn.on('click', this.onPlus1Click.bind(this));
			this.minus1btn.on('click', this.onMinus1Click.bind(this));
		}

		/**
		 * On change event
		 *
		 * @param {Event} event
		 */
		onChange(event) {
			this.time = this.input.val();
			this.period = this.select.val();
			this.value = this.input.val() + ':' + this.select.val();
			this.container.val(this.value);
		}

		/**
		 * Plus 1 button click event handler
		 *
		 * @param {Event} event
		 */
		onPlus1Click(event) {
			event.preventDefault();
			event.stopPropagation();
			this.input.val(Number(this.input.val()) + 1);
			this.onChange();
		}

		/**
		 * Minus 1 button click event handler
		 *
		 * @param {Event} event
		 */
		onMinus1Click(event) {
			event.preventDefault();
			event.stopPropagation();
			if (Number(this.input.val()) > 0) {
				this.input.val(Number(this.input.val()) - 1);
				this.onChange();
			}
		}
	},
	/**
	 * Multi currency
	 */
	MultiCurrency: class MultiCurrency {
		constructor(container) {
			this.container = container;
			this.init();
		}
		/**
		 * Register function
		 * @param {jQuery} container
		 */
		static register(container) {
			if (container.hasClass('js-multicurrency-container')) {
				return new MultiCurrency(container);
			}
			const instances = [];
			container.find('.js-multicurrency-container').each((n, e) => {
				instances.push(new MultiCurrency($(e)));
			});
			return instances;
		}
		/**
		 * Initiation
		 */
		init() {
			$('.js-multicurrency-event', this.container)
				.off('click')
				.on('click', () => {
					let modal = $('<form>').append(this.container.find('.js-currencies-container .js-currencies-modal').clone());
					this.registerEnableCurrencyEvent(modal);
					this.registerResetCurrencyEvent(modal);
					this.loadData(modal);
					this.calculateConversionRate(modal);
					app.showModalWindow({
						data: modal,
						css: {},
						cb: (data) => {
							let form = data.parent();
							form.validationEngine(app.validationEngineOptionsForRecord);
							form.on('submit', (e) => {
								e.preventDefault();
								if (form.validationEngine('validate') && this.saveCurrencies(form)) {
									let id = form.closest('.js-modal-container').attr('id');
									app.hideModalWindow(null, id);
								}
							});
						}
					});
				});
			this.getField().on('focusout', (e) => {
				let element = $(e.currentTarget);
				element.formatNumber();
				this.setPrice(element.val());
			});
		}
		/**
		 * Loading data
		 * @param {jQuery} modalContainer
		 */
		loadData(modalContainer) {
			let values = JSON.parse(this.getFieldToSave().val());
			let baseCurrencyId = values['currencyId'] || CONFIG.currencyId;
			if (values['currencies'] === undefined) {
				values['currencies'] = [];
				values['currencies'][baseCurrencyId] = { price: 0 };
			}
			for (let i in values['currencies']) {
				let row = modalContainer.find('[data-currency-id="' + i + '"]');
				if (row.length) {
					row.find('.js-enable-currency').prop('checked', true);
					row.find('.js-currency-reset,.js-base-currency,[name^="currencies["]').prop('disabled', false);
					row.find('.js-converted-price').val(values['currencies'][i]['price']);
					if (i == baseCurrencyId) {
						row.find('.js-base-currency').prop('checked', true);
					}
				}
			}
		}
		/**
		 * Set value
		 * @param {number} value
		 */
		setPrice(value) {
			let values = JSON.parse(this.getFieldToSave().val());
			let baseCurrencyId = values['currencyId'] || CONFIG.currencyId;
			values['currencies'] = values['currencies'] || {};
			values['currencies'][baseCurrencyId] = { price: value };
			values['currencyId'] = baseCurrencyId;
			values = $.extend({}, values);
			this.getFieldToSave().val(JSON.stringify($.extend({}, values)));
		}
		/**
		 * Gets field
		 */
		getField() {
			return this.container.find('.js-multicurrency-field');
		}
		/**
		 * Gets field to save
		 */
		getFieldToSave() {
			return this.container.find('.js-multicurrency-field-to-save');
		}
		/**
		 * Save
		 * @param {jQuery} modalContainer
		 */
		saveCurrencies(modalContainer) {
			let enabledBaseCurrency = modalContainer.find('.js-enable-currency').filter(':checked');
			if (enabledBaseCurrency.length < 1) {
				Vtiger_Helper_Js.showMessage({
					text: app.vtranslate('JS_PLEASE_SELECT_BASE_CURRENCY_FOR_PRODUCT'),
					type: 'error'
				});
				return false;
			}
			let selectedBaseCurrency = modalContainer.find('.js-base-currency').filter(':checked');
			if (selectedBaseCurrency.length < 1) {
				Vtiger_Helper_Js.showMessage({
					text: app.vtranslate('JS_PLEASE_ENABLE_BASE_CURRENCY_FOR_PRODUCT'),
					type: 'error'
				});
				return false;
			}

			let selectedRow = selectedBaseCurrency.closest('tr');
			let symbol = selectedRow.data('currency-symbol');
			this.container.find('.js-currency').text(symbol);
			let data = {};
			data['currencies'] = {};
			enabledBaseCurrency.closest('tr').each((n, e) => {
				let row = $(e),
					currencyId = row.data('currencyId');
				data['currencies'][currencyId] = {};
				data['currencies'][currencyId]['price'] = row.find('.js-converted-price').val();
				if (row.find('.js-base-currency:checked').length) {
					data['currencyId'] = currencyId;
				}
			});
			this.getFieldToSave().val(JSON.stringify(data));
			this.getField().val(selectedRow.find('.js-converted-price').val());
			selectedBaseCurrency.prop('checked', false);
			return true;
		}
		/**
		 * Calculate
		 * @param {jQuery} container
		 */
		calculateConversionRate(container) {
			let baseCurrencyConversionRate = container
				.find('.js-base-currency')
				.filter(':checked')
				.closest('tr')
				.find('.js-conversion-rate');
			if (baseCurrencyConversionRate.val() == '1') {
				return;
			}
			let baseCurrencyRatePrevValue = baseCurrencyConversionRate.getNumberFromValue();
			container.find('.js-conversion-rate').each(function (key, domElement) {
				let element = $(domElement);
				if (!element.is(baseCurrencyConversionRate)) {
					element.val(
						App.Fields.Double.formatToDisplay(element.getNumberFromValue() / baseCurrencyRatePrevValue, false)
					);
				}
			});
			baseCurrencyConversionRate.val('1');
		}
		/**
		 * Function to register event for enabling currency on checkbox checked
		 * @param {jQuery} container
		 */
		registerEnableCurrencyEvent(container) {
			container.on('change', '.js-enable-currency', (e) => {
				let element = $(e.currentTarget);
				let parentRow = element.closest('tr');
				if (element.is(':checked')) {
					element.attr('checked', 'checked');
					let price = this.getField().getNumberFromValue() * parentRow.find('.js-conversion-rate').getNumberFromValue();
					$('input', parentRow).removeAttr('disabled');
					parentRow.find('.js-currency-reset').removeAttr('disabled');
					parentRow.find('.js-converted-price').val(App.Fields.Double.formatToDisplay(price));
				} else {
					if (parentRow.find('.js-base-currency').is(':checked')) {
						app.showNotify({
							type: 'error',
							title:
								'"' +
								parentRow.find('.js-currency-name').text() +
								'" ' +
								app.vtranslate('JS_BASE_CURRENCY_CHANGED_TO_DISABLE_CURRENCY')
						});
						element.prop('checked', true);
						return;
					}
					parentRow.find('input').attr('disabled', 'disabled');
					parentRow.find('.js-currency-reset').attr('disabled', 'disabled');
					element.removeAttr('disabled checked');
				}
			});
		}

		/**
		 * Function to register event for reseting the currencies
		 * @param {jQuery} container
		 */
		registerResetCurrencyEvent(container) {
			container.on('click', '.js-currency-reset', (e) => {
				let parentElem = $(e.currentTarget).closest('tr');
				let price = this.getField().getNumberFromValue() * parentElem.find('.js-conversion-rate').getNumberFromValue();
				$('.js-converted-price', parentElem).val(App.Fields.Double.formatToDisplay(price));
			});
		}
	},
	/**
	 * Meeting URL
	 */
	MeetingUrl: class MeetingUrl {
		constructor(container) {
			this.container = container;
			this.init();
		}
		/**
		 * Register function
		 * @param {jQuery} container
		 */
		static register(container) {
			if (container.hasClass('js-meeting-container')) {
				return new MeetingUrl(container);
			}
			const instances = [];
			container.find('.js-meeting-container').each((n, e) => {
				instances.push(new MeetingUrl($(e)));
			});
			return instances;
		}
		/**
		 * Initiation
		 */
		init() {
			let addButton = $('.js-meeting-add', this.container);
			if (!addButton.length) {
				return false;
			}

			let valElement = $('.js-meeting-val', this.container);
			addButton.off('click').on('click', (e) => {
				let progressIndicatorElement = $.progressIndicator({ blockInfo: { enabled: true } });
				AppConnector.request(this.getUrl(e))
					.done((data) => {
						let result = data.result;
						if (result && result.success && result.url) {
							valElement.attr('readonly', true).val(result.url);
						} else {
							app.showNotify({
								text: app.vtranslate('JS_ERROR'),
								type: 'error'
							});
						}
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
					})
					.fail((_) => {
						app.showNotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
					});
			});
			$('.js-meeting-clear', this.container)
				.off('click')
				.on('click', () => {
					valElement.attr('readonly', false).val('');
				});
			this.addEventsForDependentFields();
		}
		/**
		 * Gets URL
		 */
		getUrl(e) {
			let url = e.currentTarget.dataset.url;
			let formData = $(e.currentTarget).closest('form').serializeFormData();
			let expField = e.currentTarget.dataset.expField;
			if (expField && formData && formData[expField]) {
				let date = formData[expField].split(' ');
				url += '&exp=' + encodeURIComponent(date[0]);
			}
			let roomName = e.currentTarget.dataset.roomName;
			if (roomName && formData && formData[roomName]) {
				url += '&roomName=' + encodeURIComponent(formData[roomName]);
			}
			return url;
		}
		/**
		 * Add events for dependent fields
		 */
		addEventsForDependentFields() {
			let addButton = $('.js-meeting-add', this.container);
			let valElement = $('.js-meeting-val', this.container);
			let data = addButton.data();
			let formElement = this.container.closest('form');
			for (let name of ['expField', 'roomName']) {
				let fieldName = data[name];
				if (!fieldName) {
					continue;
				}
				formElement.on('change', `[name=${fieldName}]`, (_) => {
					if (data['domain'] && valElement.val().indexOf(data['domain']) === 0) {
						addButton.trigger('click');
						app.showNotify({
							type: 'info',
							text: app.vtranslate('JS_MEETING_URL_CHANGED')
						});
					}
				});
			}
		}
	},
	/**
	 * Changes Json
	 */
	ChangesJson: class ChangesJson {
		constructor(container) {
			this.container = container;
			this.init();
		}
		/**
		 * Register function
		 * @param {jQuery} container
		 */
		static register(container) {
			if (container.hasClass('js-changesjson-container')) {
				return new ChangesJson(container);
			}
			const instances = [];
			container.find('.js-changesjson-container').each((_, e) => {
				instances.push(new ChangesJson($(e)));
			});
			return instances;
		}
		/**
		 * Initiation
		 */
		init() {
			$('.js-changesjson-edit', this.container)
				.off('click')
				.on('click', () => {
					let field = this.getField();
					let value = field.val() ? JSON.parse(field.val()) : { record: 0, module: '', changes: [] };
					let relatedField = this.getRelatedField();
					if (relatedField.length) {
						value.record = relatedField.val();
						value.module = $('input[name="popupReferenceModule"]', relatedField.closest('.fieldValue')).val();
					}
					if (!value.record || value.record == 0) {
						app.showNotify({ text: app.vtranslate('JS_LACK_INFORMATION_ABOUT_RECORD') });
						return false;
					}
					let progressIndicatorElement = $.progressIndicator({ blockInfo: { enabled: true } });
					AppConnector.request({
						module: value.module,
						record: value.record,
						changes: value.changes,
						sourceModule: field.data('module'),
						sourceField: field.attr('name'),
						view: 'ChangesJsonModal'
					})
						.done((requestData) => {
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
							app.showModalWindow({
								data: requestData,
								css: {},
								cb: (data) => {
									this.saveData(data, value);
								}
							});
						})
						.fail((_) => {
							app.showNotify({
								text: app.vtranslate('JS_ERROR'),
								type: 'error'
							});
							progressIndicatorElement.progressIndicator({ mode: 'hide' });
						});
				});
		}
		/**
		 * Save data to field
		 */
		saveData(container, data) {
			let form = container.find('form');
			container.on('click', '.js-modal__save', (e) => {
				if (form.validationEngine('validate')) {
					e.preventDefault();
					if (!form.find('input[id^="selectRow"]:checked').length) {
						app.showNotify({
							text: app.vtranslate('JS_NONE_FIELD_MARKED_IN_MASS_EDIT'),
							type: 'error'
						});
						return;
					}
					let invalidFields = form.data('jqv').InvalidFields;
					if (invalidFields.length !== 0) {
						return;
					}
					form.find('[id^="selectRow"]').each(function (_, checkbox) {
						checkbox = $(checkbox);
						if (!checkbox.prop('checked')) {
							checkbox
								.closest('.js-form-row-container')
								.find('.fieldValue [name]')
								.each(function (_, element) {
									element = $(element);
									element.attr('data-element-name', element.attr('name')).removeAttr('name');
								});
						}
					});
					let changeData = form.serializeFormData();
					delete changeData['_csrf'];
					for (let fieldName in changeData) {
						if (fieldName.substr(-2) === '[]') {
							let fieldNameShort = fieldName.substr(0, fieldName.length - 2);
							changeData[fieldNameShort] = changeData[fieldName];
							delete changeData[fieldName];
						}
					}
					data.changes = changeData;
					this.getField().val(JSON.stringify(data));
					app.hideModalWindow(null, form.closest('.js-modal-container').attr('id'));
				}
			});
		}
		/**
		 * Gets field
		 */
		getField() {
			return this.container.find('.js-changesjson-value');
		}
		/**
		 * Gets related field
		 */
		getRelatedField() {
			let relatedFieldName = this.getField().data('related-field');
			return this.container.closest('form').find(`[name=${relatedFieldName}]`);
		}
	},
	/**
	 * MultiReference
	 */
	MultiReference: class MultiReference {
		constructor(container) {
			this.container = container;
			this.select = container.find('.js-multi-reference');
			this.form = container.closest('form');
			this.init();
		}
		/**
		 * Register function
		 * @param {jQuery} container
		 */
		static register(container) {
			if (container.hasClass('js-multiReference-container')) {
				return new MultiReference(container);
			}
			const instances = [];
			container.find('.js-multiReference-container').each((_, e) => {
				instances.push(new MultiReference($(e)));
			});
			return instances;
		}
		/**
		 * Set a value for the field
		 *
		 * @param   {jQuery}  field Field element
		 * @param   {mixed}  value The value to set
		 * @param   {object}  params Additional parameters [optional]
		 *
		 * @return  {mixed} The value that has been set
		 */
		static setValue(field, value, params) {
			if (!(params && params['extend'])) {
				field.val(null);
			}
			const values = field.val();
			$.each(value, (id, label) => {
				if (!values.includes(id)) {
					field.append(new Option(label, id, true, true));
				}
			});
			field.trigger('change');
			return field.val();
		}
		/**
		 * Initiation
		 */
		init() {
			$('.js-related-popup', this.container)
				.off('click')
				.on('click', () => {
					app.showRecordsList(this.getParams(), (_modal, instance) => {
						instance.setSelectEvent((data) => {
							this.setReferenceFieldValue(data);
						});
					});
				});
			$('.js-create-reference-record', this.container)
				.off('click')
				.on('click', () => {
					this.createHandler();
				});
			this.registerAutoComplete();
		}
		/**
		 * Function which will handle the reference auto complete event registrations
		 */
		registerAutoComplete() {
			App.Fields.Picklist.showSelect2ElementView(this.select, {
				ajax: {
					data: function (item) {
						return {
							search_value: item.term ?? '',
							page: item.page
						};
					},
					processResults: (data, params) => {
						let items = new Array();
						if (!params.term) {
							items.push({
								type: 'optgroup',
								name: this.select.attr('placeholder')
							});
						} else if (data.success) {
							$.each(data.result, (_, item) => {
								items.push({
									name: item.label,
									id: item.id
								});
							});
						}
						return {
							results: items,
							pagination: {
								more: false
							}
						};
					}
				}
			});
		}
		/**
		 * Set reference field value
		 */
		createHandler() {
			let formData = this.form.serializeFormData();
			delete formData['action'];
			App.Components.QuickCreate.createRecord($('.js-popup-reference-module', this.container).val(), {
				data: {
					sourceRecordData: formData
				},
				callbackFunction: (data) => {
					if (data.success) {
						this.select.append(new Option(data.result._recordLabel, data.result._recordId, true, true));
					}
				}
			});
		}
		/**
		 * Set reference field value
		 * @param {object} data
		 */
		setReferenceFieldValue(data) {
			const values = this.select.val();
			$.each(data, (id, label) => {
				if (!values.includes(id)) {
					this.select.append(new Option(label, id, true, true));
				}
			});
		}
		/**
		 * Gets params
		 * @returns {Object}
		 */
		getParams() {
			const referenceModule = $('.js-popup-reference-module', this.container).val(),
				sourceFieldName = this.select.attr('name').slice(0, -2),
				sourceRecordElement = $('input[name="record"]', this.form),
				listFilterFieldsJson = this.form.find('input[name="listFilterFields"]').val(),
				listFilterFields = listFilterFieldsJson ? JSON.parse(listFilterFieldsJson) : [];
			let sourceRecordId = '';
			if (sourceRecordElement.length > 0) {
				sourceRecordId = sourceRecordElement.val();
			}
			let filterFields = {};
			if (
				listFilterFields[sourceFieldName] != undefined &&
				listFilterFields[sourceFieldName][referenceModule] != undefined
			) {
				$.each(listFilterFields[sourceFieldName][referenceModule], (index) => {
					let mapFieldElement = this.form.find('[name="' + index + '"]');
					if (mapFieldElement.length && mapFieldElement.val() != '') {
						filterFields[index] = mapFieldElement.val();
					}
				});
			}
			return {
				module: referenceModule,
				src_module: $('input[name="module"]', this.form).val(),
				src_field: sourceFieldName,
				src_record: sourceRecordId,
				filterFields: filterFields,
				multi_select: true
			};
		}
	},
	/**
	 * Password
	 */
	Password: class Password {
		constructor(container) {
			this.container = container;
			this.init();
		}
		/**
		 * Register function
		 * @param {jQuery} container
		 */
		static register(container) {
			if (container.hasClass('js-pwd-container')) {
				return new Password(container);
			}
			const instances = [];
			container.find('.js-pwd-container').each((_, e) => {
				instances.push(new Password($(e)));
			});
			return instances;
		}
		/**
		 * Get strength meter
		 * @returns {Object}
		 */
		static getStrengthLevels() {
			if (!this.strengthLevels) {
				this.strengthLevels = {
					0: app.vtranslate('JS_PWD_RIDICULOUS'),
					1: app.vtranslate('JS_PWD_VERY_WEAK'),
					2: app.vtranslate('JS_PWD_WEAK'),
					3: app.vtranslate('JS_PWD_MEDIUM'),
					4: app.vtranslate('JS_PWD_STRONG'),
					5: app.vtranslate('JS_PWD_VERY_STRONG')
				};
			}
			return { ...this.strengthLevels };
		}
		/**
		 * Initiation
		 */
		init() {
			const field = this.getField();
			$('.js-pwd-auto-generate', this.container)
				.off('click')
				.on('click', (e) => {
					this.getResponse($(e.currentTarget).data('url')).then((response) => {
						if (response.success && response.result && response.result.pwd) {
							this.clear();
							field.val(response.result.pwd).trigger('keyup').focus();
						}
					});
				});
			$('.js-pwd-validate', this.container)
				.off('click')
				.on('click', (e) => {
					this.getResponse($(e.currentTarget).data('url') + '&password=' + field.val()).then((response) => {
						if (response.success && response.result) {
							let message = response.result.message;
							if (Array.isArray(message)) {
								message = message.join('<br>');
							}
							field.validationEngine('showPrompt', message, response.result.type, 'topLeft', true);
							field.validationEngine('updatePromptsPosition');
						}
					});
				});
			$('.js-pwd-clear', this.container)
				.off('click')
				.on('click', () => {
					this.clear();
				});
			$('.js-pwd-copy', this.container)
				.off('click')
				.on('click', () => {
					if (this.container.find('.js-pwd-show').attr('disabled') === 'disabled') {
						this.getPassword().then((response) => {
							this.clear();
							field.val(response.result.text);
							ClipboardJS.copy(response.result.text);
							app.showNotify({
								text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
								type: 'success'
							});
						});
					} else {
						ClipboardJS.copy(this.getField().val());
						app.showNotify({
							text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
							type: 'success'
						});
					}
				});
			$('.js-pwd-get', this.container)
				.off('click')
				.on('click', () => {
					this.getPassword().then((response) => {
						this.clear();
						field.val(response.result.text);
					});
				});
			if (field.data('strengthMeter')) {
				field.off('keyup').on('keyup', (e) => {
					let score = this.strengthMeter(e.target.value || '');
					field
						.attr('data-original-title', App.Fields.Password.getStrengthLevels()[score])
						.tooltip('show')
						.validationEngine('hide');
				});
			}
		}
		/**
		 * Get decoded password
		 * @returns {Promise}
		 */
		getPassword() {
			const field = this.getField();
			return this.getResponse({
				module: field.data('module'),
				field: field.attr('name'),
				record: $('input[name="record"]', this.container.closest('form')).val() || app.getRecordId(),
				action: 'Password',
				mode: 'getPwd'
			});
		}
		/**
		 * Clear data
		 */
		clear() {
			this.getField().val('').attr('disabled', false).tooltip('dispose').validationEngine('hide');
			this.container.find('.js-pwd-validate, .js-pwd-show').attr('disabled', false);
		}
		/**
		 * Get response
		 * @param {Object|string} params
		 * @returns {Promise}
		 */
		getResponse(params) {
			const aDeferred = $.Deferred();
			let progressIndicatorElement = $.progressIndicator({ blockInfo: { enabled: true } });
			AppConnector.request(params)
				.done((response) => {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					if (response.success) {
						aDeferred.resolve(response);
					} else {
						aDeferred.reject(response);
					}
				})
				.fail((_) => {
					app.showNotify({
						text: app.vtranslate('JS_ERROR'),
						type: 'error'
					});
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					aDeferred.reject(_);
				});
			return aDeferred.promise();
		}
		/**
		 * Get strength meter score
		 * @param {string} pwd
		 * @returns {int}
		 */
		strengthMeter(pwd) {
			let score = 0;
			if (pwd.length > 6) score++;
			if (pwd.match(/[a-z]/) && pwd.match(/[A-Z]/)) score++;
			if (pwd.match(/\d+/)) score++;
			if (pwd.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/)) score++;
			if (pwd.length > 12) score++;

			return score;
		}
		/**
		 * Gets field
		 */
		getField() {
			return this.container.find('.js-pwd-field');
		}
	},
	/**
	 * Multi Attachment
	 */
	MultiAttachment: class MultiAttachment {
		/**
		 * Constructor
		 * @param {jQuery} container
		 * @param {Object} options
		 */
		constructor(container, options) {
			this.container = container;
			this.fileInput = container.find('.js-multi-attachment__file').eq(0);
			this.dataInput = container.find('.js-multi-attachment__values');
			this.form = container.closest('form');

			this.progressBar = container.find('.js-multi-attachment__progress-bar');
			this.progress = container.find('.js-multi-attachment__progress');
			this.result = container.find('.js-multi-attachment__result');
			this.files = this.dataInput.is('input') ? JSON.parse(this.dataInput.val()) : this.dataInput.data('value');

			let fieldInfo = this.dataInput.data('fieldinfo') || {};
			this.options = {
				formats: fieldInfo.formats || [],
				limit: fieldInfo.limit || 1,
				maxFileSize: fieldInfo.maxFileSize,
				maxFileSizeDisplay: fieldInfo.maxFileSizeDisplay || '',
				...options
			};
			if (this.form.length && this.fileInput.length) {
				this.initEditView();
			} else {
				this.initDetailView();
			}
		}
		/**
		 * Register function
		 * @param {jQuery} container
		 * @param {Object} options
		 */
		static register(container, options = {}) {
			if (container.hasClass('js-multi-attachment')) {
				return new MultiAttachment(container, options);
			}
			const instances = [];
			container.find('.js-multi-attachment').each((_, e) => {
				instances.push(new MultiAttachment($(e), options));
			});
			return instances;
		}
		/**
		 * Initiation for detail view
		 */
		initDetailView() {
			this.files.forEach((fileInfo) => {
				this.createItem(fileInfo);
			});
		}
		/**
		 * Initiation for edit view
		 */
		initEditView() {
			this.fileInput.detach();
			this.container.on('mouseup', this.openBrowser.bind(this));
			this.fileInput.fileupload({
				dataType: 'json',
				replaceFileInput: false,
				fileInput: this.fileInput,
				autoUpload: false,
				submit: this.submit.bind(this),
				add: this.add.bind(this),
				progressall: this.progressAll.bind(this),
				change: this.change.bind(this),
				drop: this.change.bind(this),
				dragover: this.dragOver.bind(this),
				fail: this.uploadError.bind(this),
				done: this.uploadSuccess.bind(this)
			});
			this.container.on('dragleave', this.dragLeave.bind(this));
			this.container.on('dragend', this.dragLeave.bind(this));
			this.fileInput.fileupload('option', 'dropZone', this.container);
			this.enableDragNDrop();
			this.form.on('submit', this.onFormSubmit.bind(this));
			this.deleteButtonActive = true;
			this.container.on('click', '.js-multi-attachment__file-buttons-delete', (e) => {
				e.preventDefault();
				this.deleteFile(e.currentTarget.dataset.key);
			});
			this.files.forEach((fileInfo) => {
				this.createItem(fileInfo);
			});
			this.filesActive = 0;
		}
		/**
		 * Add event handler from jQuery-file-upload
		 *
		 * @param {Event} e
		 * @param {Object} data
		 */
		add(e, data) {
			if (data.files.length > 0) {
				data.submit();
			}
		}
		/**
		 * Submit event handler from jQuery-file-upload
		 *
		 * @param {Event} e
		 * @param {Object} data
		 */
		submit(e, data) {
			this.filesActive++;
			this.progressInstance = $.progressIndicator({
				position: 'replace',
				blockInfo: {
					enabled: true,
					elementToBlock: this.container
				}
			});
		}
		/**
		 * Prevent form submission before file upload end
		 * @param e
		 */
		onFormSubmit(e) {
			if (this.filesActive) {
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();
				app.showAlert(app.vtranslate('JS_WAIT_FOR_FILE_UPLOAD'));
				return false;
			}
			return true;
		}
		/**
		 * Progressall event handler from jQuery-file-upload
		 *
		 * @param {Event} e
		 * @param {Object} data
		 */
		progressAll(e, data) {
			const progress = parseInt((data.loaded / data.total) * 100, 10);
			this.progressBar.css({ width: progress + '%' });
			if (progress === 100) {
				setTimeout(() => {
					this.progress.addClass('d-none');
					this.progressBar.css({ width: '0%' });
				}, 1000);
			} else {
				this.progress.removeClass('d-none');
			}
		}
		/**
		 * File change event handler from jQuery-file-upload
		 *
		 * @param {Event} e
		 * @param {object} data
		 */
		change(e, data) {
			let { valid, error } = this.filterFiles(data.files);
			data.files = valid;
			if (!valid.length) {
				this.fileInput.val('');
			}
			if (error.length) {
				this.showErrors(error);
			}
			this.dragLeave(e);
		}
		/**
		 * Get only valid files from list
		 *
		 * @param {Array} files
		 * @returns {Object}
		 */
		filterFiles(files) {
			let valid = [],
				error = [];
			if (files.length + this.files.length > this.options.limit) {
				error.push({ error: { text: `${app.vtranslate('JS_FILE_LIMIT')} [${this.options.limit}]` } });
			} else {
				for (let file of files) {
					this.validateFileType(file) && this.validateFileSize(file) ? valid.push(file) : error.push(file);
				}
			}
			return { valid, error };
		}
		/**
		 * Validate maximum file size
		 * @param {Object} file
		 * @returns {Boolean}
		 */
		validateFileSize(file) {
			let result = typeof file.size === 'number' && file.size < this.options.maxFileSize;
			if (!result) {
				file.error = {
					title: `${app.vtranslate('JS_UPLOADED_FILE_SIZE_EXCEEDS')} <br> [${this.options.maxFileSizeDisplay}]`,
					text: file.name
				};
			}
			return result;
		}
		/**
		 * Validate file
		 *
		 * @param {Object} file
		 * @returns {boolean}
		 */
		validateFileType(file) {
			let result =
				!this.options.formats.length ||
				this.options.formats.filter((format) => {
					return file.type === format || (format.slice(-2) === '/*' && file.type.indexOf(format.slice(0, -1)) === 0);
				}).length > 0;
			if (!result) {
				file.error = { title: app.vtranslate('JS_INVALID_FILE_TYPE'), text: file.name };
			}
			return result;
		}
		/**
		 * Show errors
		 */
		showErrors(errors = []) {
			for (let info of errors) {
				this.showError(info.error);
			}
		}
		/**
		 * Show error
		 */
		showError(error) {
			if (typeof error.type === 'undefined') {
				error.type = 'error';
			}
			error.textTrusted = false;
			app.showNotify(error);
		}
		/**
		 * Dragover event handler from jQuery-file-upload
		 *
		 * @param {Event} e
		 */
		dragOver(_e) {
			this.container.addClass('c-multi-image__drop-effect');
		}
		/**
		 * Dragleave event handler
		 * @param {Event} e
		 */
		dragLeave(_e) {
			this.container.removeClass('c-multi-image__drop-effect');
		}
		/**
		 * Error event handler from file upload request
		 *
		 * @param {Event} e
		 * @param {Object} data
		 */
		uploadError(_e, data) {
			this.progressInstance.progressIndicator({ mode: 'hide' });
			this.filesActive--;
			app.errorLog('File upload error.');
			const { jqXHR, files } = data;
			if (typeof jqXHR.responseJSON === 'undefined' || jqXHR.responseJSON === null) {
				return this.showError({
					title: app.vtranslate('JS_FILE_UPLOAD_ERROR'),
					type: 'error'
				});
			}
			files.forEach((file) => {
				this.showError({
					title: app.vtranslate('JS_FILE_UPLOAD_ERROR'),
					text: file.name,
					type: 'error'
				});
			});
			this.updateFormValues();
		}
		/**
		 * Success event handler from file upload request
		 *
		 * @param {Event} e
		 * @param {Object} data
		 */
		uploadSuccess(e, data) {
			this.progressInstance.progressIndicator({ mode: 'hide' });
			const { result } = data;
			const attach = result.result.attach;
			attach.forEach((fileAttach) => {
				this.filesActive--;
				if (typeof fileAttach.key === 'undefined') {
					return this.uploadError(e, data);
				}
				if (typeof fileAttach.info !== 'undefined' && fileAttach.info) {
					app.showNotify({
						type: 'notice',
						text: fileAttach.info
					});
				}
				this.files.push(fileAttach);
				const fileInfo = this.getFileInfo(fileAttach.key);
				this.createItem(fileInfo);
			});
			this.updateFormValues();
		}
		/**
		 * Get file information
		 *
		 * @param {String} key - file id
		 * @returns {Object}
		 */
		getFileInfo(key) {
			for (let i = 0, len = this.files.length; i < len; i++) {
				const file = this.files[i];
				if (file.key === key) {
					return file;
				}
			}
			app.errorLog(`File '${key}' not found.`);
			app.showNotify({
				text: app.vtranslate('JS_INVALID_FILE_HASH'),
				type: 'error'
			});
		}
		/**
		 * Generate preview of image as html string from existing values
		 * @param {Object} file
		 */
		createItem(file) {
			const item = document.createElement('fieldset');
			item.setAttribute('class', 'c-multi-attachment--file bg-light js-handle');
			item.setAttribute('data-key', file.key);

			const legend = document.createElement('legend');
			legend.appendChild(document.createTextNode(file.name));
			item.appendChild(legend);

			const icon = document.createElement('div');
			icon.setAttribute('class', 'c-multi-attachment--file-icon');
			const span = document.createElement('span');
			span.setAttribute('class', file.icon);
			icon.appendChild(span);
			item.appendChild(icon);

			const fileInfo = document.createElement('div');
			fileInfo.setAttribute('class', 'c-multi-attachment--file-info');
			const name = document.createElement('span');
			name.setAttribute('class', 'c-multi-attachment--file-info-main');
			name.setAttribute('aria-hidden', true);
			name.appendChild(document.createTextNode(file.name));
			fileInfo.appendChild(name);
			const size = document.createElement('span');
			size.setAttribute('class', 'c-multi-attachment--file-info-sub');
			size.appendChild(document.createTextNode(file.sizeDisplay));
			fileInfo.appendChild(size);
			item.appendChild(fileInfo);

			const buttons = document.createElement('div');
			buttons.setAttribute('class', 'js-multi-attachment__file-buttons');

			if (file.url) {
				const downloadBtn = document.createElement('a');
				downloadBtn.setAttribute('class', 'btn btn-sm btn-outline-success js-multi-attachment__file-buttons-download');
				downloadBtn.setAttribute('href', file.url);
				downloadBtn.setAttribute('download', file.name);
				downloadBtn.setAttribute('title', $('<textarea />').html(app.vtranslate('JS_DOWNLOAD')).text());
				const downloadBtnIcon = document.createElement('span');
				downloadBtnIcon.setAttribute('class', 'fa fa-download');
				downloadBtn.appendChild(downloadBtnIcon);
				buttons.appendChild(downloadBtn);
			}
			if (this.deleteButtonActive && !file.lock) {
				const deleteBtn = document.createElement('button');
				deleteBtn.setAttribute('class', 'btn btn-sm btn-outline-danger js-multi-attachment__file-buttons-delete ml-1');
				deleteBtn.setAttribute('data-key', file.key);
				deleteBtn.setAttribute('title', $('<textarea />').html(app.vtranslate('JS_DELETE')).text());
				const deleteBtnIcon = document.createElement('span');
				deleteBtnIcon.setAttribute('class', 'fa fa-trash-alt');
				deleteBtn.appendChild(deleteBtnIcon);
				buttons.appendChild(deleteBtn);
			}

			item.appendChild(buttons);
			this.result.append(item);
		}
		/**
		 * Enable drag and drop files repositioning
		 */
		enableDragNDrop() {
			this.result
				.sortable({
					containment: this.container,
					items: '.js-handle',
					stop: this.sortStop.bind(this)
				})
				.disableSelection();
		}
		/**
		 * Prevent form submission
		 *
		 * @param {Event} e
		 */
		openBrowser(e) {
			if (!e.target.closest('fieldset')) {
				e.preventDefault();
				this.fileInput.trigger('click');
			}
		}

		/**
		 * Update file position according to elements order
		 *
		 * @param {Event} e
		 * @param {Object} ui
		 */
		sortStop(e, ui) {
			const actualElements = this.result.find('fieldset').toArray();
			this.files = actualElements.map((element) => {
				for (let i = 0, len = this.files.length; i < len; i++) {
					const elementHash = $(element).data('key');
					if (this.files[i].key === elementHash) {
						return this.files[i];
					}
				}
			});
			this.updateFormValues();
		}
		/**
		 * Remove file from preview and from file list
		 *
		 * @param {String} key
		 */
		deleteFile(key) {
			const fileInfo = this.getFileInfo(key);
			this.result.find(`[data-key="${fileInfo.key}"]`).remove();
			this.files = this.files.filter((file) => file.key !== fileInfo.key);
			this.updateFormValues();
		}

		/**
		 * Update form input values
		 */
		updateFormValues() {
			this.fileInput.val('');
			const formValues = this.files.map((file) => {
				return { key: file.key, name: file.name, size: file.size, type: file.type };
			});
			this.dataInput.val(JSON.stringify(formValues));
		}
	},
	/**
	 * Icon
	 */
	Icon: class Icon {
		constructor(container) {
			this.container = container;
			this.init();
		}
		/**
		 * Register function
		 * @param {jQuery} container
		 */
		static register(container) {
			if (container.hasClass('js-icon-container')) {
				return new Icon(container);
			}
			const instances = [];
			container.find('.js-icon-container').each((_, e) => {
				instances.push(new Icon($(e)));
			});

			return instances;
		}
		/**
		 * Initiation
		 */
		init() {
			this.iconElement = $('.js-icon-show', this.container);
			$('.js-clear-selection', this.container)
				.off('click')
				.on('click', () => {
					this.clear();
				});
			$('.js-icon-select', this.container)
				.off('click')
				.on('click', () => {
					App.Components.Icons.modalView().done((data) => {
						if (data.type === 'icon') {
							const span = document.createElement('span');
							span.setAttribute('class', data.name);
							this.iconElement.html('').append(span);
						} else if (data.type === 'image') {
							const image = document.createElement('img');
							image.setAttribute('class', 'icon-img--picklist');
							image.setAttribute('src', data.src);
							this.iconElement.html('').append(image);
						}
						this.setValue(data);
						this.setDisplayValue(data.name);
					});
				});
		}
		/**
		 * Clear selection
		 */
		clear() {
			let element = this.getField();
			let fieldName = element.attr('name');
			element.val('');
			this.container.find(`#${fieldName}_display`).val('');
			this.iconElement.html('');
		}
		/**
		 * Set icon name
		 * @param {string} data
		 */
		setDisplayValue(name) {
			let fieldName = this.getField().attr('name');
			this.container.find(`#${fieldName}_display`).val(name).attr('readonly', true);
		}
		/**
		 * Set value
		 * @param {Object} data
		 */
		setValue(data) {
			let { type, name } = data;
			if (data.key) {
				name = data.key;
			}
			this.getField().val(JSON.stringify({ type: type, name: name }));
		}
		/**
		 * Gets field
		 */
		getField() {
			return this.container.find('.js-source-field');
		}
	},
	Utils: {
		registerMobileDateRangePicker(element) {
			this.hideMobileKeyboard(element);
			if (!Quasar.plugins.Platform.is.desktop) {
				element
					.on('showCalendar.daterangepicker', (ev, picker) => {
						picker.container.addClass('js-visible');
					})
					.on('hide.daterangepicker', (ev, picker) => {
						picker.container.removeClass('js-visible');
					});
			}
		},
		hideMobileKeyboard(element) {
			if (!Quasar.plugins.Platform.is.desktop) {
				element.attr('readonly', 'true').addClass('bg-white');
			}
		},
		positionPicker(ev, picker) {
			let offset = picker.element.offset();
			let $window = $(window);
			if (offset.left - $window.scrollLeft() + picker.container.outerWidth() > $window.width()) {
				picker.opens = 'left';
			} else {
				picker.opens = 'right';
			}
			picker.move();
			if (offset.top - $window.scrollTop() + picker.container.outerHeight() > $window.height()) {
				picker.drops = 'up';
			} else {
				picker.drops = 'down';
			}
			picker.move();
		},
		/**
		 * Set a value for the field
		 *
		 * @param   {jQuery}  field Field element
		 * @param   {mixed}  value The value to set
		 * @param   {object}  params Additional parameters [optional]
		 * @param   {boolean}  animation
		 */
		setValue(field, value, params, animation = true) {
			const fieldInfo = field.data('fieldinfo');
			switch (fieldInfo['type']) {
				case 'picklist':
				case 'languages':
				case 'country':
				case 'currencyList':
				case 'modules':
					App.Fields.Picklist.setValue(field, value, params);
					break;
				case 'multiReference':
					App.Fields.MultiReference.setValue(field, value, params);
					break;
				default:
					field.val(value);
					break;
			}
			if (animation) {
				const fieldValue = field.closest('.fieldValue');
				fieldValue.addClass('border border-info');
				setTimeout(function () {
					fieldValue.removeClass('border border-info');
				}, 5000);
			}
		}
	}
};
