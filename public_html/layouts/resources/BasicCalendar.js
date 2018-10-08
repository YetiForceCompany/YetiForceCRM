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
		var thisInstance = this;
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
		var popoverTimeFormat;
		if (userDefaultTimeFormat == 24) {
			userDefaultTimeFormat = 'H:mm';
			popoverTimeFormat = 'HH:mm';
		} else {
			userDefaultTimeFormat = 'h:mmt';
			popoverTimeFormat = 'hh:mm A';
		}

		//Default first day of the week
		var convertedFirstDay = CONFIG.firstDayOfWeekNo;

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
			height: app.setCalendarHeight(),
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
				app.showPopoverElementView(element.find('.fc-content'), {
					title: event.title + '<a href="index.php?module=Reservations&view=Edit&record=' + event.id + '" class="float-right"><span class="fas fa-edit"></span></a>' + '<a href="index.php?module=Reservations&view=Detail&record=' + event.id + '" class="float-right mx-1"><span class="fas fa-th-list"></span></a>',
					container: 'body',
					html: true,
					placement: 'auto',
					template: '<div class="popover calendarPopover" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
					content: '<div><span class="far fa-clock"></span> <label>' + app.vtranslate('JS_START_DATE') + '</label>: ' + event.start.format('YYYY-MM-DD ' + popoverTimeFormat) + '</div>' +
						'<div><span class="far fa-clock"></span> <label>' + app.vtranslate('JS_END_DATE') + '</label>: ' + event.end.format('YYYY-MM-DD ' + popoverTimeFormat) + '</div>' +
						'<div><span class="far fa-clock"></span> <label>' + app.vtranslate('JS_TOTAL_TIME') + '</label>: ' + event.totalTime + '</div>' +
						'<div><span class="fas fa-question-circle"></span> <label>' + app.vtranslate('JS_TYPE') + '</label>: ' + event.type + '</div>' +
						(event.status ? '<div><span class="far fa-star"></span> <label>' + app.vtranslate('JS_STATUS') + '</label>: <span class="picklistCT_Reservations_reservations_status_' + event.status + '">' + app.vtranslate(event.status) + '</span></div>' : '') +
						(event.company ? '<div><span class="userIcon-Accounts" aria-hidden="true"></span> <label>' + app.vtranslate('JS_COMPANY') + '</label>: <span class="modCT_Accounts">' + event.company + '</span></div>' : '') +
						(event.process ? '<div><span class="userIcon-' + event.processType + '" aria-hidden="true"></span> <label>' + event.processLabel + '</label>: <a class="modCT_' + event.processType + '" target="_blank" href="index.php?module=' + event.processType + '&view=Detail&record=' + event.processId + '">' + event.process + '</a></div>' : '') +
						(event.smownerid ? '<div><span class="fas fa-user"></span> <label>' + app.vtranslate('JS_ASSIGNED_TO') + '</label>: ' + event.smownerid + '</div>' : '')
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
				module: 'Reservations',
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
			module: 'Reservations',
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
		if ($("#calendarUserList").val().length && $.inArray(calendarDetails.assigned_user_id.value, $("#calendarUserList").val()) < 0) {
			return;
		}
		if ($.inArray(calendarDetails.type.value, $("#timecontrolTypes").val()) < 0) {
			return;
		}
		var calendar = this.getCalendarView();
		var startDate = calendar.fullCalendar('moment', calendarDetails.date_start.value + ' ' + calendarDetails.time_start.value);
		var endDate = calendar.fullCalendar('moment', calendarDetails.due_date.value + ' ' + calendarDetails.time_end.value);
		var eventObject = {
			id: calendarDetails._recordId,
			title: calendarDetails.title.display_value,
			smownerid: calendarDetails.assigned_user_id ? calendarDetails.assigned_user_id.display_value : calendarDetails.smownerid,
			status: calendarDetails.reservations_status ? calendarDetails.reservations_status.display_value : calendarDetails.status,
			isPrivate: calendarDetails.isPrivate,
			start: startDate.toString(),
			end: endDate.toString(),
			url: 'index.php?module=Reservations&view=Detail&record=' + calendarDetails._recordId,
			className: 'ownerCBg_' + calendarDetails.assigned_user_id.value + ' picklistCBg_OSSTimeControl_timecontrol_type_' + calendarDetails.type.value,
			totalTime: calendarDetails.sum_time.display_value,
			type: calendarDetails.type.display_value,
		};
		this.getCalendarView().fullCalendar('renderEvent', eventObject);
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

