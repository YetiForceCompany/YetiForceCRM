/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
window.Calendar_CalendarExtended_Js = class Calendar_CalendarExtended_Js extends Calendar_Calendar_Js {

	constructor(container, readonly) {
		super(container, readonly);
		this.datesRowView = false;
		this.sidebarView = {
			length: 0
		};
		this.calendarContainer = false;
		this.addCommonMethodsToYearView();
		this.calendar = this.getCalendarView();
	}

	/**
	 * Function extends FC.views.year with current class methods
	 */
	addCommonMethodsToYearView() {
		const self = this;
		FC.views.year = FC.views.year.extend({
			selectDays: self.selectDays,
			getCalendarCreateView: self.getCalendarCreateView,
			getSidebarView: self.getSidebarView,
			getCurrentCvId: self.getCurrentCvId,
			getCalendarView: self.getCalendarView,
			showRightPanelForm: self.showRightPanelForm,
			getSelectedUsersCalendar: self.getSelectedUsersCalendar,
			registerClearFilterButton: self.registerClearFilterButton,
			clearFilterButton: self.clearFilterButton,
			registerFilterTabChange: self.registerFilterTabChange,
			sidebarView: self.sidebarView,
			registerAfterSubmitForm: self.registerAfterSubmitForm,
			registerViewRenderEvents: self.registerViewRenderEvents,
			appendSubDateRow: self.appendSubDateRow,
			refreshDatesRowView: self.refreshDatesRowView,
			generateYearList: self.generateYearList,
			getDatesRowView: self.getDatesRowView,
			updateCountTaskCalendar: self.updateCountTaskCalendar,
			registerDatesChange: self.registerDatesChange,
			addHeaderButtons: self.addHeaderButtons,
			browserHistoryConfig: self.browserHistoryConfig,
			readonly: self.readonly,
			container: self.container
		});
	}

	/**
	 * Render calendar
	 */
	renderCalendar() {
		let self = this,
			basicOptions = this.setCalendarMergedOptions(),
			options = {
				firstLoad: true,
				header: {
					left: 'year,month,' + app.getMainParams('weekView') + ',' + app.getMainParams('dayView'),
					center: 'prevYear,prev,title,next,nextYear',
					right: 'today'
				},
				editable: !self.readonly,
				height: self.setCalendarHeight(),
				views: {
					basic: {
						eventLimit: false,
					},
					year: {
						eventLimit: 10,
						eventLimitText: app.vtranslate('JS_COUNT_RECORDS'),
						titleFormat: 'YYYY',
						select: function (start, end) {
						},
						loadView: function () {
							self.getCalendarView().fullCalendar('getCalendar').view.render();
						}
					},
					month: {
						titleFormat: 'YYYY MMMM',
						loadView: function () {
							self.loadCalendarData();
						}
					},
					week: {
						titleFormat: 'YYYY MMM D',
						loadView: function () {
							self.loadCalendarData();
						}
					},
					day: {
						titleFormat: 'YYYY MMM D',
						loadView: function () {
							self.loadCalendarData();
						}
					},
					basicDay: {
						type: 'agendaDay',
						loadView: function () {
							self.loadCalendarData();
						}
					}
				},
				select: function (start, end) {
					self.selectDays(start, end);
					self.getCalendarView().fullCalendar('unselect');
				},
				eventRender: function (event, element) {
					self.eventRenderer(event, element);
				},
				viewRender: function (view, element) {
					if (view.type !== 'year') {
						self.loadCalendarData(view);
					}
				},
				addCalendarEvent(calendarDetails) {
					let calendar = self.getCalendarView(),
						startDate = calendar.fullCalendar('moment', calendarDetails.date_start.value + ' ' + calendarDetails.time_start.value),
						endDate = calendar.fullCalendar('moment', calendarDetails.due_date.value + ' ' + calendarDetails.time_end.value),
						eventObject = {
							id: calendarDetails._recordId,
							title: calendarDetails.subject.display_value,
							start: startDate.format(),
							end: endDate.format(),
							module: 'Calendar',
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
					self.getCalendarView().fullCalendar('renderEvent', eventObject);
				}
			};
		options = Object.assign(basicOptions, options);
		if (!this.readonly) {
			options.eventClick = function (calEvent, jsEvent, view) {
				jsEvent.preventDefault();
				let link = new URL($(this)[0].href),
					url = 'index.php?module=Calendar&view=ActivityState&record=' +
						link.searchParams.get("record");
				self.openRightPanel();
				self.showStatusUpdate(url);
			};
		} else {
			options.eventClick = '';
		}
		this.calendar.fullCalendar(options);
	}

	registerChangeView() {
	}

	addHeaderButtons() {
		if (this.calendarContainer.find('.js-calendar__view-btn').length) {
			return;
		}
		let buttonsContainer = this.calendarContainer.prev('.js-calendar__header-buttons'),
			viewBtn = buttonsContainer.find('.js-calendar__view-btn').clone(),
			filters = buttonsContainer.find('.js-calendar__filter-container').clone();
		this.calendarContainer.find('.fc-left').prepend(viewBtn);
		this.calendarContainer.find('.fc-center').after(filters);
		this.registerClearFilterButton();
		this.registerFilterTabChange();
	}

	showStatusUpdate(params) {
		const thisInstance = this,
			progressInstance = $.progressIndicator({blockInfo: {enabled: true}});
		AppConnector.request(params).done((data) => {
			progressInstance.progressIndicator({mode: 'hide'});
			let sideBar = thisInstance.getSidebarView();
			sideBar.find('.js-qc-form').html(data);
			thisInstance.showRightPanelForm();
			app.showNewScrollbar(sideBar.find('.js-calendar__form__wrapper'), {
				suppressScrollX: true
			});
			sideBar.find('.js-activity-state .js-summary-close-edit').on('click', function () {
				thisInstance.getCalendarCreateView();
			});
			sideBar.find('.js-activity-state .editRecord').on('click', function () {
				thisInstance.getCalendarEditView($(this).data('id'));
			});
		});
	}

	registerSwitchEvents() {
		const calendarView = this.getCalendarView();
		let isWorkDays,
			switchShowTypeVal,
			switchContainer = $('.js-calendar__tab--filters'),
			switchShowType = switchContainer.find('.js-switch--showType'),
			switchSwitchingDays = switchContainer.find('.js-switch--switchingDays');
		let historyParams = app.getMainParams('historyParams', true);
		if (historyParams === '') {
			isWorkDays = (app.getMainParams('switchingDays') === 'workDays' && app.moduleCacheGet('defaultSwitchingDays') !== 'all'),
				switchShowTypeVal = (app.getMainParams('showType') === 'current' && app.moduleCacheGet('defaultShowType') !== 'history');
			if (!switchShowTypeVal) {
				switchShowType.find('.js-switch--label-off').button('toggle');
			}
		} else {
			app.setMainParams('showType', historyParams.time);
			app.setMainParams('switchingDays', historyParams.hiddenDays === '' ? 'all' : 'workDays');

		}
		switchShowType.on('change', 'input', (e) => {
			const currentTarget = $(e.currentTarget);
			if (typeof currentTarget.data('on-text') !== 'undefined') {
				app.setMainParams('showType', 'current');
				app.moduleCacheSet('defaultShowType', 'current');
			} else if (typeof currentTarget.data('off-text') !== 'undefined') {
				app.setMainParams('showType', 'history');
				app.moduleCacheSet('defaultShowType', 'history');
			}
			calendarView.fullCalendar('getCalendar').view.options.loadView();
		});
		if (switchSwitchingDays.length) {
			if (typeof isWorkDays !== 'undefined' && !isWorkDays) {
				switchSwitchingDays.find('.js-switch--label-off').button('toggle');
			}
			switchSwitchingDays.on('change', 'input', (e) => {
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
				calendarView.fullCalendar('option', 'hiddenDays', hiddenDays);
				calendarView.fullCalendar('option', 'height', this.setCalendarHeight());
				if (calendarView.fullCalendar('getView').type === 'year') {
					this.registerViewRenderEvents(calendarView.fullCalendar('getView'));
				}
			});
		}
	}

	eventRenderer(event, element) {
		if (event.id === undefined) {
			return;
		}
		const self = this;
		let editableButton = '',
			valueEventVis = '';
		if (self.getCalendarView().fullCalendar('getCalendar').view.options.editable) {
			editableButton = '<a href="javascript:void(0);" class="float-right mx-1 js-edit-element" data-js="click"><span class="fas fa-edit float-right"></span></a>';
		}
		if (event.vis !== '') {
			valueEventVis = app.vtranslate('JS_' + event.vis);
		}
		$(document).find('.js-calendar-popover.show').hide();
		app.showPopoverElementView(element.find('.fc-content'), {
			title: event.title + editableButton + '<a href="index.php?module=' + event.module + '&view=Detail&record=' + event.id + '" class="float-right mx-1"><span class="fas fa-th-list"></span></a>',
			container: 'body',
			html: true,
			placement: 'auto',
			callbackShown: function () {
				$(`.js-calendar-popover[data-event-id="${event.id}"]`).find('.js-edit-element').on('click', function () {
					self.openRightPanel();
					self.getCalendarEditView(event.id);
				});
			},
			template: `<div class="popover calendarPopover js-calendar-popover" role="tooltip" data-event-id="${event.id}" data-js="hide"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>`,
			content: '<div><span class="fas fa-clock"></span> <label>' + app.vtranslate('JS_START_DATE') + '</label>: ' + event.start_display + '</div>' +
				'<div><span class="fas fa-clock"></span> <label>' + app.vtranslate('JS_END_DATE') + '</label>: ' + event.end_display + '</div>' +
				(event.lok ? '<div><span class="fas fa-globe"></span> <label>' + app.vtranslate('JS_LOCATION') + '</label>: ' + event.lok + '</div>' : '') +
				(event.pri ? '<div><span class="fas fa-exclamation-circle"></span> <label>' + app.vtranslate('JS_PRIORITY') + '</label>: <span class="picklistCT_Calendar_taskpriority_' + event.pri + '">' + app.vtranslate('JS_' + event.pri) + '</span></div>' : '') +
				'<div><span class="fas fa-question-circle"></span> <label>' + app.vtranslate('JS_STATUS') + '</label>:  <span class="picklistCT_Calendar_activitystatus_' + event.sta + '">' + app.vtranslate('JS_' + event.sta) + '</span></div>' +
				(event.accname ? '<div><span class="userIcon-Accounts" aria-hidden="true"></span> <label>' + app.vtranslate('JS_ACCOUNTS') + '</label>: <span class="modCT_Accounts">' + event.accname + '</span></div>' : '') +
				(event.linkexl ? '<div><span class="userIcon-' + event.linkexm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_RELATION_EXTEND') + '</label>: <a class="modCT_' + event.linkexm + '" href="index.php?module=' + event.linkexm + '&view=Detail&record=' + event.linkextend + '">' + event.linkexl + '</a></div>' : '') +
				(event.linkl ? '<div><span class="userIcon-' + event.linkm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_RELATION') + '</label>: <a class="modCT_' + event.linkm + '" href="index.php?module=' + event.linkm + '&view=Detail&record=' + event.link + '">' + event.linkl + '</span></a></div>' : '') +
				(event.procl ? '<div><span class="userIcon-' + event.procm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_PROCESS') + '</label>: <a class="modCT_' + event.procm + '" href="index.php?module=' + event.procm + '&view=Detail&record=' + event.process + '">' + event.procl + '</a></div>' : '') +
				(event.subprocl ? '<div><span class="userIcon-' + event.subprocm + '" aria-hidden="true"></span> <label>' + app.vtranslate('JS_SUB_PROCESS') + '</label>: <a class="modCT_' + event.subprocm + '" href="index.php?module=' + event.subprocm + '&view=Detail&record=' + event.subprocess + '">' + event.subprocl + '</a></div>' : '') +
				(event.state ? '<div><span class="fas fa-star"></span> <label>' + app.vtranslate('JS_STATE') + '</label>:  <span class="picklistCT_Calendar_state_' + event.state + '">' + app.vtranslate(event.state) + '</span></div>' : '') +
				'<div><span class="fas fa-eye"></span> <label>' + app.vtranslate('JS_VISIBILITY') + '</label>:  <span class="picklistCT_Calendar_visibility_' + event.vis + '">' + valueEventVis + '</div>' +
				(event.smownerid ? '<div><span class="fas fa-user"></span> <label>' + app.vtranslate('JS_ASSIGNED_TO') + '</label>: ' + event.smownerid + '</div>' : '')
		});
		if (event.rendering === 'background') {
			element.append(`<span class="${event.icon} mr-1"></span>${event.title}`)
		}
	}

	getDatesRowView() {
		this.datesRowView = $('.js-dates-row');
		return this.datesRowView;
	}

	/**
	 * Appends subdate row to calendar header and register its scroll
	 * @param toolbar
	 */
	appendSubDateRow(toolbar) {
		if (!this.calendarContainer.find('.js-dates-row').length) {
			this.subDateRow = $(`
								<div class="js-scroll js-dates-row u-overflow-auto-lg-down order-4 flex-grow-1 position-relative my-1 w-100" data-js="perfectScrollbar | container">
									<div class="d-flex flex-nowrap w-100">
										<div class="js-sub-date-list w-100 sub-date-list row no-gutters flex-nowrap nav nav-tabs" data-js="data-type"></div>
									</div>
								</div>
								`);
			toolbar.append(this.subDateRow);
			if ($(window).width() > app.breakpoints.lg) {
				app.showNewScrollbar(this.subDateRow, {
					suppressScrollY: true
				});
			}
		}
	}

	/**
	 * Function toggles next year/month and general arrows on view render
	 * @param view
	 * @param element
	 */
	registerViewRenderEvents(view) {
		this.calendarContainer = this.getCalendarView();
		let toolbar = this.calendarContainer.find('.fc-toolbar.fc-header-toolbar'),
			nextPrevButtons = toolbar.find('.fc-prev-button, .fc-next-button'),
			yearButtons = toolbar.find('.fc-prevYear-button, .fc-nextYear-button');
		this.appendSubDateRow(toolbar);
		this.refreshDatesRowView(view);
		this.addHeaderButtons();
		if (view.type === 'year') {
			nextPrevButtons.hide();
			yearButtons.show();
		} else if (view.type === 'month') {
			nextPrevButtons.show();
			yearButtons.show();
		} else {
			nextPrevButtons.show();
			yearButtons.hide();
		}
	}

	/**
	 * Date bar with counts
	 * @param object calendarView
	 */
	refreshDatesRowView(calendarView) {
		const self = this;
		switch (calendarView.type) {
			case 'year':
				self.generateYearList(calendarView.intervalStart, calendarView.intervalEnd);
				break;
			case 'month':
				self.generateSubMonthList(calendarView.intervalStart, calendarView.intervalEnd);
				break;
			case 'week':
			case 'agendaWeek':
				self.generateSubWeekList(calendarView.start, calendarView.end);
				break;
			default:
				self.generateSubDaysList(calendarView.start, calendarView.end);
		}
		self.updateCountTaskCalendar();
		self.registerDatesChange();
	}

	registerDatesChange() {
		this.getDatesRowView().find('.js-sub-record').on('click', (e) => {
			let currentTarget = $(e.currentTarget);
			currentTarget.addClass('active');
			this.getCalendarView().fullCalendar('gotoDate', moment(currentTarget.data('date'), "YYYY-MM-DD"));
		});
	}

	getCurrentCvId() {
		return $(".js-calendar__extended-filter-tab .active").parent('.js-filter-tab').data('cvid');
	}

	registerFilterTabChange() {
		const thisInstance = this;
		this.getCalendarView().find(".js-calendar__extended-filter-tab").on('shown.bs.tab', function () {
			thisInstance.getCalendarView().fullCalendar('getCalendar').view.options.loadView();
		});
	}

	getSelectedUsersCalendar() {
		const sidebar = this.getSidebarView();
		let selectedUsers = sidebar.find('.js-input-user-owner-id:checked'),
			selectedUsersAjax = sidebar.find('.js-input-user-owner-id-ajax'),
			selectedRolesAjax = sidebar.find('.js-input-role-owner-id-ajax'),
			users = [];
		if (selectedUsers.length > 0) {
			selectedUsers.each(function () {
				users.push($(this).val());
			});
		} else if (selectedUsersAjax.length > 0) {
			users = selectedUsersAjax.val().concat(selectedRolesAjax.val());
		}
		return users;
	}

	getSidebarView() {
		if (!this.sidebarView.length) {
			this.sidebarView = $('#rightPanel');
		}
		return this.sidebarView;
	}

	updateCountTaskCalendar() {
		let datesView = this.getDatesRowView(),
			subDatesElements = datesView.find('.js-sub-record'),
			dateArray = {},
			user = this.getSelectedUsersCalendar();
		if (user.length === 0) {
			user = [app.getMainParams('userId')];
		}
		subDatesElements.each(function (key, element) {
			let data = $(this).data('date'),
				type = $(this).data('type');
			if (type === 'years') {
				dateArray[key] = [moment(data + '-01').format('YYYY-MM-DD') + ' 00:00:00', moment(data + '-01').endOf('year').format('YYYY-MM-DD') + ' 23:59:59'];
			} else if (type === 'months') {
				dateArray[key] = [moment(data).format('YYYY-MM-DD') + ' 00:00:00', moment(data).endOf('month').format('YYYY-MM-DD') + ' 23:59:59'];
			} else if (type === 'weeks') {
				dateArray[key] = [moment(data).format('YYYY-MM-DD') + ' 00:00:00', moment(data).add(6, 'day').format('YYYY-MM-DD') + ' 23:59:59'];
			} else if (type === 'days') {
				dateArray[key] = [moment(data).format('YYYY-MM-DD') + ' 00:00:00', moment(data).format('YYYY-MM-DD') + ' 23:59:59'];
			}
		});
		AppConnector.request({
			module: 'Calendar',
			action: 'Calendar',
			mode: 'getCountEventsGroup',
			dates: dateArray,
			user: user,
			time: app.getMainParams('showType'),
			cvid: this.getCurrentCvId()
		}).done(function (events) {
			subDatesElements.each(function (key, element) {
				$(this).find('.js-count-events').removeClass('hide').html(events.result[key]);
			});
		});
	}

	/**
	 * Load calendar edit view
	 * @param int id
	 * @param Object params
	 */
	loadCalendarEditView(id, params) {
		const aDeferred = $.Deferred();
		let formData = {
			'module': app.getModuleName(),
			'view': 'EventForm',
			'record': id
		};
		if (typeof params !== 'undefined') {
			$.extend(formData, params);
		}
		AppConnector.request(formData).done((data) => {
			aDeferred.resolve($(data));
		}).fail((error) => {
			aDeferred.reject();
			app.errorLog(error);
		});
		return aDeferred.promise();
	}

	/**
	 * EditView
	 * @param int id
	 * @param Object params
	 */
	getCalendarEditView(id, params) {
		const thisInstance = this,
			aDeferred = $.Deferred();
		const progressInstance = $.progressIndicator({blockInfo: {enabled: true}});
		thisInstance.loadCalendarEditView(id, params).done((data) => {
			progressInstance.progressIndicator({mode: 'hide'});
			let sideBar = thisInstance.getSidebarView();
			sideBar.find('.js-qc-form').html(data);
			thisInstance.showRightPanelForm();
			let rightFormCreate = $(document).find('form[name="QuickCreate"]'),
				editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(sideBar.find('[name="module"]').val()),
				headerInstance = new Vtiger_Header_Js(),
				params = [];
			editViewInstance.registerBasicEvents(rightFormCreate);
			rightFormCreate.validationEngine(app.validationEngineOptions);
			headerInstance.registerHelpInfo(rightFormCreate);
			App.Fields.Picklist.showSelect2ElementView(sideBar.find('select'));
			sideBar.find('.js-summary-close-edit').on('click', function () {
				thisInstance.getCalendarCreateView();
			});
			params.callbackFunction = thisInstance.registerAfterSubmitForm(thisInstance, data);
			headerInstance.registerQuickCreatePostLoadEvents(rightFormCreate, params);
			$.each(sideBar.find('.ckEditorSource'), function (key, element) {
				let ckEditorInstance = new Vtiger_CkEditor_Js();
				ckEditorInstance.loadCkEditor($(element), {
					height: '5em',
					toolbar: 'Min'
				});
			});
			aDeferred.resolve(sideBar.find('.js-qc-form'));
		}).fail((error) => {
			progressInstance.progressIndicator({mode: 'hide'});
			app.errorLog(error);
		});
		return aDeferred.promise();
	}

	loadCalendarData(view = this.getCalendarView().fullCalendar('getView')) {
		const self = this;
		let user = [],
			formatDate = CONFIG.dateFormat.toUpperCase(),
			cvid = self.getCurrentCvId(),
			calendarInstance = this.getCalendarView();
		calendarInstance.fullCalendar('removeEvents');
		let progressInstance = $.progressIndicator({blockInfo: {enabled: true}});
		user = self.getSelectedUsersCalendar();
		if (0 === user.length) {
			user = [app.getMainParams('userId')];
		}
		self.clearFilterButton(user, cvid);
		if (view.type === 'agendaDay') {
			self.selectDays(view.start, view.end);
			view.end = view.end.add(1, 'day');
		}
		let options = {
			module: 'Calendar',
			action: 'Calendar',
			mode: 'getEvents',
			start: view.start.format(formatDate),
			end: view.end.format(formatDate),
			user: user,
			time: app.getMainParams('showType'),
			cvid: cvid,
			historyUrl: `index.php?module=Calendar&view=CalendarExtended&history=true&viewType=${view.type}&start=${view.start.format(formatDate)}&end=${view.end.format(formatDate)}&user=${user}&time=${app.getMainParams('showType')}&cvid=${cvid}&hiddenDays=${view.options.hiddenDays}`
		};
		let connectorMethod = window["AppConnector"]["requestPjax"];
		if (this.readonly || (view.options.firstLoad && this.browserHistoryConfig !== null)) {
			options = Object.assign(options, {
				start: this.browserHistoryConfig.start,
				end: this.browserHistoryConfig.end,
				user: this.browserHistoryConfig.user,
				time: this.browserHistoryConfig.time,
				cvid: this.browserHistoryConfig.cvid
			});
			connectorMethod = window["AppConnector"]["request"];
		}
		connectorMethod(options).done((events) => {
			calendarInstance.fullCalendar('removeEvents');
			calendarInstance.fullCalendar('addEventSource', events.result);
			progressInstance.progressIndicator({mode: 'hide'});
		});
		self.registerViewRenderEvents(view);
		view.options.firstLoad = false;
	}

	clearFilterButton(user, cvid) {
		let currentUser = parseInt(app.getMainParams('userId')),
			time = app.getMainParams('showType'),
			statement = ((user.length === 0 || (user.length === 1 && parseInt(user) === currentUser)) && cvid === undefined && time === 'current');
		$(".js-calendar__clear-filters").toggleClass('d-none', statement);
	}

	registerClearFilterButton() {
		const sidebar = this.getSidebarView(),
			calendarView = this.getCalendarView();
		let clearBtn = calendarView.find('.js-calendar__clear-filters');
		app.showPopoverElementView(clearBtn);
		clearBtn.on('click', () => {
			$(".js-calendar__extended-filter-tab a").removeClass('active');
			app.setMainParams('showType', 'current');
			app.moduleCacheSet('defaultShowType', 'current');
			sidebar.find("input:checkbox").prop('checked', false);
			sidebar.find("option:selected").prop('selected', false);
			sidebar.find(".js-input-user-owner-id[value=" + app.getMainParams('userId') + "]").prop('checked', true);
			calendarView.fullCalendar('getCalendar').view.options.loadView();
		});
	}

	generateYearList(dateStart, dateEnd) {
		const thisInstance = this,
			datesView = thisInstance.getDatesRowView();
		let prevYear = moment(dateStart).subtract(1, 'year'),
			actualYear = moment(dateStart),
			nextYear = moment(dateStart).add(1, 'year'),
			html = '',
			active = '';
		while (prevYear <= nextYear) {
			if (prevYear.format('YYYY') === actualYear.format('YYYY')) {
				active = 'active';
			} else {
				active = '';
			}
			html +=
				`<div class="js-sub-record sub-record col-4 nav-item" data-date="${prevYear.format('YYYY')}" data-type="years" data-js="click | class: active">
					<div class="sub-record-content nav-link ${active}">
						<div class="sub-date-name">
							${prevYear.format('YYYY')}
							<div class="js-count-events count badge c-badge--md ml-1" data-js="html">0</div>
						</div>
					</div>
				</div>`;
			prevYear = moment(prevYear).add(1, 'year');
		}
		datesView.find('.js-sub-date-list').html(html);
	}

	generateSubMonthList(dateStart, dateEnd) {
		let datesView = this.getDatesRowView(),
			activeMonth = parseInt(moment(dateStart).locale('en').format('M')) - 1,
			html = '',
			active = '';
		for (let month = 0; 12 > month; ++month) {
			if (month === activeMonth) {
				active = 'active';
			} else {
				active = '';
			}
			html +=
				`<div class="js-sub-record sub-record nav-item col-1 px-0" data-type="months" data-date="${moment(dateStart).month(month).format('YYYY-MM')}" data-js="click | class: active">
					<div class="sub-record-content nav-link ${active}">
						<div class="sub-date-name">${app.vtranslate('JS_' + moment().month(month).format('MMM').toUpperCase()).toUpperCase()}
							<div class="js-count-events count badge c-badge--md ml-1" data-js="html">0</div>
						</div>
					</div>
				</div>`;
		}
		datesView.find('.js-sub-date-list').html(html);
	}

	generateSubWeekList(dateStart, dateEnd) {
		let datesView = this.getDatesRowView(),
			prevWeeks = moment(dateStart).subtract(5, 'weeks'),
			actualWeek = moment(dateStart).format('WW'),
			nextWeeks = moment(dateStart).add(6, 'weeks'),
			html = '';
		while (prevWeeks <= nextWeeks) {
			let active = '';
			if (prevWeeks.format('WW') === actualWeek) {
				active = ' active';
			}
			html += '<div class="js-sub-record sub-record nav-item col-1 px-0" data-type="weeks" data-date="' + prevWeeks.format('YYYY-MM-DD') + '" data-js="click | class: active">' +
				'<div class="sub-record-content nav-link' + active + '">' +
				'<div class="sub-date-name">' + app.vtranslate('JS_WEEK_SHORT') + ' ' + prevWeeks.format('WW') +
				'<div class="js-count-events count badge c-badge--md ml-1" data-js="html">0</div>' +
				'</div>' +
				'</div>' +
				'</div>';
			prevWeeks = moment(prevWeeks).add(1, 'weeks');
		}
		datesView.find('.js-sub-date-list').html(html);
	}

	generateSubDaysList(dateStart, dateEnd) {
		const thisInstance = this;
		let datesView = thisInstance.getDatesRowView(),
			prevDays = moment(dateStart).subtract(5, 'days'),
			actualDay = moment(dateStart).format('DDD'),
			nextDays = moment(dateStart).add(7, 'days'),
			daysToShow = nextDays.diff(prevDays, 'days'),
			html = '';
		for (let day = 0; day < daysToShow; ++day) {
			let active = '';
			if (app.getMainParams('switchingDays') === 'workDays' && app.moduleCacheGet('defaultSwitchingDays') !== 'all') {
				if ($.inArray(prevDays.day(), app.getMainParams('hiddenDays', true)) !== -1) {
					prevDays = moment(prevDays).add(1, 'days');
					daysToShow++;
					continue;
				}
			}
			if (prevDays.format('DDD') === actualDay) {
				active = ' active';
			}
			html += '<div class="js-sub-record sub-record nav-item col-1 px-0" data-type="days" data-date="' + prevDays.format('YYYY-MM-DD') + '" data-js="click | class: active">' +
				'<div class="sub-record-content nav-link' + active + '">' +
				'<div class="sub-date-name">' + app.vtranslate('JS_DAY_SHORT') + ' ' + prevDays.format('DD') +
				'<div class="js-count-events count badge c-badge--md ml-1" data-js="html">0</div>' +
				'</div>' +
				'</div>' +
				'</div>';
			prevDays = moment(prevDays).add(1, 'days');
		}
		datesView.find('.js-sub-date-list').html(html);
	}

	selectDays(startDate, endDate) {
		this.container.find('.js-right-panel-event-link').tab('show');
		let start_hour = $('#start_hour').val(),
			end_hour = $('#end_hour').val(),
			view = this.getCalendarView().fullCalendar('getView');
		if (endDate.hasTime() == false) {
			endDate.add(-1, 'days');
		}
		startDate = startDate.format();
		endDate = endDate.format();
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
				endDate = endDate + 'T' + end_hour + ':00';
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
			let dateFormat = data.find('[name="date_start"]').data('dateFormat').toUpperCase(),
				timeFormat = data.find('[name="time_start"]').data('format'),
				defaultTimeFormat = '';
			if (timeFormat == 24) {
				defaultTimeFormat = 'HH:mm';
			} else {
				defaultTimeFormat = 'hh:mm A';
			}
			data.find('[name="date_start"]').val(moment(startDate).format(dateFormat));
			data.find('[name="due_date"]').val(moment(endDate).format(dateFormat));
			if (data.find('.js-autofill').prop('checked') === true) {
				Calendar_Edit_Js.getInstance().getFreeTime(data);
			} else {
				data.find('[name="time_start"]').val(moment(startDate).format(defaultTimeFormat));
				data.find('[name="time_end"]').val(moment(endDate).format(defaultTimeFormat));
			}
		});
	}

	registerUsersChange(formContainer) {
		formContainer.find('.js-input-user-owner-id-ajax, .js-input-user-owner-id').on('change', () => {
			this.getCalendarView().fullCalendar('getCalendar').view.options.loadView();
		});
		this.registerPinUser();
	}

	/**
	 * Register actions to do after save record
	 * @param instance
	 * @param data
	 * @returns {function}
	 */
	registerAfterSubmitForm(self, data) {
		const calendarView = this.getCalendarView();
		let returnFunction = function (data) {
			if (data.success) {
				let textToShow = app.vtranslate('JS_SAVE_NOTIFY_SUCCESS'),
					recordActivityStatus = data.result.activitystatus.value,
					historyStatus = app.getMainParams('activityStateLabels', true).history,
					inHistoryStatus = $.inArray(recordActivityStatus, historyStatus),
					showType = app.getMainParams('showType');
				if ((-1 !== inHistoryStatus && 'history' === showType) || (-1 === inHistoryStatus && 'history' !== showType)) {
					if (calendarView.fullCalendar('clientEvents', data.result._recordId)[0]) {
						self.updateCalendarEvent(data.result._recordId, data.result);
					} else {
						const calendarInstance = calendarView.fullCalendar('getCalendar');
						if (calendarInstance.view.type !== 'year') {
							calendarInstance.view.options.addCalendarEvent(data.result);
						} else {
							calendarInstance.view.render();
						}
						if (data.result.followup.value !== undefined) {
							calendarView.fullCalendar('removeEvents', data.result.followup.value);
						}
					}
				}
				self.refreshDatesRowView(calendarView.fullCalendar('getView'));
				self.getSidebarView().find('.js-qc-form').html('');
				self.getCalendarCreateView();
				Vtiger_Helper_Js.showPnotify({
					text: textToShow,
					type: 'success',
					animation: 'show'
				});
			}
		};
		return returnFunction;
	}

	openRightPanel() {
		let calendarRightPanel = $('.js-calendar-right-panel');
		if (calendarRightPanel.hasClass('hideSiteBar')) {
			calendarRightPanel.find('.js-toggle-site-bar-right-button').trigger('click');
		}
	}

	showRightPanelForm() {
		let calendarRightPanel = $('.js-calendar-right-panel');
		if (!calendarRightPanel.find('.js-right-panel-event').hasClass('active')) {
			calendarRightPanel.find('.js-right-panel-event-link').trigger('click');
		}
		app.showNewScrollbar(calendarRightPanel.find('.js-calendar__form__wrapper'), {
			suppressScrollX: true
		});
		app.showPopoverElementView(calendarRightPanel.find('.js-popover-tooltip'));
	}

	registerSiteBarEvents() {
		let calendarRightPanel = $('.js-calendar-right-panel');
		calendarRightPanel.find('.js-show-sitebar').on('click', () => {
			if (calendarRightPanel.hasClass('hideSiteBar')) {
				calendarRightPanel.find('.js-toggle-site-bar-right-button').trigger('click');
			}
		});
	}

	loadCalendarCreateView() {
		let aDeferred = $.Deferred();
		AppConnector.request({
			'module': app.getModuleName(),
			'view': 'EventForm',
		}).done((data) => {
			aDeferred.resolve($(data));
		}).fail((error) => {
			aDeferred.reject();
			app.errorLog(error);
		});
		return aDeferred.promise();
	}

	updateCalendarEvent(calendarEventId, eventData) {
		const calendar = this.getCalendarView();
		let recordToUpdate = calendar.fullCalendar('clientEvents', calendarEventId)[0],
			calendarDetails = eventData,
			startDate = calendar.fullCalendar('moment', calendarDetails.date_start.value + ' ' + calendarDetails.time_start.value),
			endDate = calendar.fullCalendar('moment', calendarDetails.due_date.value + ' ' + calendarDetails.time_end.value);
		recordToUpdate.title = calendarDetails.subject.display_value;
		recordToUpdate.start = startDate.format();
		recordToUpdate.end = endDate.format();
		recordToUpdate.url = 'index.php?module=Calendar&view=Detail&record=' + calendarEventId;
		recordToUpdate.ctivitytype = calendarDetails.activitytype.value;
		recordToUpdate.allDay = calendarDetails.allday.value == 'on';
		recordToUpdate.state = calendarDetails.state.value;
		recordToUpdate.vis = calendarDetails.visibility.value;
		recordToUpdate.sta = calendarDetails.activitystatus.value;
		recordToUpdate.className = ['ownerCBg_' + calendarDetails.assigned_user_id.value, 'picklistCBr_Calendar_activitytype_' + calendarDetails.activitytype.value];
		recordToUpdate.start_display = calendarDetails.date_start.display_value + ' ' + calendarDetails.time_start.display_value;
		recordToUpdate.end_display = calendarDetails.due_date.display_value + ' ' + calendarDetails.time_end.display_value;
		recordToUpdate.smownerid = calendarDetails.assigned_user_id.display_value;
		recordToUpdate.pri = calendarDetails.taskpriority.value;
		recordToUpdate.lok = calendarDetails.location.display_value;
		calendar.fullCalendar('updateEvent', recordToUpdate);
	}

	getCalendarCreateView() {
		const thisInstance = this;
		let sideBar = thisInstance.getSidebarView(),
			qcForm = sideBar.find('.js-qc-form'),
			aDeferred = $.Deferred();
		if (qcForm.find('form').length > 0 && qcForm.find('input[name=record]').length === 0) {
			aDeferred.resolve(qcForm);
			return aDeferred.promise();
		}
		let progressInstance = $.progressIndicator({blockInfo: {enabled: true}});
		this.loadCalendarCreateView().done((data) => {
			progressInstance.progressIndicator({mode: 'hide'});
			qcForm.html(data);
			thisInstance.showRightPanelForm();
			let rightFormCreate = $(document).find('form[name="QuickCreate"]'),
				moduleName = sideBar.find('[name="module"]').val(),
				editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(moduleName),
				headerInstance = new Vtiger_Header_Js(),
				params = [];
			App.Fields.Picklist.showSelect2ElementView(sideBar.find('select'));
			editViewInstance.registerBasicEvents(rightFormCreate);
			rightFormCreate.validationEngine(app.validationEngineOptions);
			headerInstance.registerHelpInfo(rightFormCreate);
			params.callbackFunction = thisInstance.registerAfterSubmitForm(thisInstance, data);
			headerInstance.registerQuickCreatePostLoadEvents(rightFormCreate, params);
			$.each(sideBar.find('.ckEditorSource'), function (key, element) {
				let ckEditorInstance = new Vtiger_CkEditor_Js();
				ckEditorInstance.loadCkEditor($(element), {
					height: '5em',
					toolbar: 'Min'
				});
			});
			aDeferred.resolve(qcForm);
		}).fail((error) => {
			progressInstance.progressIndicator({mode: 'hide'});
			app.errorLog(error);
		});
		return aDeferred.promise();
	}

	registerPinUser() {
		$('.js-pin-user').off('click').on('click', function () {
			const thisInstance = $(this);
			AppConnector.request({
				'module': app.getModuleName(),
				'action': 'Calendar',
				'mode': 'pinOrUnpinUser',
				'element_id': thisInstance.data('elementid'),
			}).done((data) => {
				let response = data.result;
				if (response === 'unpin') {
					thisInstance.find('.js-pin-icon').removeClass('fas').addClass('far');
				} else if (response === 'pin') {
					thisInstance.find('.js-pin-icon').removeClass('far').addClass('fas');
				} else {
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_ERROR'));
				}
			});
		});
	}

	registerAddForm() {
		const thisInstance = this;
		let sideBar = thisInstance.getSidebarView();
		thisInstance.getCalendarCreateView();
		AppConnector.request('index.php?module=Calendar&view=RightPanelExtended&mode=getUsersList').done(
			function (data) {
				if (data) {
					let formContainer = sideBar.find('.js-users-form');
					formContainer.html(data);
					thisInstance.registerUsersChange(formContainer);
					App.Fields.Picklist.showSelect2ElementView(formContainer.find('select'));
					app.showNewScrollbar(formContainer, {
						suppressScrollX: true
					});
				}
			}
		);
		AppConnector.request('index.php?module=Calendar&view=RightPanelExtended&mode=getGroupsList').done(
			function (data) {
				if (data) {
					let formContainer = sideBar.find('.js-group-form');
					formContainer.html(data);
					thisInstance.registerUsersChange(formContainer);
					App.Fields.Picklist.showSelect2ElementView(formContainer.find('select'));
					formContainer.addClass('u-min-h-30per');
					app.showNewScrollbar(formContainer, {
						suppressScrollX: true
					});
				}
			}
		);
	}

	/**
	 * Find element on list (user, group)
	 * @param {jQuery.Event} e
	 */
	findElementOnList(e) {
		let target = $(e.target),
			value = target.val().toLowerCase(),
			container = target.closest('.js-filter__container');
		container.find('.js-filter__item__value').filter(function () {
			let item = $(this).closest('.js-filter__item__container');
			if ($(this).text().trim().toLowerCase().indexOf(value) > -1) {
				item.removeClass('d-none');
			} else {
				item.addClass('d-none');
			}
		});
	}

	/**
	 * Register filter for users and groups
	 */
	registerFilterForm() {
		const self = this;
		this.getSidebarView().find('a[data-toggle="tab"]').one('shown.bs.tab', function (e) {
			$(".js-filter__search").on('keyup', self.findElementOnList.bind(self));
		});
	}

	/**
	 * Register events
	 */
	registerEvents() {
		super.registerEvents();
		this.registerAddForm();
		this.registerSiteBarEvents();
		this.registerFilterForm();
		ElementQueries.listen();
	}
}
