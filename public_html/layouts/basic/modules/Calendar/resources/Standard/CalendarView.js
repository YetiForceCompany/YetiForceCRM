/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

window.Calendar_Calendar_Js = class Calendar_Calendar_Js extends Calendar_Js {

	constructor(container) {
		super(container);
	}

	setCalendarModuleOptions() {
		let self = this,
			options = {
				select: function (start, end) {
					self.selectDays(start, end);
					self.getCalendarView().fullCalendar('unselect');
				},
				eventClick: function (calEvent, jsEvent, view) {
					jsEvent.preventDefault();
					var link = new URL($(this)[0].href);
					var progressInstance = jQuery.progressIndicator({blockInfo: {enabled: true}});
					var url = 'index.php?module=Calendar&view=ActivityStateModal&record=' + link.searchParams.get("record");
					var callbackFunction = function (data) {
						progressInstance.progressIndicator({mode: 'hide'});
					};
					var modalWindowParams = {
						url: url,
						cb: callbackFunction
					};
					app.showModalWindow(modalWindowParams);
				}
			};
		return options;
	}

	eventRenderer(event, element) {
		let self = this;
		let valueEventVis = '';
		if (event.vis !== '') {
			valueEventVis = app.vtranslate('JS_' + event.vis);
		}
		app.showPopoverElementView(element.find('.fc-content'), {
			title: event.title + '<a href="index.php?module=' + event.module + '&view=Edit&record=' + event.id + '" class="float-right"><span class="fas fa-edit"></span></a>' + '<a href="index.php?module=' + event.module + '&view=Detail&record=' + event.id + '" class="float-right mx-1"><span class="fas fa-th-list"></span></a>',
			container: 'body',
			html: true,
			placement: 'auto',
			template: '<div class="popover calendarPopover" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
			content: '<div><span class="far fa-clock"></span> <label>' + app.vtranslate('JS_START_DATE') + '</label>: ' + event.start_display + '</div>' +
				'<div><span class="far fa-clock"></span> <label>' + app.vtranslate('JS_END_DATE') + '</label>: ' + event.end_display + '</div>' +
				(event.lok ? '<div><span class="fas fa-globe"></span> <label>' + app.vtranslate('JS_LOCATION') + '</label>: ' + event.lok + '</div>' : '') +
				(event.pri ? '<div><span class="fas fa-exclamation-circle"></span> <label>' + app.vtranslate('JS_PRIORITY') + '</label>: <span class="picklistCT_Calendar_taskpriority_' + event.pri + '">' + app.vtranslate('JS_' + event.pri) + '</span></div>' : '') +
				'<div><span class="fas fa-question-circle"></span> <label>' + app.vtranslate('JS_STATUS') + '</label>:  <span class="picklistCT_Calendar_activitystatus_' + event.sta + '">' + app.vtranslate('JS_' + event.sta) + '</span></div>' +
				(event.accname ? '<div><span class="userIcon-Accounts" aria-hidden="true"></span> <label>' + app.vtranslate('JS_ACCOUNTS') + '</label>: <span class="modCT_Accounts">' + event.accname + '</span></div>' : '') +
				(event.linkexl ? '<div><span class="userIcon-' + event.linkexm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_RELATION_EXTEND') + '</label>: <a class="modCT_' + event.linkexm + '" target="_blank" href="index.php?module=' + event.linkexm + '&view=Detail&record=' + event.linkextend + '">' + event.linkexl + '</a></div>' : '') +
				(event.linkl ? '<div><span class="userIcon-' + event.linkm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_RELATION') + '</label>: <a class="modCT_' + event.linkm + '" target="_blank" href="index.php?module=' + event.linkm + '&view=Detail&record=' + event.link + '">' + event.linkl + '</span></a></div>' : '') +
				(event.procl ? '<div><span class="userIcon-' + event.procm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_PROCESS') + '</label>: <a class="modCT_' + event.procm + '"target="_blank" href="index.php?module=' + event.procm + '&view=Detail&record=' + event.process + '">' + event.procl + '</a></div>' : '') +
				(event.subprocl ? '<div><span class="userIcon-' + event.subprocm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_SUB_PROCESS') + '</label>: <a class="modCT_' + event.subprocm + '" target="_blank" href="index.php?module=' + event.subprocm + '&view=Detail&record=' + event.subprocess + '">' + event.subprocl + '</a></div>' : '') +
				(event.state ? '<div><span class="far fa-star"></span> <label>' + app.vtranslate('JS_STATE') + '</label>:  <span class="picklistCT_Calendar_state_' + event.state + '">' + app.vtranslate(event.state) + '</span></div>' : '') +
				'<div><span class="fas fa-eye"></span> <label>' + app.vtranslate('JS_VISIBILITY') + '</label>:  <span class="picklistCT_Calendar_visibility_' + event.vis + '">' + valueEventVis + '</div>' +
				(event.smownerid ? '<div><span class="fas fa-user"></span> <label>' + app.vtranslate('JS_ASSIGNED_TO') + '</label>: ' + event.smownerid + '</div>' : '')
		});
		if (event.rendering === 'background') {
			element.append(`<span class="${event.icon} mr-1"></span>${event.title}`);
		}
	}

	getValuesFromSelect2(element, data, text) {
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
	}

	loadCalendarData(allEvents) {
		var progressInstance = jQuery.progressIndicator({blockInfo: {enabled: true}});
		var thisInstance = this;
		thisInstance.getCalendarView().fullCalendar('removeEvents');
		var view = thisInstance.getCalendarView().fullCalendar('getView');
		var types = [];
		var formatDate = CONFIG.dateFormat.toUpperCase();
		types = thisInstance.getValuesFromSelect2($("#calendarActivityTypeList"), types);
		if (types.length == 0) {
			allEvents = true;
		}
		var user = [];
		user = thisInstance.getValuesFromSelect2($("#calendarUserList"), user);
		if (user.length == 0) {
			user = [CONFIG.userId];
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
				start: view.start.format(formatDate),
				end: view.end.format(formatDate),
				user: user,
				time: app.getMainParams('showType'),
				types: types,
				filters: filters
			};
			AppConnector.request(params).done(function (events) {
				thisInstance.getCalendarView().fullCalendar('addEventSource', events.result);
				progressInstance.progressIndicator({mode: 'hide'});
				thisInstance.registerSelect2Event();
			});
		} else {
			thisInstance.getCalendarView().fullCalendar('removeEvents');
			progressInstance.progressIndicator({mode: 'hide'});
		}
	}

	selectDays(startDate, endDate) {
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
		this.getCalendarCreateView().done(function (data) {
			if (data.length <= 0) {
				return;
			}
			if (view.name != 'agendaDay' && view.name != 'agendaWeek') {
				startDate = startDate + 'T' + start_hour + ':00';
				endDate = endDate + 'T' + start_hour + ':00';
				if (startDate == endDate) {
					let activityType = data.find('[name="activitytype"]').val();
					let activityDurations = JSON.parse(data.find('[name="defaultOtherEventDuration"]').val());
					let minutes = 0;
					for (let i in activityDurations) {
						if (activityDurations[i].activitytype === activityType) {
							minutes = parseInt(activityDurations[i].duration);
							break;
						}
					}
					endDate = moment(endDate).add(minutes, 'minutes').toISOString();
				}
			}
			var dateFormat = data.find('[name="date_start"]').data('dateFormat').toUpperCase();
			var timeFormat = data.find('[name="time_start"]').data('format');
			if (timeFormat == 24) {
				var defaultTimeFormat = 'HH:mm';
			} else {
				defaultTimeFormat = 'hh:mm A';
			}
			var startDateString = moment(startDate).format(dateFormat);
			var startTimeString = moment(startDate).format(defaultTimeFormat);
			var endDateString = moment(endDate).format(dateFormat);
			var endTimeString = moment(endDate).format(defaultTimeFormat);

			data.find('[name="date_start"]').val(startDateString);
			data.find('[name="due_date"]').val(endDateString);
			data.find('[name="time_start"]').val(startTimeString);
			data.find('[name="time_end"]').val(endTimeString);

			var headerInstance = new Vtiger_Header_Js();
			headerInstance.handleQuickCreateData(data, {
				callbackFunction: function (data) {
					thisInstance.addCalendarEvent(data.result);
				}
			});
		});
	}

	addCalendarEvent(calendarDetails) {
		const thisInstance = this;
		let usersList = $("#calendarUserList").val();
		if (usersList.length === 0) {
			usersList = [CONFIG.userId.toString()];
		}
		let groupList = $("#calendarGroupList").val();
		if ($.inArray(calendarDetails.assigned_user_id.value, usersList) < 0 && ($.inArray(calendarDetails.assigned_user_id.value, groupList) < 0 || groupList.length === 0)) {
			return;
		}
		let types = thisInstance.getValuesFromSelect2($("#calendarActivityTypeList"), []);
		if (types.length !== 0 && $.inArray(calendarDetails.activitytype.value, $("#calendarActivityTypeList").val()) < 0) {
			return;
		}
		var state = $('.fc-toolbar .js-switch--label-on').last().hasClass('active');
		var calendar = this.getCalendarView();
		var taskstatus = $.inArray(calendarDetails.activitystatus.value, ['PLL_POSTPONED', 'PLL_CANCELLED', 'PLL_COMPLETED']);
		if (state === true && taskstatus >= 0 || state != true && taskstatus == -1) {
			return false;
		}
		var startDate = calendar.fullCalendar('moment', calendarDetails.date_start.value + ' ' + calendarDetails.time_start.value);
		var endDate = calendar.fullCalendar('moment', calendarDetails.due_date.value + ' ' + calendarDetails.time_end.value);
		var eventObject = {
			id: calendarDetails._recordId,
			title: calendarDetails.subject.display_value,
			start: startDate.format(),
			end: endDate.format(),
			url: 'index.php?module=Calendar&view=Detail&record=' + calendarDetails._recordId,
			activitytype: calendarDetails.activitytype.value,
			allDay: calendarDetails.allday.value == 'on',
			state: calendarDetails.state.value,
			vis: calendarDetails.visibility.value,
			sta: calendarDetails.activitystatus.value,
			className: 'ownerCBg_' + calendarDetails.assigned_user_id.value + ' picklistCBr_Calendar_activitytype_' + calendarDetails.activitytype.value,
			start_display: calendarDetails.date_start.display_value,
			end_display: calendarDetails.due_date.display_value,
			smownerid: calendarDetails.assigned_user_id.display_value,
			pri: calendarDetails.taskpriority.value,
			lok: calendarDetails.location.display_value
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
		var progressInstance = jQuery.progressIndicator({blockInfo: {enabled: true}});
		this.loadCalendarCreateView().done(function (data) {
			progressInstance.progressIndicator({mode: 'hide'});
			thisInstance.calendarCreateView = data;
			aDeferred.resolve(data.clone(true, true));
		}).fail(function (error) {
			progressInstance.progressIndicator({mode: 'hide'});
			console.error(error);
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
		}).fail(function () {
			aDeferred.reject();
		});
		return aDeferred.promise();
	}

	getCalendarView() {
		if (this.calendarView == false) {
			this.calendarView = jQuery('.js-calendar__container');
		}
		return this.calendarView;
	}

	switchTpl(on, off, state) {
		return `<div class="btn-group btn-group-toggle js-switch c-calendar-switch" data-toggle="buttons">
					<label class="btn btn-outline-primary c-calendar-switch__button js-switch--label-on ${state ? '' : 'active'}">
						<input type="radio" name="options" data-on-text="${on}" autocomplete="off" ${state ? '' : 'checked'}>
						${on}
					</label>
					<label class="btn btn-outline-primary c-calendar-switch__button ${state ? 'active' : ''}">
						<input type="radio" name="options" data-off-text="${off}" autocomplete="off" ${state ? 'checked' : ''}>
						${off}
					</label>
				</div>`;
	}

	registerSwitchEvents() {
		const calendarview = this.getCalendarView();
		let switchHistory,
			switchAllDays,
			switchContainer = $(`<div class="js-calendar-switch-container"></div>`).insertAfter(calendarview.find('.fc-center'));
		if (app.getMainParams('showType') == 'current' && app.moduleCacheGet('defaultShowType') != 'history') {
			switchHistory = false;
		} else {
			switchHistory = true;
		}
		$(this.switchTpl(app.vtranslate('JS_TO_REALIZE'), app.vtranslate('JS_HISTORY'), switchHistory))
			.prependTo(switchContainer)
			.on('change', 'input', (e) => {
				const currentTarget = $(e.currentTarget);
				if (typeof currentTarget.data('on-text') !== 'undefined') {
					app.setMainParams('showType', 'current');
					app.moduleCacheSet('defaultShowType', 'current');
				} else if (typeof currentTarget.data('off-text') !== 'undefined') {
					app.setMainParams('showType', 'history');
					app.moduleCacheSet('defaultShowType', 'history');
				}
				this.loadCalendarData();
			});
		if (app.getMainParams('switchingDays') === 'workDays' && app.moduleCacheGet('defaultSwitchingDays') !== 'all') {
			switchAllDays = false;
		} else {
			switchAllDays = true;
		}
		if (app.getMainParams('hiddenDays', true) !== false) {
			$(this.switchTpl(app.vtranslate('JS_WORK_DAYS'), app.vtranslate('JS_ALL'), switchAllDays))
				.prependTo(switchContainer)
				.on('change', 'input', (e) => {
					const currentTarget = $(e.currentTarget);
					let hiddenDays = [];
					if (typeof currentTarget.data('on-text') !== 'undefined') {
						app.setMainParams('switchingDays', 'workDays');
						app.moduleCacheSet('defaultSwitchingDays', 'workDays');
						hiddenDays = app.getMainParams('hiddenDays', true);
					} else if (typeof currentTarget.data('off-text') !== 'undefined') {
						app.setMainParams('switchingDays', 'all');
						app.moduleCacheSet('defaultSwitchingDays', 'all');
					}
					calendarview.fullCalendar('option', 'hiddenDays', hiddenDays);
					this.registerSwitchEvents();
					this.loadCalendarData();
				});
		}
	}

	registerCacheSettings() {
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
		calendar.find('.fc-toolbar .fc-button').on('click', function (e) {
			let view;
			let element = $(e.currentTarget);
			view = calendar.fullCalendar('getView');
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
				alert.removeClass('d-none');
			});
			alert.find('.cacheClear').on('click', function (e) {
				app.moduleCacheClear();
				alert.addClass('d-none');
				location.reload();
			});
		}
	}

	registerEvents() {
		super.registerEvents();
		this.registerCacheSettings();
		this.registerSwitchEvents();
	}
}
