/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery.Class("Reservations_Calendar_Js", {
	registerUserListWidget: function () {
		var thisInstance = new Reservations_Calendar_Js();
		var widgetContainer = $('.widgetContainer');
		widgetContainer.hover(
				function () {
					$(this).css('overflow', 'visible');
				}, function () {
			$(this).css('overflow', 'hidden');
		}
		);
		this.registerColorField(widgetContainer.find('#calendarUserList'), 'userCol');
		this.registerColorField(widgetContainer.find('#timecontrolTypes'), 'listCol');
		widgetContainer.find('.select2').on('change', function () {
			$(this).closest('.siteBarContent').find('.refreshHeader').removeClass('hide');
		});
	},
	registerColorField: function (field, fieldClass) {
		var params = {};
		params.dropdownCss = {'z-index': 0};
		params.formatSelection = function (object, container) {
			var selectedId = object.id;
			var selectedOptionTag = field.find('option[value="' + selectedId + '"]');
			container.addClass(fieldClass + '_' + selectedId);
			var element = '<div>' + selectedOptionTag.text() + '</div>';
			return element;
		}
		app.changeSelectElementView(field, 'select2', params);
	},
}, {
	calendarView: false,
	calendarCreateView: false,
	weekDaysArray: {Sunday: 0, Monday: 1, Tuesday: 2, Wednesday: 3, Thursday: 4, Friday: 5, Saturday: 6},
	registerCalendar: function () {
		var thisInstance = this;
		var eventLimit = jQuery('#eventLimit').val();
		if (eventLimit == 'true') {
			eventLimit = true;
		}
		else if (eventLimit == 'false') {
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
		var popoverTimeFormat;
		if (userDefaultTimeFormat == 24) {
			userDefaultTimeFormat = 'H:mm';
			popoverTimeFormat = 'HH:mm';
		} else {
			userDefaultTimeFormat = 'h:mmt';
			popoverTimeFormat = 'hh:mm A';
		}

		//Default first day of the week
		var defaultFirstDay = jQuery('#start_day').val();
		var convertedFirstDay = thisInstance.weekDaysArray[defaultFirstDay];

		//Default first hour of the day
		var defaultFirstHour = jQuery('#start_hour').val();
		var explodedTime = defaultFirstHour.split(':');
		defaultFirstHour = explodedTime['0'];

		thisInstance.getCalendarView().fullCalendar({
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
			views: {
				basic: {
					eventLimit: false,
				}
			},
			dayClick: function (date, jsEvent, view) {
				thisInstance.selectDay(date.format());
				thisInstance.getCalendarView().fullCalendar('unselect');
			},
			eventDrop: function (event, delta, revertFunc) {
				thisInstance.updateEvent(event, delta, revertFunc);
			},
			eventResize: function (event, delta, revertFunc) {
				thisInstance.updateEvent(event, delta, revertFunc);
			},
			eventRender: function (event, element) {
				element.find('.fc-content').popover({
					title: event.title,
					placement: 'auto right',
					html: true,
					trigger: 'hover',
					delay: 500,
					container: 'body',
					content: '<i class="icon-time"></i> ' + app.vtranslate('JS_START_DATE') + ': ' + event.start.format('YYYY-MM-DD ' + popoverTimeFormat) + '<br /><i class="icon-time"></i> ' + app.vtranslate('JS_END_DATE') + ': ' + event.end.format('YYYY-MM-DD ' + popoverTimeFormat)
				});
			},
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
	},
	registerButtonSelectAll: function () {
		var selectBtn = $('.selectAllBtn');
		selectBtn.click(function (e) {
			var selectAllLabel = $(this).find('.selectAll');
			var deselectAllLabel = $(this).find('.deselectAll');
			if (selectAllLabel.hasClass('hide')) {
				selectAllLabel.removeClass('hide');
				deselectAllLabel.addClass('hide');
				$(this).closest('.quickWidget').find('select option').prop("selected", false);
			}
			else {
				$(this).closest('.quickWidget').find('select option').prop("selected", true);
				deselectAllLabel.removeClass('hide');
				selectAllLabel.addClass('hide');
			}
			$(this).closest('.quickWidget').find('select').trigger("change");
		});
	},
	loadCalendarData: function (allEvents) {
		var progressInstance = jQuery.progressIndicator();
		var thisInstance = this;
		thisInstance.getCalendarView().fullCalendar('removeEvents');
		var view = thisInstance.getCalendarView().fullCalendar('getView');
		var start_date = view.start.format();
		var end_date = view.end.format();
		if (jQuery('#calendarUserList').length == 0) {
			var user = jQuery('#current_user_id').val();
		} else {
			var user = jQuery('#calendarUserList').val();
		}
		if (jQuery('#timecontrolTypes').length > 0) {
			var types = jQuery('#timecontrolTypes').val();
		} else {
			allEvents = true;
		}

		if (allEvents == true || types != null) {
			var params = {
				module: 'Reservations',
				action: 'Calendar',
				mode: 'getEvent',
				start: start_date,
				end: end_date,
				user: user,
				types: types
			}
			AppConnector.request(params).then(function (events) {
				thisInstance.getCalendarView().fullCalendar('addEventSource', events.result);
				progressInstance.hide();
			});
		} else {
			thisInstance.getCalendarView().fullCalendar('removeEvents');
			progressInstance.hide();
		}
	},
	updateEvent: function (event, delta, revertFunc) {
		var progressInstance = jQuery.progressIndicator();
		var start = event.start.format();
		var end = event.end.format();
		var params = {
			module: 'Reservations',
			action: 'Calendar',
			mode: 'updateEvent',
			id: event.id,
			start: start,
			delta: delta._data
		}
		AppConnector.request(params).then(function (response) {
			if (!response['result']) {
				Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_EDIT_PERMISSION'));
				revertFunc();
			}
			progressInstance.hide();
		},
				function (error) {
					progressInstance.hide();
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_EDIT_PERMISSION'));
					revertFunc();
				});
	},
	selectDay: function (date) {
		var thisInstance = this;
		thisInstance.getCalendarCreateView().then(function (data) {
			if (data.length <= 0) {
				return;
			}
			var dateFormat = data.find('[name="date_start"]').data('dateFormat');
			var timeFormat = data.find('[name="time_start"]').data('format');
			if (timeFormat == 24) {
				var defaultTimeFormat = 'HH:mm';
			} else {
				defaultTimeFormat = 'hh:mm tt';
			}
			var startDateInstance = Date.parse(date);
			var startDateString = app.getDateInVtigerFormat(dateFormat, startDateInstance);
			var startTimeString = startDateInstance.toString(defaultTimeFormat);
			var endDateInstance = Date.parse(date);
			var endDateString = app.getDateInVtigerFormat(dateFormat, endDateInstance);


			var view = thisInstance.getCalendarView().fullCalendar('getView');
			if ('month' == view.name) {
				var diffDays = parseInt((endDateInstance - startDateInstance) / (1000 * 60 * 60 * 24));
				if (diffDays > 1) {
					var defaultFirstHour = jQuery('#start_hour').val();
					var explodedTime = defaultFirstHour.split(':');
					startTimeString = explodedTime['0'];

					var defaultLastHour = jQuery('#end_hour').val();
					var explodedTime = defaultLastHour.split(':');
					endTimeString = explodedTime['0'];
				} else {
					var now = new Date();
					var startTimeString = now.toString(defaultTimeFormat);
					now.setMinutes(now.getMinutes() + 15);
					var endTimeString = now.toString(defaultTimeFormat);
				}
			} else {
				endDateInstance.setMinutes(endDateInstance.getMinutes() + 30);
				var endTimeString = endDateInstance.toString(defaultTimeFormat);
			}

			data.find('[name="date_start"]').val(startDateString);
			data.find('[name="due_date"]').val(endDateString);
			data.find('[name="time_start"]').val(startTimeString);
			data.find('[name="time_end"]').val(endTimeString);

			var headerInstance = new Vtiger_Header_Js();
			headerInstance.handleQuickCreateData(data, {callbackFunction: function (data) {
					thisInstance.addCalendarEvent(data.result, dateFormat);
				}});
			jQuery('.modal-body').css({'max-height': app.getScreenHeight(70) + 'px', 'overflow-y': 'auto'});
		});
	},
	addCalendarEvent: function (calendarDetails, dateFormat) {
		// convert dates to db format
		calendarDetails.date_start.display_value = app.getDateInDBInsertFormat(dateFormat, calendarDetails.date_start.display_value);
		calendarDetails.due_date.display_value = app.getDateInDBInsertFormat(dateFormat, calendarDetails.due_date.display_value);
		var calendar = this.getCalendarView();

		var eventObject = {};
		eventObject.id = calendarDetails._recordId;
		eventObject.title = calendarDetails.title.display_value;
		var startDate = calendar.fullCalendar('moment', calendarDetails.date_start.display_value + ' ' + calendarDetails.time_start.display_value);
		eventObject.start = startDate.toString();
		var endDate = calendar.fullCalendar('moment', calendarDetails.due_date.display_value + ' ' + calendarDetails.time_end.display_value);
		var assignedUserId = calendarDetails.assigned_user_id.value;
		eventObject.end = endDate.toString();
		eventObject.url = 'index.php?module=Reservations&view=Detail&record=' + calendarDetails._recordId;
		eventObject.className = 'userCol_' + calendarDetails.assigned_user_id.value + ' calCol_' + calendarDetails.type.value;
		this.getCalendarView().fullCalendar('renderEvent', eventObject);
	},
	getCalendarCreateView: function () {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();

		if (this.calendarCreateView !== false) {
			aDeferred.resolve(this.calendarCreateView.clone(true, true));
			return aDeferred.promise();
		}
		var progressInstance = jQuery.progressIndicator();
		this.loadCalendarCreateView().then(
				function (data) {
					progressInstance.hide();
					thisInstance.calendarCreateView = data;
					aDeferred.resolve(data.clone(true, true));
				},
				function () {
					progressInstance.hide();
				}
		);
		return aDeferred.promise();
	},
	registerRefreshEvent: function () {
		var thisInstance = this;
		$(".refreshCalendar").click(function () {
			$(this).closest('.refreshHeader').addClass('hide');
			thisInstance.loadCalendarData();
		});
	},
	loadCalendarCreateView: function () {
		var aDeferred = jQuery.Deferred();
		var moduleName = app.getModuleName();
		var url = 'index.php?module=' + moduleName + '&view=QuickCreateAjax';
		var headerInstance = Vtiger_Header_Js.getInstance();
		headerInstance.getQuickCreateForm(url, moduleName).then(
				function (data) {
					aDeferred.resolve(jQuery(data));
				},
				function () {
					aDeferred.reject();
				}
		);
		return aDeferred.promise();
	},
	getCalendarView: function () {
		if (this.calendarView == false) {
			this.calendarView = jQuery('#calendarview');
		}
		return this.calendarView;
	},
	registerChangeView: function () {
		var thisInstance = this;
		thisInstance.getCalendarView().find("button.fc-button:not(.dropdown-toggle)").click(function () {
			thisInstance.loadCalendarData();
		});
	},
	registerCalendarScroll: function () {
		var calendarContainer = $('.bodyContents');
		app.showScrollBar(calendarContainer, {
			railVisible: true,
			alwaysVisible: true,
			position: 'left'
		});
	},
	registerEvents: function () {
		this.registerCalendar();
		this.loadCalendarData(true);
		this.registerChangeView();
		this.registerButtonSelectAll();
		this.registerRefreshEvent();
		this.registerCalendarScroll();
	}
});
