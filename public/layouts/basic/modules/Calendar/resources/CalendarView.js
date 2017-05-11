/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
jQuery.Class("Calendar_CalendarView_Js", {
	currentInstance: false,
	getInstanceByView: function () {
		var view = jQuery('#currentView').val();
		var jsFileName = view + 'View';
		var moduleClassName = view + "_" + jsFileName + "_Js";
		var instance;
		if (typeof window[moduleClassName] != 'undefined') {
			instance = new window[moduleClassName]();
		} else {
			instance = new Calendar_CalendarView_Js();
		}
		return instance;
	},
	registerSwitches: function () {
		var widgetContainer = jQuery('#rightPanel .quickWidget');
		var switchesContainer = widgetContainer.find('.widgetContainer input.switchBtn');
		app.showBtnSwitch(switchesContainer);
		widgetContainer.find('.widgetContainer').each(function () {
			var h = jQuery(this).height();
			if (h > 250) {
				jQuery(this).find('div:first').slimScroll({
					height: '250px'
				});
			}
		});
	},
	registerWidget: function () {
		var thisInstance = this.getInstanceByView();
		var widgetContainer = jQuery('#rightPanel .quickWidget');
		widgetContainer.find('.switchsParent').on('switchChange.bootstrapSwitch', function (event, state) {
			var element = jQuery(this).closest('.quickWidget');
			if (state) {
				element.find('.widgetContainer input.switchBtn').bootstrapSwitch('state', true);
			} else {
				element.find('.widgetContainer input.switchBtn').bootstrapSwitch('state', false);
			}
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
		};
		app.changeSelectElementView(field, 'select2', params);
	}
}, {
	calendarView: false,
	calendarCreateView: false,
	weekDaysArray: {Sunday: 0, Monday: 1, Tuesday: 2, Wednesday: 3, Thursday: 4, Friday: 5, Saturday: 6},
	renderCalendar: function () {
		var thisInstance = this;

		var eventLimit = app.getMainParams('eventLimit');
		if (eventLimit == 'true') {
			eventLimit = true;
		} else if (eventLimit == 'false') {
			eventLimit = false;
		} else {
			eventLimit = parseInt(eventLimit) + 1;
		}
		var weekView = app.getMainParams('weekView');
		var dayView = app.getMainParams('dayView');

		//User preferred default view
		var userDefaultActivityView = app.getMainParams('activity_view');
		if (userDefaultActivityView == 'Today') {
			userDefaultActivityView = dayView;
		} else if (userDefaultActivityView == 'This Week') {
			userDefaultActivityView = weekView;
		} else {
			userDefaultActivityView = 'month';
		}
		var defaultView = app.moduleCacheGet('defaultView');
		if (defaultView != null) {
			userDefaultActivityView = defaultView;
		}

		//Default time format
		var userDefaultTimeFormat = app.getMainParams('time_format');
		if (userDefaultTimeFormat == 24) {
			userDefaultTimeFormat = 'H:mm';
		} else {
			userDefaultTimeFormat = 'h:mmt';
		}

		//Default first day of the week
		var defaultFirstDay = app.getMainParams('start_day');
		var convertedFirstDay = thisInstance.weekDaysArray[defaultFirstDay];

		//Default first hour of the day
		var defaultFirstHour = app.getMainParams('start_hour') + ':00';
		var hiddenDays = [];
		if (app.getMainParams('switchingDays') == 'workDays') {
			hiddenDays = app.getMainParams('hiddenDays', true);
		}
		var options = {
			header: {
				left: 'month,' + weekView + ',' + dayView,
				center: 'title today',
				right: 'prev,next'
			},
			timeFormat: userDefaultTimeFormat,
			axisFormat: userDefaultTimeFormat,
			scrollTime: defaultFirstHour,
			firstDay: convertedFirstDay,
			defaultView: userDefaultActivityView,
			editable: true,
			slotMinutes: 15,
			defaultEventMinutes: 0,
			forceEventDuration: true,
			defaultTimedEventDuration: '01:00:00',
			eventLimit: eventLimit,
			selectable: true,
			selectHelper: true,
			hiddenDays: hiddenDays,
			views: {
				basic: {
					eventLimit: false,
				}
			},
			select: function (start, end) {
				thisInstance.selectDays(start, end);
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
					trigger: 'hover',
					delay: 500,
					title: event.title,
					container: 'body',
					html: true,
					placement: 'auto right',
					template: '<div class="popover calendarPopover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
					content: '<div><span class="glyphicon glyphicon-time" aria-hidden="true"></span> <label>' + app.vtranslate('JS_START_DATE') + '</label>: ' + event.start_display + '</div>' +
							'<div><span class="glyphicon glyphicon-time" aria-hidden="true"></span> <label>' + app.vtranslate('JS_END_DATE') + '</label>: ' + event.end_display + '</div>' +
							(event.lok ? '<div><span class="glyphicon glyphicon-globe" aria-hidden="true"></span> <label>' + app.vtranslate('JS_LOCATION') + '</label>: ' + event.lok + '</div>' : '') +
							(event.pri ? '<div><span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span> <label>' + app.vtranslate('JS_PRIORITY') + '</label>: ' + app.vtranslate('JS_' + event.pri) + '</div>' : '') +
							'<div><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span> <label>' + app.vtranslate('JS_STATUS') + '</label>: ' + app.vtranslate('JS_' + event.sta) + '</div>' +
							(event.accname ? '<div><span class="calIcon modIcon_Accounts" aria-hidden="true"></span> <label>' + app.vtranslate('JS_ACCOUNTS') + '</label>: ' + event.accname + '</div>' : '') +
							(event.linkl ? '<div><span class="userIcon-' + event.linkm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_RELATION') + '</label>: ' + event.linkl + '</div>' : '') +
							(event.procl ? '<div><span class="userIcon-' + event.procm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_PROCESS') + '</label>: ' + event.procl + '</div>' : '') +
							(event.subprocl ? '<div><span class="userIcon-' + event.subprocm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_SUB_PROCESS') + '</label>: ' + event.subprocl + '</div>' : '') +
							(event.state ? '<div><span class="glyphicon glyphicon-star-empty" aria-hidden="true"></span> <label>' + app.vtranslate('JS_STATE') + '</label>: ' + app.vtranslate(event.state) + '</div>' : '') +
							'<div><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> <label>' + app.vtranslate('JS_VISIBILITY') + '</label>: ' + app.vtranslate('JS_' + event.vis) + '</div>' +
							(event.smownerid ? '<div><span class="glyphicon glyphicon-user" aria-hidden="true"></span> <label>' + app.vtranslate('JS_ASSIGNED_TO') + '</label>: ' + event.smownerid + '</div>' : '')
				});
				element.find('.fc-content, .fc-info').click(function () {
					var progressInstance = jQuery.progressIndicator({blockInfo: {enabled: true}});
					var event = $(this).closest('.fc-event');
					var url = 'index.php?module=Calendar&view=ActivityStateModal&record=' + event.data('id');
					var callbackFunction = function (data) {
						progressInstance.progressIndicator({mode: 'hide'});
					};
					var modalWindowParams = {
						url: url,
						cb: callbackFunction
					};
					app.showModalWindow(modalWindowParams);
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
		};

		if (app.moduleCacheGet('start') != null) {
			var s = moment(app.moduleCacheGet('start')).valueOf();
			var e = moment(app.moduleCacheGet('end')).valueOf();
			options.defaultDate = moment(moment(s + ((e - s) / 2)).format('YYYY-MM-DD'));
		}

		thisInstance.getCalendarView().fullCalendar('destroy');
		thisInstance.getCalendarView().fullCalendar(options);
		thisInstance.createAddSwitch();
		thisInstance.registerCalendarScroll();
	},
	registerCalendarScroll: function () {
		var calendarContainer = $('.bodyContents');
		app.showScrollBar(calendarContainer, {
			railVisible: true,
			alwaysVisible: true,
			position: 'left'
		});
	},
	getValuesFromSelect2: function (element, data, text) {
		if (element.hasClass('select2-hidden-accessible')) {
			var types = (element.select2('data'));
			for (var i = 0; i < types.length; i++) {
				if (text) {
					data.push(types[i].text);
				} else {
					data.push(types[i].id);
				}
			}
		}
		return data;
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
			} else {
				$(this).closest('.quickWidget').find('select option').prop("selected", true);
				deselectAllLabel.removeClass('hide');
				selectAllLabel.addClass('hide');
			}
			$(this).closest('.quickWidget').find('select').trigger("change");
		});
	},
	loadCalendarData: function (allEvents) {
		var progressInstance = jQuery.progressIndicator({blockInfo: {enabled: true}});
		var thisInstance = this;
		thisInstance.getCalendarView().fullCalendar('removeEvents');
		var view = thisInstance.getCalendarView().fullCalendar('getView');
		var start_date = view.start.format();
		var end_date = view.end.format();
		var types = [];
		var formatDate = app.getMainParams('userDateFormat');
		types = thisInstance.getValuesFromSelect2($("#calendarActivityTypeList"), types);
		if (types.length == 0) {
			allEvents = true;
		}
		var user = [];
		user = thisInstance.getValuesFromSelect2($("#calendarUserList"), user);
		if (user.length == 0) {
			user = [app.getMainParams('current_user_id')];
		}
		user = thisInstance.getValuesFromSelect2($("#calendarGroupList"), user);
		var filters = [];
		$(".calendarFilters .filterField").each(function (index) {
			var element = $(this);
			var name, value;
			if (element.attr('type') == 'checkbox') {
				name = element.val();
				value = element.prop('checked') ? 1 : 0;
			} else {
				name = element.attr('name');
				value = element.val();
			}
			filters.push({name: name, value: value});
		});
		if (allEvents == true || types.length > 0) {
			var params = {
				module: 'Calendar',
				action: 'Calendar',
				mode: 'getEvents',
				start: app.getDateInVtigerFormat(formatDate, Date.parse(start_date)),
				end: app.getDateInVtigerFormat(formatDate, Date.parse(end_date)),
				user: user,
				time: app.getMainParams('showType'),
				types: types,
				filters: filters
			};
			AppConnector.request(params).then(function (events) {
				thisInstance.getCalendarView().fullCalendar('addEventSource', events.result);
				progressInstance.progressIndicator({mode: 'hide'});
			});
		} else {
			thisInstance.getCalendarView().fullCalendar('removeEvents');
			progressInstance.progressIndicator({mode: 'hide'});
		}
	},
	updateEvent: function (event, delta, revertFunc) {
		var progressInstance = jQuery.progressIndicator({blockInfo: {enabled: true}});
		var start = event.start.format();
		var params = {
			module: 'Calendar',
			action: 'Calendar',
			mode: 'updateEvent',
			id: event.id,
			start: start,
			delta: delta._data,
			allDay: event.allDay
		};
		AppConnector.request(params).then(function (response) {
			progressInstance.progressIndicator({mode: 'hide'});
			if (!response['result']) {
				Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_EDIT_PERMISSION'));
				revertFunc();
			}
		},
				function (error) {
					progressInstance.progressIndicator({mode: 'hide'});
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_EDIT_PERMISSION'));
					revertFunc();
				});
	},
	selectDays: function (startDate, endDate) {
		var thisInstance = this;
		var start_hour = $('#start_hour').val();
		var end_hour = $('#end_hour').val();
		if (endDate.hasTime() == false) {
			endDate.add(-1, 'days');
		}
		startDate = startDate.format();
		endDate = endDate.format();
		var view = thisInstance.getCalendarView().fullCalendar('getView');
		if (start_hour == '') {
			start_hour = '00';
		}
		if (end_hour == '') {
			end_hour = '00';
		}
		this.getCalendarCreateView().then(function (data) {
			if (data.length <= 0) {
				return;
			}
			if (view.name != 'agendaDay' && view.name != 'agendaWeek') {
				if (startDate == endDate) {
					var defaulDuration = 0;
					if (data.find('[name="activitytype"]').val() == 'Call') {
						defaulDuration = data.find('[name="defaultCallDuration"]').val();
					} else {
						defaulDuration = data.find('[name="defaultOtherEventDuration"]').val();
					}
					var startDateObject = Date.parse(start_hour);
					var endDateObject = startDateObject.addMinutes(defaulDuration);
					end_hour = endDateObject.toString('HH:mm');
				}
				startDate = startDate + 'T' + start_hour + ':00';
				endDate = endDate + 'T' + end_hour + ':00';
			}
			var dateFormat = data.find('[name="date_start"]').data('dateFormat');
			var timeFormat = data.find('[name="time_start"]').data('format');
			if (timeFormat == 24) {
				var defaultTimeFormat = 'HH:mm';
			} else {
				defaultTimeFormat = 'hh:mm tt';
			}

			var startDateInstance = Date.parse(startDate);
			var startDateString = app.getDateInVtigerFormat(dateFormat, startDateInstance);
			var startTimeString = startDateInstance.toString(defaultTimeFormat);
			var endDateInstance = Date.parse(endDate);
			var endDateString = app.getDateInVtigerFormat(dateFormat, endDateInstance);
			var endTimeString = endDateInstance.toString(defaultTimeFormat);

			data.find('[name="date_start"]').val(startDateString);
			data.find('[name="due_date"]').val(endDateString);
			data.find('[name="time_start"]').val(startTimeString);
			data.find('[name="time_end"]').val(endTimeString);

			var headerInstance = new Vtiger_Header_Js();
			headerInstance.handleQuickCreateData(data, {callbackFunction: function (data) {
					thisInstance.addCalendarEvent(data.result);
				}});
			jQuery('.modal-body').css({'max-height': app.getScreenHeight(70) + 'px', 'overflow-y': 'auto'});
		});
	},
	addCalendarEvent: function (calendarDetails) {
		var state = $('.fc-toolbar input.switchBtn').bootstrapSwitch('state');
		var eventObject = {};
		var calendar = this.getCalendarView();

		var taskstatus = $.inArray(calendarDetails.activitystatus.value, ['PLL_POSTPONED', 'PLL_CANCELLED', 'PLL_COMPLETED']);
		if (state == true && taskstatus >= 0 || state != true && taskstatus == -1) {
			return false;
		}
		eventObject.id = calendarDetails._recordId;
		eventObject.title = calendarDetails.subject.display_value;
		var startDate = calendar.fullCalendar('moment', calendarDetails.date_start.display_value + ' ' + calendarDetails.time_start.display_value);
		eventObject.start = startDate.toString();
		var endDate = calendar.fullCalendar('moment', calendarDetails.due_date.display_value + ' ' + calendarDetails.time_end.display_value);
		var assignedUserId = calendarDetails.assigned_user_id.value;
		eventObject.end = endDate.toString();
		eventObject.url = 'index.php?module=Calendar&view=Detail&record=' + calendarDetails._recordId;
		eventObject.activitytype = calendarDetails.activitytype.value;

		if ('on' == calendarDetails.allday.value)
			eventObject.allDay = true;
		else
			eventObject.allDay = false;
		eventObject.state = calendarDetails.state.value;
		eventObject.vis = calendarDetails.visibility.value;
		eventObject.sta = calendarDetails.activitystatus.value;
		eventObject.className = 'userCol_' + calendarDetails.assigned_user_id.value + ' calCol_' + calendarDetails.activitytype.value;
		this.getCalendarView().fullCalendar('renderEvent', eventObject);
	},
	getCalendarCreateView: function () {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();

		if (this.calendarCreateView !== false) {
			aDeferred.resolve(this.calendarCreateView.clone(true, true));
			return aDeferred.promise();
		}
		var progressInstance = jQuery.progressIndicator({blockInfo: {enabled: true}});
		this.loadCalendarCreateView().then(
				function (data) {
					progressInstance.progressIndicator({mode: 'hide'});
					thisInstance.calendarCreateView = data;
					aDeferred.resolve(data.clone(true, true));
				},
				function () {
					progressInstance.progressIndicator({mode: 'hide'});
				}
		);
		return aDeferred.promise();
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
		thisInstance.getCalendarView().find("button.fc-button").click(function () {
			thisInstance.loadCalendarData();
		});
	},
	goToRecordsList: function (link) {
		var thisInstance = this;
		var types = thisInstance.getValuesFromSelect2($("#calendarActivityTypeList"), []);
		var user = thisInstance.getValuesFromSelect2($("#calendarUserList"), [], false);
		user = thisInstance.getValuesFromSelect2($("#calendarGroupList"), user, false);
		var view = thisInstance.getCalendarView().fullCalendar('getView');
		var start_date = view.start.format();
		var end_date = view.end.format();
		var status = app.getMainParams('activityStateLabels', true);
		var searchParams = '["activitystatus","e","' + status[app.getMainParams('showType')].join() + '"]';
		searchParams += ',["date_start","bw","' + start_date + ',' + end_date + '"]';
		if (types.length) {
			searchParams += ',["activitytype","e","' + types + '"]';
		}
		if (user.length) {
			searchParams += ',["assigned_user_id","e","' + user + '"]';
		}
		$(".calendarFilters .filterField").each(function () {
			var type = $(this).attr('type');
			if (type == 'checkbox' && $(this).prop('checked')) {
				searchParams += (searchParams != '' ? ',[' : '[') + $(this).data('search') + ']';
			}

		});
		window.location.href = link + '&search_params=[[' + searchParams + ']]';
	},
	registerAddButton: function () {
		var thisInstance = this;
		jQuery('.calendarViewContainer .widget_header .addButton').on('click', function (e) {
			thisInstance.getCalendarCreateView().then(function (data) {
				var headerInstance = new Vtiger_Header_Js();
				headerInstance.handleQuickCreateData(data, {callbackFunction: function (data) {
						thisInstance.addCalendarEvent(data.result);
					}});
			});
		});
	},
	createAddSwitch: function () {
		var thisInstance = this;
		var calendarview = this.getCalendarView();
		var checked = '';
		if (app.getMainParams('showType') == 'current' && app.moduleCacheGet('defaultShowType') != 'history') {
			checked = ' checked ';
		}
		var switchBtn = jQuery('<span class=""><input class="switchBtn showType" id="defaultShowType" type="checkbox" title="' + app.vtranslate('JS_CHANGE_ACTIVITY_TIME') + '" ' + checked + ' data-size="small" data-handle-width="90" data-label-width="5" data-on-text="' + app.vtranslate('JS_TO_REALIZE') + '" data-off-text="' + app.vtranslate('JS_HISTORY') + '"></span>')
				.prependTo(calendarview.find('.fc-toolbar .fc-right'))
				.on('switchChange.bootstrapSwitch', function (e, state) {
					if (state) {
						app.setMainParams('showType', 'current');
						app.moduleCacheSet('defaultShowType', 'current');
					} else {
						app.setMainParams('showType', 'history');
						app.moduleCacheSet('defaultShowType', 'history');
					}
					thisInstance.loadCalendarData();
				});
		app.showBtnSwitch(switchBtn.find('.switchBtn'));
		checked = '';
		if (app.getMainParams('switchingDays') == 'workDays' && app.moduleCacheGet('defaultSwitchingDays') != 'all') {
			checked = ' checked ';
		}
		if (app.getMainParams('hiddenDays', true) !== false){
			switchBtn = jQuery('<span class=""><input class="switchBtn switchingDays" type="checkbox" id="defaultSwitchingDays" title="' + app.vtranslate('JS_SWITCHING_DAYS') + '" ' + checked + ' data-size="small" data-handle-width="90" data-label-width="5" data-on-text="' + app.vtranslate('JS_WORK_DAYS') + '" data-off-text="' + app.vtranslate('JS_ALL') + '"></span>')
					.prependTo(calendarview.find('.fc-toolbar .fc-right'))
					.on('switchChange.bootstrapSwitch', function (e, state) {
						if (state) {
							app.setMainParams('switchingDays', 'workDays');
							app.moduleCacheSet('defaultSwitchingDays', 'workDays');
						} else {
							app.setMainParams('switchingDays', 'all');
							app.moduleCacheSet('defaultSwitchingDays', 'all');
						}
						thisInstance.renderCalendar();
						thisInstance.loadCalendarData();
					});
			app.showBtnSwitch(switchBtn.find('.switchBtn'));
		}
	},
	registerSelect2Event: function () {
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
		app.showSelect2ElementView($('#calendarUserList'));
		app.showSelect2ElementView($('#calendarActivityTypeList'));
		app.showSelect2ElementView($('#calendarGroupList'));
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
	},
	registerCacheSettings: function () {
		var thisInstance = this;
		var calendar = thisInstance.getCalendarView();
		if (app.moduleCacheGet('defaultSwitchingDays') == 'all') {
			app.setMainParams('switchingDays', 'all');
		} else {
			app.setMainParams('switchingDays', 'workDays');
		}
		if (app.moduleCacheGet('defaultShowType') == 'history') {
			app.setMainParams('showType', 'history');
		} else {
			app.setMainParams('showType', 'current');
		}
		$('.siteBarRight .filterField').each(function (index) {
			var name = $(this).attr('id');
			var value = app.moduleCacheGet(name);
			var element = $('#' + name);
			if (element.length > 0 && value != null) {
				if (element.attr('type') == 'checkbox') {
					element.prop("checked", value);
				}
			}
		});
		calendar.find('.fc-toolbar .fc-button').click(function (e) {
			var defaultView, view, options;
			var element = $(e.currentTarget);
			view = calendar.fullCalendar('getView');
			options = view.options;
			if (element.hasClass('fc-' + view.name + '-button')) {
				app.moduleCacheSet('defaultView', view.name);
			} else if (element.hasClass('fc-prev-button') || element.hasClass('fc-next-button') || element.hasClass('fc-today-button')) {
				app.moduleCacheSet('start', view.start.format());
				app.moduleCacheSet('end', view.end.format());
			}
		});
		var keys = app.moduleCacheKeys();
		if (keys.length > 0) {
			var alert = $('#moduleCacheAlert');
			$('.bodyContents').on('Vtiger.Widget.Load.undefined', function (e, data) {
				alert.removeClass('hide');
			});
			alert.find('.cacheClear').click(function (e) {
				app.moduleCacheClear();
				alert.addClass('hide');
				location.reload();
			});
		}
	},
	registerLoadCalendarData: function () {
		var thisInstance = this;
		var widgets = $('.siteBarRight .widgetContainer').length;
		$('.bodyContents').on('Vtiger.Widget.Load.undefined', function (e, data) {
			widgets -= 1;
			if (widgets == 0) {
				thisInstance.loadCalendarData(true);
			}
		});
	},
	registerEvents: function () {
		this.registerCacheSettings();
		this.renderCalendar();
		this.registerAddButton();
		this.registerLoadCalendarData();
		this.registerButtonSelectAll();
		this.registerChangeView();
	}
});
jQuery(document).ready(function () {
	var instance = Calendar_CalendarView_Js.getInstanceByView();
	instance.registerEvents();
	Calendar_CalendarView_Js.currentInstance = instance;
	Calendar_CalendarView_Js.registerWidget();
});
