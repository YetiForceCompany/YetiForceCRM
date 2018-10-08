/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

window.BasicCalendar_Js = class BasicCalendar_Js {

	constructor() {
		this.calendarView = false;
		this.calendarCreateView = false;
	}

	getInstanceByView(view) {
		if (typeof view === 'undefined') {
			view = $('#currentView').val();
		}
		var moduleClassName = "Calendar_" + view + "_Js";
		var instance;
		if (typeof window[moduleClassName] !== "undefined") {
			instance = new window[moduleClassName]();
		} else {
			instance = new Calendar_Calendar_Js();
		}
		return instance;
	}

	registerColorField(field, fieldClass) {
		var params = {};
		params.dropdownCss = {'z-index': 0};
		params.formatSelection = function (object, container) {
			var selectedId = object.id;
			var selectedOptionTag = field.find('option[value="' + selectedId + '"]');
			container.addClass(fieldClass + '_' + selectedId);
			var element = '<div>' + selectedOptionTag.text() + '</div>';
			return element;
		};
		App.Fields.Picklist.changeSelectElementView(field, 'select2', params);
	}

	registerCalendar() {
		var self = this;
		var eventLimit = jQuery('#eventLimit').val();
		if (eventLimit == 'true') {
			eventLimit = true;
		} else if (eventLimit == 'false') {
			eventLimit = false;
		} else {
			eventLimit = parseInt(eventLimit) + 1;
		}
		var weekView = jQuery('#weekView').val();
		var dayView = jQuery('#dayView').val();

		//User preferred default view
		var userDefaultActivityView = jQuery('#activity_view').val();
		if (userDefaultActivityView == 'Today') {
			userDefaultActivityView = dayView;
		} else if (userDefaultActivityView == 'This Week') {
			userDefaultActivityView = weekView;
		} else {
			userDefaultActivityView = 'month';
		}

		//Default time format
		var userDefaultTimeFormat = jQuery('#time_format').val();
		if (userDefaultTimeFormat == 24) {
			userDefaultTimeFormat = 'H:mm';
		} else {
			userDefaultTimeFormat = 'h:mmt';
		}
		//Default first day of the week
		var convertedFirstDay = CONFIG.firstDayOfWeekNo;

		//Default first hour of the day
		var defaultFirstHour = jQuery('#start_hour').val();
		var explodedTime = defaultFirstHour.split(':');
		defaultFirstHour = explodedTime['0'];

		self.getCalendarView().fullCalendar({
			header: {
				left: 'month,' + weekView + ',' + dayView,
				center: 'title today',
				right: 'prev,next'
			},
			timeFormat: userDefaultTimeFormat,
			axisFormat: userDefaultTimeFormat,
			firstHour: defaultFirstHour,
			firstDay: convertedFirstDay,
			defaultView: userDefaultActivityView,
			editable: true,
			slotMinutes: 15,
			defaultEventMinutes: 0,
			forceEventDuration: true,
			defaultTimedEventDuration: '01:00:00',
			eventLimit: eventLimit,
			allDaySlot: false,
			height: app.setCalendarHeight(),
			views: {
				basic: {
					eventLimit: false,
				}
			},
			dayClick: function (date, jsEvent, view) {
				self.selectDay(date.format());
				self.getCalendarView().fullCalendar('unselect');
			},
			eventDrop: function (event, delta, revertFunc) {
				self.updateEvent(event, delta, revertFunc);
			},
			eventResize: function (event, delta, revertFunc) {
				self.updateEvent(event, delta, revertFunc);
			},
			eventRender: self.eventRenderer.bind(self),
			monthNames: [app.vtranslate('JS_JANUARY'), app.vtranslate('JS_FEBRUARY'), app.vtranslate('JS_MARCH'),
				app.vtranslate('JS_APRIL'), app.vtranslate('JS_MAY'), app.vtranslate('JS_JUNE'), app.vtranslate('JS_JULY'),
				app.vtranslate('JS_AUGUST'), app.vtranslate('JS_SEPTEMBER'), app.vtranslate('JS_OCTOBER'),
				app.vtranslate('JS_NOVEMBER'), app.vtranslate('JS_DECEMBER')],
			monthNamesShort: [app.vtranslate('JS_JAN'), app.vtranslate('JS_FEB'), app.vtranslate('JS_MAR'),
				app.vtranslate('JS_APR'), app.vtranslate('JS_MAY'), app.vtranslate('JS_JUN'), app.vtranslate('JS_JUL'),
				app.vtranslate('JS_AUG'), app.vtranslate('JS_SEP'), app.vtranslate('JS_OCT'), app.vtranslate('JS_NOV'),
				app.vtranslate('JS_DEC')],
			dayNames: [app.vtranslate('JS_SUNDAY'), app.vtranslate('JS_MONDAY'), app.vtranslate('JS_TUESDAY'),
				app.vtranslate('JS_WEDNESDAY'), app.vtranslate('JS_THURSDAY'), app.vtranslate('JS_FRIDAY'),
				app.vtranslate('JS_SATURDAY')],
			dayNamesShort: [app.vtranslate('JS_SUN'), app.vtranslate('JS_MON'), app.vtranslate('JS_TUE'),
				app.vtranslate('JS_WED'), app.vtranslate('JS_THU'), app.vtranslate('JS_FRI'),
				app.vtranslate('JS_SAT')],
			buttonText: {
				today: app.vtranslate('JS_TODAY'),
				month: app.vtranslate('JS_MONTH'),
				week: app.vtranslate('JS_WEEK'),
				day: app.vtranslate('JS_DAY')
			},
			allDayText: app.vtranslate('JS_ALL_DAY'),
			eventLimitText: app.vtranslate('JS_MORE')
		});
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
		var progressInstance = jQuery.progressIndicator();
		var start = event.start.format();
		var end = event.end.format();
		var params = {
			module: CONFIG.module,
			action: 'Calendar',
			mode: 'updateEvent',
			id: event.id,
			start: start,
			delta: delta._data
		};
		AppConnector.request(params).done(function (response) {
			if (!response['result']) {
				Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_EDIT_PERMISSION'));
				revertFunc();
			}
			progressInstance.hide();
		}).fail(function () {
			progressInstance.hide();
			Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_EDIT_PERMISSION'));
			revertFunc();
		});
	}

	selectDay(date) {
		var thisInstance = this;

		thisInstance.getCalendarCreateView().done(function (data) {
			if (data.length <= 0) {
				return;
			}
			var dateFormat = data.find('[name="date_start"]').data('dateFormat').toUpperCase();
			var timeFormat = data.find('[name="time_start"]').data('format');
			if (timeFormat == 24) {
				var defaultTimeFormat = 'HH:mm';
			} else {
				defaultTimeFormat = 'hh:mm A';
			}
			var startDateInstance = Date.parse(date);
			var startDateString = moment(date).format(dateFormat);
			var startTimeString = moment(date).format(defaultTimeFormat);
			var endDateInstance = Date.parse(date);
			var endDateString = moment(date).format(dateFormat);

			var view = thisInstance.getCalendarView().fullCalendar('getView');
			var endTimeString;
			if ('month' == view.name) {
				var diffDays = parseInt((endDateInstance - startDateInstance) / (1000 * 60 * 60 * 24));
				if (diffDays > 1) {
					var defaultFirstHour = jQuery('#start_hour').val();
					var explodedTime = defaultFirstHour.split(':');
					startTimeString = explodedTime['0'];
					var defaultLastHour = jQuery('#end_hour').val();
					explodedTime = defaultLastHour.split(':');
					endTimeString = explodedTime['0'];
				} else {
					var now = new Date();
					startTimeString = moment(now).format(defaultTimeFormat);
					endTimeString = moment(now).add(15, 'minutes').format(defaultTimeFormat);
				}
			} else {
				endTimeString = moment(endDateInstance).add(30, 'minutes').format(defaultTimeFormat);
			}
			data.find('[name="date_start"]').val(startDateString);
			data.find('[name="due_date"]').val(endDateString);
			data.find('[name="time_start"]').val(startTimeString);
			data.find('[name="time_end"]').val(endTimeString);

			var headerInstance = new Vtiger_Header_Js();
			headerInstance.handleQuickCreateData(data, {
				callbackFunction(data) {
					thisInstance.addCalendarEvent(data.result, dateFormat);
				}
			});
			jQuery('.modal-body').css({'max-height': app.getScreenHeight(70) + 'px', 'overflow-y': 'auto'});
		});
	}

	addCalendarEvent(calendarDetails, dateFormat) {
		//TODO:Write basci method
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

	registerRefreshEvent() {
		var thisInstance = this;
		$(".refreshCalendar").on('click', function () {
			$(this).closest('.refreshHeader').addClass('d-none');
			thisInstance.loadCalendarData();
		});
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
			this.calendarView = jQuery('.js-calendar__container');
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
		this.registerCalendar();
		this.registerLoadCalendarData();
		this.registerChangeView();
		this.registerButtonSelectAll();
		this.registerRefreshEvent();
		this.registerAddButton();
	}
}

