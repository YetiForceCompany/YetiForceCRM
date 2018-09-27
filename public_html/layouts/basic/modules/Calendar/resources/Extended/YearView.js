/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
var FC = $.fullCalendar; // a reference to FullCalendar's root namespace
var View = FC.View;      // the class that all views must inherit from

var YearView = View.extend({
	isRegisterUsersChangeRegistered: false,
	calendarView: false,
	calendarCreateView: false,
	initialize: function () {
		this.registerFilterTabChange();
		this.registerClearFilterButton();
		this.registerUsersChange();
	},
	renderHtml: function (year) {
		let col2Breakpoint = 'col-xxl-2';
		if ($('#switchingDays').val() === 'all') {
			col2Breakpoint = 'col-xxxl-2';
		}
		return `	
			<div class="h-100 fc-year">
				<div class="fc-year__container row no-gutters">
					<div class="fc-january fc-year__month col-sm-6 col-lg-4 col-xl-3 ${col2Breakpoint}" data-date="${year}-01-01"></div>
					<div class="fc-february fc-year__month col-sm-6 col-lg-4 col-xl-3 ${col2Breakpoint}" data-date="${year}-02-01"></div>
					<div class="fc-march fc-year__month col-sm-6 col-lg-4 col-xl-3 ${col2Breakpoint}" data-date="${year}-03-01"></div>
					<div class="fc-april fc-year__month col-sm-6 col-lg-4 col-xl-3 ${col2Breakpoint}" data-date="${year}-04-01"></div>
					<div class="fc-may fc-year__month col-sm-6 col-lg-4 col-xl-3 ${col2Breakpoint}" data-date="${year}-05-01"></div>
					<div class="fc-june fc-year__month col-sm-6 col-lg-4 col-xl-3 ${col2Breakpoint}" data-date="${year}-06-01"></div>
					<div class="fc-july fc-year__month col-sm-6 col-lg-4 col-xl-3 ${col2Breakpoint}" data-date="${year}-07-01"></div>
					<div class="fc-august fc-year__month col-sm-6 col-lg-4 col-xl-3 ${col2Breakpoint}" data-date="${year}-08-01"></div>
					<div class="fc-september fc-year__month col-sm-6 col-lg-4 col-xl-3 ${col2Breakpoint}" data-date="${year}-09-01"></div>
					<div class="fc-october fc-year__month col-sm-6 col-lg-4 col-xl-3 ${col2Breakpoint}" data-date="${year}-10-01"></div>
					<div class="fc-november fc-year__month col-sm-6 col-lg-4 col-xl-3 ${col2Breakpoint}" data-date="${year}-11-01"></div>
					<div class="fc-december fc-year__month col-sm-6 col-lg-4 col-xl-3 ${col2Breakpoint}" data-date="${year}-12-01"></div>
				</div>
			</div>
		`;
	},
	getCalendarView: function () {
		if (this.calendarView === false) {
			this.calendarView = $('#calendarview');
		}
		return this.calendarView;
	},
	loadCalendarData: function (calendar, events) {
		const thisInstance = this;
		let height = (calendar.find('.fc-bg :first').height() - calendar.find('.fc-day-number').height()) - 10,
			width = (calendar.find('.fc-day-number').width() / 2) - 10,
			i;
		for (i in events.result) {
			events.result[i]['width'] = width;
			events.result[i]['height'] = height;
		}
		calendar.fullCalendar('addEventSource',
			events.result
		);
		calendar.find(".js-show-day").on('click', function () {
			let date = moment($(this).data('date')).format(CONFIG.dateFormat.toUpperCase());
			thisInstance.getCalendarView().fullCalendar('changeView', 'agendaDay', date);
			$(".js-sub-record .sub-active").click();
		});
		calendar.find(".fc-center").on('click', function () {
			let date = moment($(this).closest('[data-date]').data('date')).format(CONFIG.dateFormat.toUpperCase());
			thisInstance.getCalendarView().fullCalendar('changeView', 'month', date);
			$(".js-sub-record .sub-active").click();
		});
	},
	addCalendarEvent() {
		this.render();
	},
	getSidebarView() {
		return $('#rightPanel');
	},
	clearFilterButton(user, cvid) {
		let currentUser = parseInt(app.getMainParams('userId')),
			time = app.getMainParams('showType'),
			statement = ((user.length === 0 || (user.length === 1 && parseInt(user) === currentUser)) && cvid === undefined && time === 'current');
		$(".js-calendar__clear-filters").toggleClass('d-none', statement);
	},
	registerFilterTabChange() {
		const thisInstance = this;
		$(".js-calendar__extended-filter-tab").on('shown.bs.tab', function () {
			thisInstance.render();
		});
	},
	getSelectedUsersCalendar() {
		let selectedUsers = this.getSidebarView().find('.js-inputUserOwnerId:checked'),
			selectedUsersAjax = this.getSidebarView().find('.js-inputUserOwnerIdAjax'),
			users = [];
		if (selectedUsers.length > 0) {
			selectedUsers.each(function () {
				users.push($(this).val());
			});
		} else if (selectedUsersAjax.length > 0) {
			users = this.getSidebarView().find('.js-inputUserOwnerIdAjax').val();
		}
		return users;
	},
	getCurrentCvId() {
		return $(".js-calendar__extended-filter-tab .active").parent('.js-filter-tab').data('cvid');
	},
	registerUsersChange() {
		const thisInstance = this;
		if (!thisInstance.isRegisterUsersChangeRegistered) {
			thisInstance.isRegisterUsersChangeRegistered = true;
			thisInstance.getSidebarView().find('.js-inputUserOwnerId').on('change', () => {
				thisInstance.render();
			});
			thisInstance.getSidebarView().find('.js-inputUserOwnerIdAjax').on('change', () => {
				thisInstance.render();
			});
		}
	},
	registerClearFilterButton() {
		const thisInstance = this,
			sidebar = thisInstance.getSidebarView();
		$(".js-calendar__clear-filters").on('click', () => {
			$(".js-calendar__extended-filter-tab a").removeClass('active');
			$(".js-calendar-switch-container .js-switch").eq(1).find('.js-switch--label-on').click();
			sidebar.find("input:checkbox").prop('checked', false);
			sidebar.find(".js-inputUserOwnerId[value=" + app.getMainParams('userId') + "]").prop('checked', true);
			thisInstance.render();
		});
	},
	showRightPanelForm() {
		if ($('.js-calendarRightPanel').hasClass('hideSiteBar')) {
			$('.js-toggleSiteBarRightButton').trigger('click');
		}
		if (!$('.js-rightPanelEvent').hasClass('active')) {
			$('.js-rightPanelEventLink').trigger('click');
		}
	},
	loadCalendarCreateView() {
		let aDeferred = $.Deferred();
		AppConnector.request({
			'module': app.getModuleName(),
			'view': 'EventForm',
		}).then(
			(data) => {
				aDeferred.resolve($(data));
			},
			() => {
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},
	appendWeekButton() {
		$('.fc-row.fc-week.fc-widget-content').each(function () {
			let date = $(this).find('.fc-day-top').first().data('date');
			$(this).prepend(`<div class="js-show-week fc-year__show-week-btn" data-date="${date}" data-js="click"><span class="fas fa-angle-double-right"></span></div>`);
		});
		this.getCalendarView().find(".js-show-week").on('click', (e) => {
			let date = moment($(e.currentTarget).data('date')).format(CONFIG.dateFormat.toUpperCase());
			this.getCalendarView().fullCalendar('changeView', 'agendaWeek', date);
			$(".js-sub-record .sub-active").click();
		});
	},
	render: function () {
		const self = this;
		let hiddenDays = [],
			calendar = self.getCalendarView().fullCalendar('getCalendar'),
			date = calendar.getDate().year(),
			yearView = this.el.html(this.renderHtml(date)),
			user = this.getSelectedUsersCalendar(),
			progressInstance = $.progressIndicator({blockInfo: {enabled: true}}),
			cvid = this.getCurrentCvId(),
			convertedFirstDay = CONFIG.firstDayOfWeekNo;

		if (app.getMainParams('switchingDays') === 'workDays') {
			hiddenDays = app.getMainParams('hiddenDays', true);
		}
		if (user.length === 0) {
			user = [app.getMainParams('userId')];
		}
		this.clearFilterButton(user, cvid);
		AppConnector.request({
			module: 'Calendar',
			action: 'Calendar',
			mode: 'getEventsYear',
			start: date + '-01-01',
			end: date + '-12-31',
			user: user,
			yearView: true,
			time: app.getMainParams('showType'),
			cvid: cvid
		}).done(function (events) {
			yearView.find('.fc-year__month').each(function (i) {
				self.loadCalendarData($(this).fullCalendar({
					defaultView: 'month',
					titleFormat: 'MMMM',
					header: {center: 'title', left: false, right: false},
					height: 'auto',
					selectable: true,
					firstDay: convertedFirstDay,
					select: function (start, end) {
						self.selectDays(start, end);
					},
					defaultDate: moment(calendar.getDate().year() + '-' + (i + 1), "YYYY-MM-DD"),
					eventRender: function (event, element) {
						if (event.rendering === 'background') {
							element.append(`<span class="${event.icon} mr-1"></span>${event.title}`);
							return element;
						}
						element = `<div class="js-show-day cell-calendar u-cursor-pointer d-flex" data-date="${event.date}" data-js="click">
							<a class="mx-auto" href="#" data-date="${event.date}">
								<span class="badge fc-year__event-badge">&nbsp;&nbsp;</span>
							</a>
						</div>`;
						return element;
					},
					hiddenDays: hiddenDays,
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
						year: app.vtranslate('JS_YEAR'),
						month: app.vtranslate('JS_MONTH'),
						week: app.vtranslate('JS_WEEK'),
						day: app.vtranslate('JS_DAY')
					},
					allDayText: app.vtranslate('JS_ALL_DAY'),
				}), events);
			});
			self.appendWeekButton();
			progressInstance.progressIndicator({mode: 'hide'});
		});
	},
});

FC.views.year = YearView; // register our class with the view system
