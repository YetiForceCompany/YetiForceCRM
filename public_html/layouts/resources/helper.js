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

$.Class("Vtiger_Helper_Js", {
	checkServerConfigResponseCache: '',
	langCode: '',
	/*
	 * Function to set lang code
	 */
	setLangCode: function () {
		var htmlTag = document.getElementsByTagName('html')[0];
		this.langCode = htmlTag.getAttribute('lang') ? htmlTag.getAttribute('lang') : 'en';
	},
	/*
	 * Function to get lang code
	 */
	getLangCode: function () {
		if (!this.langCode) {
			this.setLangCode();
		}
		return this.langCode;
	},
	/*
	 * Function to get the instance of Mass edit of Email
	 */
	getEmailMassEditInstance: function () {

		var className = 'Emails_MassEdit_Js';
		var emailMassEditInstance = new window[className]();
		return emailMassEditInstance
	},
	getDayFromDate: function (date) {
		var dayOfWeek = this.getDay(date);
		return this.getLabelDayFromDate(dayOfWeek);
	},
	getDay: function (date) {
		var dateObj = new Date(date);
		if (isNaN(dateObj.getDay())) {
			dateObj = Date.parse(date);
		}
		return dateObj.getDay();
	},
	getLabelDayFromDate: function (day) {
		var dayOfWeek = day;
		var dayOfWeekLabel = '';
		switch (dayOfWeek) {
			case 0:
				dayOfWeekLabel = 'JS_SUN';
				break;
			case 1:
				dayOfWeekLabel = 'JS_MON';
				break;
			case 2:
				dayOfWeekLabel = 'JS_TUE';
				break;
			case 3:
				dayOfWeekLabel = 'JS_WED';
				break;
			case 4:
				dayOfWeekLabel = 'JS_THU';
				break;
			case 5:
				dayOfWeekLabel = 'JS_FRI';
				break;
			case 6:
				dayOfWeekLabel = 'JS_SAT';
				break;
		}
		return app.vtranslate(dayOfWeekLabel);
	},
	/*
	 * Function to get Date Instance
	 * @params date---this is the field value
	 * @params dateFormat---user date format
	 * @return date object
	 */

	getDateInstance: function (dateTime, dateFormat) {
		let dateTimeComponents = dateTime.split(" "),
			dateComponent = dateTimeComponents[0],
			timeComponent = dateTimeComponents[1],
			seconds = '00',
			dotMode = '-';
		if (dateFormat.indexOf("-") !== -1) {
			dotMode = '-';
		}
		if (dateFormat.indexOf(".") !== -1) {
			dotMode = '.';
		}
		if (dateFormat.indexOf("/") !== -1) {
			dotMode = '/';
		}

		let splittedDate = dateComponent.split(dotMode),
			splittedDateFormat = dateFormat.split(dotMode),
			year = splittedDate[splittedDateFormat.indexOf("yyyy")],
			month = splittedDate[splittedDateFormat.indexOf("mm")],
			date = splittedDate[splittedDateFormat.indexOf("dd")],
			dateInstance = Date.parse(year + '/' + month + '/' + date);
		if (isNaN(dateInstance) || (year.length > 4) || (month.length > 2) || (date.length > 2) || (dateInstance == null)) {
			throw app.vtranslate('JS_INVALID_DATE');
		}

		//Before creating date object time is set to 00
		//because as while calculating date object it depends system timezone
		if (typeof timeComponent === "undefined") {
			timeComponent = '00:00:00';
		}

		let timeSections = timeComponent.split(':');
		if (typeof timeSections[2] !== "undefined") {
			seconds = timeSections[2];
		}

		//Am/Pm component exits
		if (typeof dateTimeComponents[2] !== "undefined") {
			if (dateTimeComponents[2].toLowerCase() === 'pm' && timeSections[0] !== '12') {
				timeSections[0] = parseInt(timeSections[0], 10) + 12;
			}

			if (dateTimeComponents[2].toLowerCase() === 'am' && timeSections[0] === '12') {
				timeSections[0] = '00';
			}
		}

		month = month - 1;
		dateInstance = new Date(year, month, date, timeSections[0], timeSections[1], seconds);
		return dateInstance;
	},
	/*
	 * Function to show the confirmation messagebox
	 */
	showConfirmationBox: function (params) {
		var aDeferred = $.Deferred();
		var baseParams = {
			callback: function (result) {
				if (result) {
					aDeferred.resolve();
				} else {
					aDeferred.reject();
				}
			}
		}
		var bootBoxModal = bootbox.confirm($.extend(baseParams, params));
		bootBoxModal.on('hidden', function (e) {
			//In Case of multiple modal. like mass edit and quick create, if bootbox is shown and hidden , it will remove
			// modal open
			if ($('#' + Window.lastModalId).length > 0) {
				// Mimic bootstrap modal action body state change
				$('body').addClass('modal-open');
			}
		})
		return aDeferred.promise();
	},
	showMessage: function (params) {
		if (typeof params.type === "undefined") {
			params.type = 'info';
		}
		if (typeof params.title === "undefined") {
			params.title = app.vtranslate('JS_MESSAGE');
		}
		Vtiger_Helper_Js.showPnotify(params);
	},
	/*
	 * Function to show pnotify message
	 */
	showPnotify: function (customParams) {
		let userParams = customParams;
		if (typeof customParams === 'string') {
			userParams = {};
			userParams.text = customParams;
		}
		let params = {
			target: document.body,
			data: {
				type: 'error',
				hide: false,
				delay: '2000',
				modules: {
					Buttons: {
						closerHover: false,
						labels: {close: app.vtranslate('JS_CLOSE')}
					},
					Animate: {
						animate: true,
						inClass: 'zoomInLeft',
						outClass: 'zoomOutRight'
					}
				}
			}
		};
		if (typeof customParams.type !== "undefined" && customParams.type != 'error') {
			params.data.hide = true;
		}
		params.data = $.extend(params.data, userParams);
		return new PNotify(params);
	},
	/*
	 * Function to remove pnotify message
	 */
	hidePnotify: function (notice) {
		if (typeof notice === "undefined") {
			notice = $('.ui-pnotify');
		}
		notice.remove();
	},
	/*
	 * Function to add clickoutside event on the element - By using outside events plugin
	 * @params element---On which element you want to apply the click outside event
	 * @params callbackFunction---This function will contain the actions triggered after clickoutside event
	 */
	addClickOutSideEvent: function (element, callbackFunction) {
		element.one('clickoutside', callbackFunction);
	},
	/*
	 * Function to show horizontal top scroll bar
	 */
	showHorizontalTopScrollBar: function () {
		var container = $('.contentsDiv');
		var topScroll = $('.contents-topscroll', container);
		var bottomScroll = $('.contents-bottomscroll', container);
		$('.bottomscroll-div', container).attr('style', '');
		$('.topscroll-div', container).css('width', $('.bottomscroll-div', container).outerWidth());
		$('.bottomscroll-div', container).css('width', $('.topscroll-div', container).outerWidth());
		topScroll.on('scroll', function () {
			bottomScroll.scrollLeft(topScroll.scrollLeft());
		});
		bottomScroll.on('scroll', function () {
			topScroll.scrollLeft(bottomScroll.scrollLeft());
		});
	},
	convertToDateString: function (stringDate, dateFormat, modDay, type) {
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

		var splittedDate = stringDate.split(dotMode);
		var splittedDateFormat = dateFormat.split(dotMode);
		var year = splittedDate[splittedDateFormat.indexOf("yyyy")];
		var month = splittedDate[splittedDateFormat.indexOf("mm")];
		var date = splittedDate[splittedDateFormat.indexOf("dd")];
		var dateInstance = new Date(year, month - 1, date);
		if ((year.length > 4) || (month.length > 2) || (date.length > 2) || (dateInstance == null)) {
			var errorMsg = app.vtranslate('JS_INVALID_DATE');
			throw errorMsg;
		}
		var newDate = dateInstance;
		if ('0' == modDay) {
			if ('Calendar' == type) {
				newDate.setDate(dateInstance.getDate() - 1);
			}
		} else if ('-1' == modDay) {
			if ('Calendar' == type) {
				newDate.setDate(dateInstance.getDate() - 2);
			} else {
				newDate.setDate(dateInstance.getDate() - 1);
			}
		} else {
			if ('Calendar' != type) {
				newDate.setTime(dateInstance.getTime() + parseInt(modDay) * 24 * 60 * 60 * 1000);
			}
		}
		return app.getStringDate(newDate);
	},
	hideOptions: function (element, attr, value) {
		var opval = '';
		element.find('option').each(function (index, option) {
			option = $(option);
			if (value != option.data(attr)) {
				option.addClass('d-none');
				option.attr('disabled', 'disabled');
			} else {
				if (opval == '') {
					opval = option.val();
				}
				option.removeClass('d-none');
				option.removeAttr('disabled');
			}
		});
		element.val(opval).trigger('change');
	},
	unique: function (array) {
		return array.filter(function (el, index, arr) {
			return index === arr.indexOf(el);
		});
	}
}, {});
