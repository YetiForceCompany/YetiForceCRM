/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
App.Fields = {
	'Date': {
		months: ["JS_JAN", "JS_FEB", "JS_MAR", "JS_APR", "JS_MAY", "JS_JUN", "JS_JUL", "JS_AUG", "JS_SEP", "JS_OCT", "JS_NOV", "JS_DEC"],
		fullMonths: ["JS_JANUARY", "JS_FEBRUARY", "JS_MARCH", "JS_APRIL", "JS_MAY", "JS_JUNE", "JS_JULY", "JS_AUGUST", "JS_SEPTEMBER", "JS_OCTOBER", "JS_NOVEMBER", "JS_DECEMBER"],
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
		}
	},
	Password: {
		/**
		 * Register clip
		 * @param {string} key
		 * @returns {ClipboardJS}
		 */
		registerCopyClipboard: function (key) {
			if (key == undefined) {
				key = '.clipboard';
			}
			return new ClipboardJS(key, {
				text: function (trigger) {
					Vtiger_Helper_Js.showPnotify({
						text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
						type: 'success'
					});
					trigger = jQuery(trigger);
					var element = jQuery(trigger.data('copyTarget'));
					var val;
					if (typeof trigger.data('copyType') !== 'undefined') {
						if (element.is("select")) {
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
	},
	DateTime: {
		/*
		 * Initialization datetime fields
		 * @param {jQuery} parentElement
		 * @param {jQuery} customParams
		 */
		register: function (parentElement, customParams) {
			if (typeof parentElement == 'undefined') {
				parentElement = jQuery('body');
			} else {
				parentElement = jQuery(parentElement);
			}
			if (parentElement.hasClass('dateTimePickerField')) {
				var elements = parentElement;
			} else {
				var elements = jQuery('.dateTimePickerField', parentElement);
			}
			if (elements.length == 0) {
				return;
			}
			var parentDateElem = elements.closest('.dateTime');
			jQuery('.input-group-text', parentDateElem).on('click', function (e) {
				var elem = jQuery(e.currentTarget);
				elem.closest('.dateTime').find('input.dateTimePickerField ').get(0).focus();
			});
			var language = jQuery('body').data('language');
			var dateFormat = elements.data('dateFormat').toUpperCase();
			var timeFormat = elements.data('hourFormat');
			var timePicker24Hour = true;
			if (timeFormat === 24) {
				timeFormat = 'hh:mm';
			} else {
				timePicker24Hour = false;
				timeFormat = 'hh:mm A';
			}
			var format = dateFormat + ' ' + timeFormat
			if ($.fn.datepicker.dates[language] == undefined) {
				var langCodes = Object.keys($.fn.datepicker.dates);
				language = langCodes[0];
			}
			var params = {
				singleDatePicker: true,
				showDropdowns: true,
				timePicker: true,
				timePicker24Hour: timePicker24Hour,
				timePickerIncrement: 1,
				autoUpdateInput: true,
				autoApply: true,
				opens: "left",
				locale: {
					separator: ',',
					format: format,
					applyLabel: app.vtranslate('JS_APPLY'),
					cancelLabel: app.vtranslate('JS_CANCEL'),
					monthNames: $.fn.datepicker.dates[language].months,
					daysOfWeek: $.fn.datepicker.dates[language].daysMin,
					firstDay: $.fn.datepicker.dates[language].weekStart
				},
			};
			if (typeof customParams != 'undefined') {
				params = jQuery.extend(params, customParams);
			}
			elements.each(function (index, element) {
				element = $(element);
				element.daterangepicker(params);
				element.on('apply.daterangepicker', function (ev, picker) {
					$(this).val(picker.startDate.format(format));
				});
			});
		},
	},
	Text: {
		/*
		 * Initialization CkEditor
		 * @param {jQuery} parentElement
		 * @param {Object} customParams
		 */
		registerCkEditor: function (parentElement, params) {
			if (typeof parentElement == 'undefined') {
				parentElement = jQuery('body');
			} else {
				parentElement = jQuery(parentElement);
			}
			if (parentElement.hasClass('js-ckeditor') && !parentElement.prop('disabled')) {
				var elements = parentElement;
			} else {
				var elements = jQuery('.js-ckeditor:not([disabled])', parentElement);
			}
			if (elements.length == 0) {
				return;
			}
			$.each(elements, function (key, element) {
				var ckEditorInstance = new Vtiger_CkEditor_Js();
				ckEditorInstance.loadCkEditor($(element), params);
			});
		},
		/**
		 * Destroy ckEditor
		 * @param {jQuery} element
		 */
		destroyCkEditor: function (element) {
			if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances && element.attr('id') in CKEDITOR.instances) {
				CKEDITOR.instances[element.attr('id')].destroy();
			}
		},
	}
}
