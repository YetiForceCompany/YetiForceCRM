/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

window.App.Fields = {
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
		register(parentElement, registerForAddon, customParams, clasName = 'dateField') {
			if (typeof parentElement === "undefined") {
				parentElement = $('body');
			} else {
				parentElement = $(parentElement);
			}
			if (typeof registerForAddon === "undefined") {
				registerForAddon = true;
			}
			let elements = $('.' + clasName, parentElement);
			if (parentElement.hasClass(clasName)) {
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
					$(e.currentTarget).closest('.date').find('input.' + clasName).get(0).focus();
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
					format: format,
					titleFormat: 'MM yyyy', /* Leverages same syntax as 'format' */
					weekStart: CONFIG.firstDayOfWeekNo
				};
			}
			let params = {
				todayBtn: "linked",
				clearBtn: true,
				language: CONFIG.langKey,
				weekStart: CONFIG.firstDayOfWeekNo,
				autoclose: true,
				todayHighlight: true,
				format: format,
				enableOnReadonly: false
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
				}
			};

			if (typeof customParams !== "undefined") {
				params = $.extend(params, customParams);
			}
			parentElement.find('.js-date__btn').off().on('click', (e) => {
				$(e.currentTarget).parent().next('.dateRangeField')[0].focus();
			});
			elements.each((index, element) => {
				let currentParams = $.extend(true, params, $(element).data('params'));
				$(element).daterangepicker(currentParams)
					.on('apply.daterangepicker', function (ev, picker) {
						$(this).val(picker.startDate.format(currentParams.locale.format) + ',' + picker.endDate.format(currentParams.locale.format));
						$(this).trigger('change');
					})
					.on('show.daterangepicker', (ev, picker) => {
						this.positionPicker(ev, picker);
					})
					.on('showCalendar.daterangepicker', (ev, picker) => {
						this.positionPicker(ev, picker);
					});
			});
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
		}
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
				if (elements.length !== 0 && typeof elements !== "undefined") {
					this.isModal = elements.closest('.js-modal-container').length;
					if (this.isModal) {
						let self = this;
						this.progressInstance = $.progressIndicator({
							blockInfo: {
								enabled: true,
								onBlock: () => {
									self.loadEditor(elements, params);
								}
							},
						});
					} else {
						this.loadEditor(elements, params);
					}
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
				const instance = this.getEditorInstanceFromName(),
					self = this;
				let config = {
					language: CONFIG.langKey,
					allowedContent: true,
					removeButtons: '',
					scayt_autoStartup: false,
					enterMode: CKEDITOR.ENTER_BR,
					shiftEnterMode: CKEDITOR.ENTER_P,
					emojiEnabled: false,
					mentionsEnabled: false,
					on: {
						instanceReady: function (evt) {
							evt.editor.on('blur', function () {
								evt.editor.updateElement();
							});
							if (self.isModal) {
								self.progressInstance.progressIndicator({mode: 'hide'});
							}
						}
					},
					extraPlugins: 'colorbutton,pagebreak,colordialog,find,selectall,showblocks,div,print,font,justify,bidi',
					toolbar: 'Full',
					toolbar_Full: [
						{
							name: 'clipboard',
							items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
						},
						{name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt']},
						{name: 'links', items: ['Link', 'Unlink']},
						{
							name: 'insert',
							items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak']
						},
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
						{name: 'basicstyles', items: ['CopyFormatting', 'RemoveFormat']}
					],
					toolbar_Min: [
						{
							name: 'basicstyles',
							items: ['Bold', 'Italic', 'Underline', 'Strike']
						},
						{name: 'colors', items: ['TextColor', 'BGColor']},
						{name: 'tools', items: ['Maximize']},
						{
							name: 'paragraph',
							items: ['NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl']
						},
						{name: 'basicstyles', items: ['CopyFormatting', 'RemoveFormat']}
					],
					toolbar_Clipboard: [
						{name: 'document', items: ['Print']},
						{name: 'basicstyles', items: ['CopyFormatting', 'RemoveFormat']},
						{
							name: 'clipboard',
							items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
						}
					]
				};
				if (typeof customConfig !== "undefined") {
					config = $.extend(config, customConfig);
				}
				config = Object.assign(config, element.data());
				if (config.emojiEnabled) {
					let emojiToolbar = {name: 'links', items: ['EmojiPanel']};
					if (typeof config.toolbar === 'string') {
						config[`toolbar_${config.toolbar}`].push(emojiToolbar);
					} else if (Array.isArray(config.toolbar)) {
						config.toolbar.push(emojiToolbar);
					}
					config.extraPlugins = config.extraPlugins + ',emoji'
					config.outputTemplate = '{id}';
				}
				if (config.mentionsEnabled) {
					config.extraPlugins = config.extraPlugins + ',mentions'
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
				return [{
					feed: this.getMentionUsersData.bind(this),
					itemTemplate: `<li data-id="{id}" class="row no-gutters">
											<div class="c-img__completion__container">
												<div class="{icon} m-auto u-w-fit u-font-size-14px"></div>
												<img src="{image}" class="c-img__completion mr-2" alt="{label}" title="{label}">
											</div>
											<div class="col row no-gutters u-overflow-x-hidden">
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
											<div class="col c-circle-icon mr-1">
												<span class="userIcon-{module}"></span>
											</div>
											<div class="col row no-gutters u-overflow-x-hidden">
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
				if (typeof inputDiv === "undefined" || inputDiv.length === 0) {
					return;
				}
				let basicParams = {
					completionsCollection: {
						records: true,
						users: true,
						emojis: true
					}
				};
				this.params = Object.assign(basicParams, inputDiv.data(), params);
				this.inputDiv = inputDiv;
				this.collection = [];
				if (this.params.completionsCollection.records) {
					this.collection.push(this.registerMentionCollection('#'))
				}
				if (this.params.completionsCollection.users) {
					this.collection.push(this.registerMentionCollection('@', 'owners'))
				}
				if (this.params.completionsCollection.emojis) {
					this.collection.push(this.registerEmojiCollection())
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
							return `<a href="#" data-id="${symbol + item.original.id}" data-module="${item.original.module}">${item.original.label.split('(')[0].trim()}</a>`;
						}
						return symbol + item.original.label;
					},
					values: (text, cb) => {
						if (text.length >= CONFIG.globalSearchAutocompleteMinLength) {
							App.Fields.Text.getMentionData(text, users => cb(users), searchModule);
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
					fillAttr: 'label',
				}
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
					},
				}
			}

			/*
			 * Mention template
			 */
			mentionTemplate(params) {
				let icon = '';
				if (params.module !== undefined) {
					icon = `userIcon-${params.module}`;
				}
				if (params.icon !== undefined && params.icon !== '') {
					icon = params.icon;
				}
				let avatar = `<div class="col c-circle-icon mr-1">
								<span class="${icon}"></span>
							</div>`;
				if (params.image !== undefined && params.image !== '') {
					avatar = `<div class="c-img__completion__container"><img src="${params.image}" class="c-img__completion mr-2" alt=${params.label}" title="${params.label}"></div>`
				}
				return `<div data-id="${params.id}" class="row no-gutters">
							${avatar}
							<div class="col row no-gutters u-overflow-x-hidden">
								<strong class="u-text-ellipsis--no-hover col-12">${params.label}</strong>
								<div class="fullname col-12 u-text-ellipsis--no-hover text-muted small">${params.category}</div>
							</div>
						</div>`
			}

			/**
			 * Register
			 * @param {jQuery} inputDiv - contenteditable div
			 */
			register(inputDiv) {
				const self = this;
				this.completionsCollection = new Tribute({
					collection: self.collection,
					allowSpaces: true,
					replaceTextSuffix: '',
				});
				this.completionsCollection.attach(inputDiv[0]);
				if (this.params.completionsTextarea !== undefined) {
					this.registerCompletionsTextArea(inputDiv);
				}
				if (this.params.completionsButtons !== undefined) {
					this.registerCompletionsButtons();
				}
				if (App.emoji === undefined) {
					fetch(`${CONFIG.siteUrl}/vendor/ckeditor/ckeditor/plugins/emoji/emoji.json`)
						.then(response => response.json())
						.then(response => {
							App.emoji = response;
						}).catch(error => console.error('Error:', error));
				}
				this.registerTagClick(inputDiv);
			}

			/**
			 * Register completons hidden textarea - useful with forms
			 * @param {jQuery} inputDiv - contenteditable div
			 */
			registerCompletionsTextArea(inputDiv) {
				let textarea = inputDiv.siblings(`[name=${inputDiv.attr('id')}]`);
				inputDiv.on('focus', function () {
					textarea.val(inputDiv.html());
				}).on('blur keyup paste input', function () {
					textarea.val(inputDiv.html());
				});
			}

			/**
			 * Register tag click
			 * @param inputDiv
			 */
			registerTagClick(inputDiv) {
				inputDiv.closest('.js-completions__container').find('.js-completions__messages').on('click', '.js-completions__tag', (e) => {
					e.preventDefault();
					inputDiv.append($(e.target).clone());
				});
			}

			/**
			 * Register completions buttons
			 */
			registerCompletionsButtons() {
				let completionsContainer = this.inputDiv.parents().eq(3);
				this.registerEmojiPanel(this.inputDiv, completionsContainer.find('.js-completions__emojis'));
				completionsContainer.find('.js-completions__users').on('click', (e) => {
					this.completionsCollection.showMenuForCollection(this.inputDiv[0], 1);
				});
				completionsContainer.find('.js-completions__records').on('click', (e) => {
					this.completionsCollection.showMenuForCollection(this.inputDiv[0], 0);
				});
			}

			/**
			 * Register emojipanel library
			 * @param {jQuery} inputDiv - contenteditable div
			 * @param {jQuery} emojisContainer
			 */
			registerEmojiPanel(inputDiv, emojisContainer) {
				new EmojiPanel({
					container: '.js-completions__emojis',
					json_url: CONFIG.siteUrl + 'libraries/emojipanel/dist/emojis.json',
				});
				emojisContainer.on('click', (e) => {
					let element = $(e.target);
					element.toggleClass('active');
				})
				emojisContainer.on('click', '.emoji', (e) => {
					e.preventDefault();
					e.stopPropagation();
					if ($(e.currentTarget).data('char') !== undefined) {
						inputDiv.append(`${$(e.currentTarget).data('char')}`);
					}
				});
				emojisContainer.on('mouseenter', '.emoji', (e) => {
					if ($(e.currentTarget).data('name') !== undefined) {
						emojisContainer.find('.emoji-hovered').remove();
						emojisContainer.find('footer').prepend(`<div class="emoji-hovered">${$(e.currentTarget).data('char') + ' ' + $(e.currentTarget).data('name')}</div>`);
					}
				});
				emojisContainer.on('clickoutside', () => {
					emojisContainer.removeClass('active');
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
				})
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
			const hash = Math.random().toString(36).substr(2, 10) + Math.random().toString(36).substr(2, 10) + new Date().valueOf() + Math.random().toString(36).substr(2, 6);
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
			if (typeof parent === "undefined") {
				parent = $('body');
			}
			if (typeof view === "undefined") {
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
			if (typeof params === "undefined") {
				params = {};
			}
			if ($(selectElement).length > 1) {
				return $(selectElement).each((index, element) => {
					this.showSelect2ElementView($(element).eq(0), params);
				});
			}
			params = this.registerParams(selectElement, params);
			selectElement.each(function () {
				let select = $(this);
				let htmlBoolParams = select.data('select');
				if (htmlBoolParams === 'tags') {
					params.tags = true;
					params.tokenSeparators = [","]
				} else {
					params[htmlBoolParams] = true;
				}
				select.select2(params)
					.on("select2:open", (e) => {
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
					}).on("select2:unselect", () => {
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
					let actualElement = $(data.element);
					if (typeof selectElement.data('showAdditionalIcons') !== "undefined" && actualElement.is('option')) {
						return '<div class="js-element__title d-flex justify-content-between" data-js="appendTo"><div class="u-text-ellipsis--no-hover">' + actualElement.text() + '</div></div>';
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
				params.escapeMarkup = function (markup) {
					return markup;
				};
			} else if (typeof this[params.templateResult] === 'function') {
				params.templateResult = this[params.templateResult];
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
		 * @param selectElement
		 * @param params
		 * @returns {*}
		 */
		registerAjaxParams(selectElement, params) {
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
				method: 'POST',
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
			return params;
		},
		/**
		 * Prepend template with a flag, function is calling by select2
		 * @param optionData
		 * @returns {Mixed|jQuery|HTMLElement}
		 */
		prependDataTemplate(optionData) {
			let template = optionData.text;
			if (optionData.id !== undefined && optionData.id !== '') {
				template = $(optionData.element.dataset.template);
				if (optionData.element.dataset.state !== undefined) { //check if element has icons with different states
					if (optionData.element.dataset.state === 'active') {
						template.find('.js-select-option-event').removeClass(optionData.element.dataset.iconInactive)
							.addClass(optionData.element.dataset.iconActive)
					} else {
						template.find('.js-select-option-event').removeClass(optionData.element.dataset.iconActive)
							.addClass(optionData.element.dataset.iconInactive)
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
			select.find('option[data-sort-index]').sort((a, b) => {
				return ($(b).data('sort-index')) < ($(a).data('sort-index')) ? 1 : -1;
			}).appendTo(select);
		},
		/**
		 * Register select drag and drop sorting
		 * @param {jQuery} select2 element
		 * @param {function} callback function
		 */
		registerSortEvent(select, cb = () => {
		}) {
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
				if (currentTarget.is('path')) { //svg target fix
					currentTarget = currentTarget.closest('.js-select-option-event');
				}
				let currentElementData = $(event.params.args.data.element).data(),
					optionElement = $(event.params.args.data.element),
					progressIndicatorElement = $.progressIndicator({blockInfo: {enabled: true}});
				AppConnector.request(currentElementData.url).done((data) => {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
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
							Vtiger_Helper_Js.showPnotify({text: response.message, type: 'success'});
						}
					} else if (response && response.message) {
						Vtiger_Helper_Js.showPnotify({text: response.message});
					}
				}).fail(function () {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
				});
			});
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
		register(container) {
			container.find('.js-multi-email').each((index, element) => {
				const inputElement = element;
				$(element).find('.js-email').each((index, element) => {
					$(element).on('change', (e) => {
						App.Fields.MultiEmail.parseToJSON($(inputElement));
					});
				});
				$(element).find('.js-add-item').each((index, element) => {
					$(element).on('click', (e) => {
						App.Fields.MultiEmail.addEmail($(inputElement));
					});
				});
				$(element).find('.js-remove-item').each((index, element) => {
					$(element).on('click', (e) => {
						App.Fields.MultiEmail.removeEmail($(e.target), $(inputElement));
						App.Fields.MultiEmail.parseToJSON(container);
					});
				});
				$(element).find('input.js-checkbox').each((index, element) => {
					$(element).on('change', (e) => {
						App.Fields.MultiEmail.toggleCheckBox($(e.target));
						App.Fields.MultiEmail.parseToJSON(container);
					});
				});
			});
		},
		/**
		 * Convert data to json
		 * @param {jQuery} element
		 */
		parseToJSON(element) {
			let allFields = $(element).find('[class*=js-multi-email-row]');
			let arr = [];
			let arrayLength = allFields.length;
			for (let i = 0; i < arrayLength; ++i) {
				let inputField = $(allFields[i]).find('input.js-email').eq(0);
				let checkboxField = $(allFields[i]).find('input.js-checkbox').eq(0);
				if (inputField.val() !== '') {
					arr.push({
						e: $(inputField).val(),
						o: $(checkboxField).is(":checked") ? 1 : 0
					});
				}
			}
			$(element).find('input.js-hidden-email').val(JSON.stringify(arr));
		},
		/**
		 * Invoked after clicking the add button
		 * @param {jQuery} container
		 */
		addEmail(container) {
			let newField = container.find('[class*=js-multi-email-row]').eq(0).clone(false, false);
			let cnt = container.find('[class*=js-multi-email-row]').length + 1;
			newField.removeClass('js-multi-email-row-1');
			newField.addClass('js-multi-email-row-' + cnt);
			newField.find('input.js-email').val('');
			newField.find('input.js-checkbox').removeAttr('checked');
			newField.find('label.js-label-checkbox').removeClass('active');
			newField.find('.js-remove-item').eq(0).on('click', (e) => {
				App.Fields.MultiEmail.removeEmail($(e.target), container);
				App.Fields.MultiEmail.parseToJSON(container);
			});
			newField.find('input.js-checkbox').eq(0).on('change', (e) => {
				App.Fields.MultiEmail.toggleCheckBox($(e.target));
				App.Fields.MultiEmail.parseToJSON(container);
			});
			newField.find('input.js-email').eq(0).on('change', (e) => {
				App.Fields.MultiEmail.parseToJSON(container);
			});
			newField.insertAfter(container.find('[class*=js-multi-email-row]').last());
		},
		/**
		 * Invoked after clicking the remove button
		 * @param {jQuery} container
		 */
		removeEmail(element, container) {
			if (container.find('[class*=js-multi-email-row]').length > 1) {
				element.closest('[class*=js-multi-email-row]').remove();
			}
		},
		/**
		 * Toggle checkbox
		 * @param {jQuery} element
		 */
		toggleCheckBox(element) {
			if ($(element).is(":checked")) {
				element.closest('label.js-label-checkbox')
					.eq(0).find('svg.svg-inline--fa').eq(0)
					.removeClass('fa-square').addClass('fa-check-square');
			} else {
				element.closest('label.js-label-checkbox')
					.eq(0).find('svg.svg-inline--fa').eq(0)
					.removeClass('fa-check-square').addClass('fa-square');
			}
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
				inputElement.find('.js-add-item').on('click', (e) => {
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
					partData[fields[k]] = $(allFields[i]).find('[name="' + fields[k] + '"]').val();
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
		},
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
				return app.errorLog("Dependend select field container is missing.");
			}
			container.each(function () {
				const masterSelect = $(this),
					slaveSelect = $(masterSelect.data('slave')),
					data = masterSelect.data('data');
				if (!slaveSelect.length) {
					return app.errorLog("Could not find slave select element (data-slave attribute)");
				}
				if (!data) {
					return app.errorLog("Could not load data (data-data attribute)");
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
					integer = integer.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1" + groupSeparator);
				} else if (groupingPattern === '123456,789') {
					integer = integer.slice(0, -3) + groupSeparator + integer.slice(-3);
				} else if (groupingPattern === '12,34,56,789') {
					integer = integer.slice(0, -3).replace(/(\d)(?=(\d\d)+(?!\d))/g, "$1" + groupSeparator) + groupSeparator + integer.slice(-3);
				}
			}
			return integer;
		},
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
			value = parseFloat(value);
			if (fixed) {
				value = value.toFixed(numberOfDecimal);
			}
			let a = value.toString().split('.');
			let integer = App.Fields.Integer.formatToDisplay(a[0]);
			let decimal = a[1];
			if (numberOfDecimal) {
				if (CONFIG.truncateTrailingZeros) {
					if (decimal) {
						let d = '';
						for (var i = 0; i < decimal.length; i++) {
							if (decimal[decimal.length - i - 1] !== '0') {
								d = decimal[decimal.length - i - 1] + d;
							}
						}
						decimal = d;
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
	}
}
;
