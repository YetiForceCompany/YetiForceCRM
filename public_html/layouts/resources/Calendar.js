/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

window.Calendar_Js = class Calendar_Js {

	constructor(container = $('.js-base-container'), readonly = false) {
		this.calendarView = false;
		this.calendarCreateView = false;
		this.container = container;
		this.readonly = readonly;
		this.browserHistoryConfig = readonly ? {} : this.setBrowserHistoryConfig();
		this.calendarBasicOptions = this.setCalendarBasicOptions();
		this.calendarAdvancedOptions = this.setCalendarAdvancedOptions();
		this.calendarModuleOptions = this.setCalendarModuleOptions();
		this.calendarMergedOptions = this.setCalendarMergedOptions();
	}

	setCalendarHeight() {
		let paddingTop = 15, calendarH;
		if ('CalendarExtended' === CONFIG.view) {
			paddingTop = 5;
		}
		if (this.container.hasClass('js-modal-container')) {
			if (this.container.closest('.user-info--active').length) {
				paddingTop = 23;
			} else {
				paddingTop = 47;
			}
		}
		if ($(window).width() > 993) {
			calendarH = $(window).height() - this.container.find('.js-calendar__container').offset().top - $('.js-footer').height() - paddingTop;
			new ResizeSensor(this.container.find('.contentsDiv'), () => {
				calendarH = $(window).height() - this.container.find('.js-calendar__container').offset().top - $('.js-footer').height() - paddingTop;
				$('.js-calendar__container').fullCalendar('option', 'height', calendarH);
				$('.js-calendar__container').height(calendarH + 10); // without this line calendar scroll stops working
			});
		} else if ($(window).width() < 993) {
			calendarH = 'auto';
		}
		return calendarH;
	}

	renderCalendar() {
		this.getCalendarView().fullCalendar(this.calendarMergedOptions);
	}

	setCalendarMergedOptions() {
		return Object.assign(this.calendarBasicOptions, this.calendarAdvancedOptions, this.calendarModuleOptions, this.browserHistoryConfig);
	}

	setCalendarModuleOptions() {
		return {};
	}

	setCalendarAdvancedOptions() {
		let self = this;
		return {
			header: {
				left: 'month,' + app.getMainParams('weekView') + ',' + app.getMainParams('dayView'),
				center: 'title today',
				right: 'prev,next'
			},
			allDaySlot: false,
			views: {
				basic: {
					eventLimit: false,
				}
			},
			eventDrop: function (event, delta, revertFunc) {
				self.updateEvent(event, delta, revertFunc);
			},
			eventResize: function (event, delta, revertFunc) {
				self.updateEvent(event, delta, revertFunc);
			},
			eventRender: self.eventRenderer,
			height: this.setCalendarHeight(this.container)
		}
	}

	setCalendarBasicOptions() {
		let eventLimit = app.getMainParams('eventLimit'),
			userDefaultActivityView = app.getMainParams('activity_view'),
			defaultView = app.moduleCacheGet('defaultView'),
			userDefaultTimeFormat = app.getMainParams('time_format');
		if (eventLimit == 'true') {
			eventLimit = true;
		} else if (eventLimit == 'false') {
			eventLimit = false;
		} else {
			eventLimit = parseInt(eventLimit) + 1;
		}
		if (userDefaultActivityView === 'Today') {
			userDefaultActivityView = app.getMainParams('dayView');
		} else if (userDefaultActivityView === 'This Week') {
			userDefaultActivityView = app.getMainParams('weekView');
		} else {
			userDefaultActivityView = 'month';
		}
		if (defaultView != null) {
			userDefaultActivityView = defaultView;
		}
		if (userDefaultTimeFormat == 24) {
			userDefaultTimeFormat = 'H:mm';
		} else {
			userDefaultTimeFormat = 'h:mmt';
		}
		let options = {
			timeFormat: userDefaultTimeFormat,
			axisFormat: userDefaultTimeFormat,
			defaultView: userDefaultActivityView,
			slotMinutes: 15,
			defaultEventMinutes: 0,
			forceEventDuration: true,
			defaultTimedEventDuration: '01:00:00',
			eventLimit: eventLimit,
			eventLimitText: app.vtranslate('JS_MORE'),
			selectHelper: true,
			scrollTime: app.getMainParams('start_hour') + ':00',
			monthNamesShort: [app.vtranslate('JS_JAN'), app.vtranslate('JS_FEB'), app.vtranslate('JS_MAR'),
				app.vtranslate('JS_APR'), app.vtranslate('JS_MAY'), app.vtranslate('JS_JUN'), app.vtranslate('JS_JUL'),
				app.vtranslate('JS_AUG'), app.vtranslate('JS_SEP'), app.vtranslate('JS_OCT'), app.vtranslate('JS_NOV'),
				app.vtranslate('JS_DEC')],
			dayNames: [app.vtranslate('JS_SUNDAY'), app.vtranslate('JS_MONDAY'), app.vtranslate('JS_TUESDAY'),
				app.vtranslate('JS_WEDNESDAY'), app.vtranslate('JS_THURSDAY'), app.vtranslate('JS_FRIDAY'),
				app.vtranslate('JS_SATURDAY')],
			buttonText: {
				today: app.vtranslate('JS_TODAY'),
				year: app.vtranslate('JS_YEAR'),
				month: app.vtranslate('JS_MONTH'),
				week: app.vtranslate('JS_WEEK'),
				day: app.vtranslate('JS_DAY')
			},
			allDayText: app.vtranslate('JS_ALL_DAY'),
		};
		if (app.moduleCacheGet('start') !== null) {
			var s = moment(app.moduleCacheGet('start')).valueOf();
			var e = moment(app.moduleCacheGet('end')).valueOf();
			options.defaultDate = moment(moment(s + ((e - s) / 2)).format('YYYY-MM-DD'));
		}
		return $.extend(this.getCalendarMinimalConfig(), options);
	}

	getCalendarMinimalConfig() {
		let hiddenDays = [];
		if (app.getMainParams('switchingDays') === 'workDays') {
			hiddenDays = app.getMainParams('hiddenDays', true);
		}
		return {
			firstDay: CONFIG.firstDayOfWeekNo,
			selectable: true,
			hiddenDays: hiddenDays,
			monthNames: [app.vtranslate('JS_JANUARY'), app.vtranslate('JS_FEBRUARY'), app.vtranslate('JS_MARCH'),
				app.vtranslate('JS_APRIL'), app.vtranslate('JS_MAY'), app.vtranslate('JS_JUNE'), app.vtranslate('JS_JULY'),
				app.vtranslate('JS_AUGUST'), app.vtranslate('JS_SEPTEMBER'), app.vtranslate('JS_OCTOBER'),
				app.vtranslate('JS_NOVEMBER'), app.vtranslate('JS_DECEMBER')],
			dayNamesShort: [app.vtranslate('JS_SUN'), app.vtranslate('JS_MON'), app.vtranslate('JS_TUE'),
				app.vtranslate('JS_WED'), app.vtranslate('JS_THU'), app.vtranslate('JS_FRI'),
				app.vtranslate('JS_SAT')],
		};
	}

	setBrowserHistoryConfig() {
		let historyParams = app.getMainParams('historyParams', true),
			options;
		if (historyParams !== '' && app.moduleCacheGet('browserHistoryEvent')) {
			options = {
				start: historyParams.start,
				end: historyParams.end,
				user: historyParams.user.split(",").map((x) => {
					return parseInt(x)
				}),
				time: historyParams.time,
				hiddenDays: historyParams.hiddenDays.split(",").map((x) => {
					return parseInt(x)
				}),
				cvid: historyParams.cvid,
				defaultView: historyParams.viewType
			};
			let s = moment(options.start).valueOf();
			let e = moment(options.end).valueOf();
			options.defaultDate = moment(moment(s + ((e - s) / 2)).format('YYYY-MM-DD'));
			Object.keys(options).forEach(key => options[key] === 'undefined' && delete options[key]);
			app.moduleCacheSet('browserHistoryEvent', false)
		} else {
			options = null;
		}
		window.addEventListener('popstate', function (event) {
			app.moduleCacheSet('browserHistoryEvent', true)
		}, false);
		return options;
	}

	eventRenderer(event, element) {
		//TODO:Write basci method
	}

	registerButtonSelectAll() {
		var selectBtn = $('.selectAllBtn');
		selectBtn.on('click', function (e) {
			var selectAllLabel = $(this).find('.selectAll');
			var deselectAllLabel = $(this).find('.deselectAll');
			if (selectAllLabel.hasClass('d-none')) {
				selectAllLabel.removeClass('d-none');
				deselectAllLabel.addClass('d-none');
				$(this).closest('.quickWidget').find('select option').prop("selected", false);
			} else {
				$(this).closest('.quickWidget').find('select option').prop("selected", true);
				deselectAllLabel.removeClass('d-none');
				selectAllLabel.addClass('d-none');
			}
			$(this).closest('.quickWidget').find('select').trigger("change");
		});
	}

	registerSelect2Event() {
		var thisInstance = this;
		$('.siteBarRight .select2').each(function (index) {
			var name = $(this).attr('id');
			var value = app.moduleCacheGet(name);
			var element = $('#' + name);
			if (element.length > 0 && value != null) {
				if (element.prop('tagName') == 'SELECT') {
					element.val(value);
				}
			}
		});
		$('.siteBarRight .select2, .siteBarRight .filterField').off('change');
		App.Fields.Picklist.showSelect2ElementView($('#calendarUserList'));
		App.Fields.Picklist.showSelect2ElementView($('#timecontrolTypes'));
		App.Fields.Picklist.showSelect2ElementView($('#calendarActivityTypeList'));
		$('.siteBarRight .select2, .siteBarRight .filterField').on('change', function () {
			var element = $(this);
			var value = element.val();
			if (value == null) {
				value = '';
			}
			thisInstance.loadCalendarData();
			if (element.attr('type') == 'checkbox') {
				value = element.is(':checked');
			}
			app.moduleCacheSet(element.attr('id'), value);
		});
	}

	loadCalendarData(allEvents) {
		var progressInstance = jQuery.progressIndicator();
		var thisInstance = this;
		thisInstance.getCalendarView().fullCalendar('removeEvents');
		var view = thisInstance.getCalendarView().fullCalendar('getView');
		var start_date = view.start.format();
		var end_date = view.end.format();
		var user;
		if (jQuery('#calendarUserList').length == 0) {
			user = CONFIG.userId;
		} else {
			user = jQuery('#calendarUserList').val();
		}
		if (jQuery('#timecontrolTypes').length > 0) {
			var types = jQuery('#timecontrolTypes').val();
		} else {
			allEvents = true;
		}

		if (allEvents == true || types != null) {
			var params = {
				module: CONFIG.module,
				action: 'Calendar',
				mode: 'getEvent',
				start: start_date,
				end: end_date,
				user: user,
				types: types
			};
			AppConnector.request(params).done(function (events) {
				thisInstance.getCalendarView().fullCalendar('addEventSource', events.result);
				thisInstance.registerSelect2Event();
				progressInstance.hide();
			});
		} else {
			thisInstance.getCalendarView().fullCalendar('removeEvents');
			progressInstance.hide();
		}
	}

	updateEvent(event, delta, revertFunc) {
		var progressInstance = jQuery.progressIndicator({blockInfo: {enabled: true}});
		var start = event.start.format();
		var params = {
			module: CONFIG.module,
			action: 'Calendar',
			mode: 'updateEvent',
			id: event.id,
			start: start,
			delta: delta._data,
			allDay: event.allDay
		};
		AppConnector.request(params).done(function (response) {
			if (!response['result']) {
				Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_EDIT_PERMISSION'));
				revertFunc();
			}
			progressInstance.progressIndicator({'mode': 'hide'});
		}).fail(function () {
			progressInstance.progressIndicator({'mode': 'hide'});
			Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_EDIT_PERMISSION'));
			revertFunc();
		});
	}

	addCalendarEvent(calendarDetails, dateFormat) {
		//TODO: Write basic method
	}

	getCalendarCreateView() {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();

		if (this.calendarCreateView !== false) {
			aDeferred.resolve(this.calendarCreateView.clone(true, true));
			return aDeferred.promise();
		}
		var progressInstance = jQuery.progressIndicator();
		this.loadCalendarCreateView().done(function (data) {
			progressInstance.hide();
			thisInstance.calendarCreateView = data;
			aDeferred.resolve(data.clone(true, true));
		}).fail(function () {
			progressInstance.hide();
		});
		return aDeferred.promise();
	}

	loadCalendarCreateView() {
		var aDeferred = jQuery.Deferred();
		var moduleName = app.getModuleName();
		var url = 'index.php?module=' + moduleName + '&view=QuickCreateAjax';
		var headerInstance = Vtiger_Header_Js.getInstance();
		headerInstance.getQuickCreateForm(url, moduleName).done(function (data) {
			aDeferred.resolve(jQuery(data));
		}).fail(function (textStatus, errorThrown) {
			aDeferred.reject(textStatus, errorThrown);
		});
		return aDeferred.promise();
	}

	getCalendarView() {
		if (this.calendarView == false) {
			this.calendarView = this.container.find('.js-calendar__container');
		}
		return this.calendarView;
	}

	registerChangeView() {
		var thisInstance = this;
		thisInstance.getCalendarView().find("button.fc-button:not(.dropdown-toggle)").on('click', function () {
			thisInstance.loadCalendarData();
		});
	}

	registerAddButton() {
		const self = this;
		$('.js-add').on('click', (e) => {
			self.getCalendarCreateView().done((data) => {
				const headerInstance = new Vtiger_Header_Js();
				headerInstance.handleQuickCreateData(data, {
					callbackFunction: (data) => {
						self.addCalendarEvent(data.result);
					}
				});
			});
		});
	}

	registerLoadCalendarData() {
		var thisInstance = this;
		var widgets = $('.siteBarRight .widgetContainer').length;
		$('.bodyContents').on('Vtiger.Widget.Load.undefined', function (e, data) {
			widgets -= 1;
			if (widgets == 0) {
				thisInstance.loadCalendarData(true);
			}
		});
	}

	registerEvents() {
		this.renderCalendar();
		this.registerLoadCalendarData();
		this.registerChangeView();
		this.registerButtonSelectAll();
		this.registerAddButton();
	}
}
