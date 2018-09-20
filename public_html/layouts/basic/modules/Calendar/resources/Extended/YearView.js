/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
let FC = $.fullCalendar; // a reference to FullCalendar's root namespace
let View = FC.View;      // the class that all views must inherit from

let YearView = View.extend({
	isRegisterUsersChangeRegistered: false,
	initialize: function () {
		this.registerFilterTabChange();
		this.registerClearFilterButton();
		this.registerUsersChange();
	},
	renderHtml: function () {
		return `	
			<div class="h-100 fc-year">
				<div class="fc-year__container row no-gutters">
					<div class="fc-january fc-year__month col-sm-6 col-xl-4"></div>
					<div class="fc-february fc-year__month col-sm-6 col-xl-4"></div>
					<div class="fc-march fc-year__month col-sm-6 col-xl-4"></div>
					<div class="fc-april fc-year__month col-sm-6 col-xl-4"></div>
					<div class="fc-may fc-year__month col-sm-6 col-xl-4"></div>
					<div class="fc-june fc-year__month col-sm-6 col-xl-4"></div>
					<div class="fc-july fc-year__month col-sm-6 col-xl-4"></div>
					<div class="fc-august fc-year__month col-sm-6 col-xl-4"></div>
					<div class="fc-septempber fc-year__month col-sm-6 col-xl-4"></div>
					<div class="fc-october fc-year__month col-sm-6 col-xl-4"></div>
					<div class="fc-november fc-year__month col-sm-6 col-xl-4"></div>
					<div class="fc-december fc-year__month col-sm-6 col-xl-4"></div>
				</div>
			</div>
		`;
	},
	loadCalendarData: function (calendar, events) {
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
		calendar.find(".cell-calendar a").on('click', function () {
			let url = 'index.php?module=Calendar&view=List';
			//if (customFilter) { TODO: add customFilter
			//	url += '&viewname=' + calendar.find('select.widgetFilter.customFilter').val();
			//} else {
			url += '&viewname=All';
			//}
			url += '&search_params=[[';
			// let owner = calendar.find('.widgetFilter.owner option:selected');
			// if (owner.val() != 'all') {
			// 	url += '["assigned_user_id","e","' + owner.val() + '"],';
			// }
			let date = moment($(this).data('date')).format(CONFIG.dateFormat.toUpperCase())
			window.location.href = url + '["activitytype","e","' + $(this).data('type') + '"],["date_start","bw","' + date + ',' + date + '"]]]';
		});
	},
	getSidebarView() {
		return $('#rightPanel');
	},
	clearFilterButton(user, cvid) {
		let currentUser = parseInt(app.getMainParams('userId')),
			statement = ((user.length === 0 || (user.length === 1 && parseInt(user) === currentUser)) && cvid === undefined);
		$(".js-calendar-clear-filters").toggleClass('d-none', statement);

	},
	registerFilterTabChange() {
		const thisInstance = this;
		$(".js-calendar-extended-filter-tab").on('shown.bs.tab', function () {
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
		return $(".js-calendar-extended-filter-tab .active").parent('.js-filter-tab').data('cvid');
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
		$(".js-calendar-clear-filters").on('click', () => {
			$(".js-calendar-extended-filter-tab a").removeClass('active');
			sidebar.find("input:checkbox").prop('checked', false);
			sidebar.find(".js-inputUserOwnerId[value=" + app.getMainParams('userId') + "]").prop('checked', true);
			thisInstance.render();
		})
	},
	render: function () {
		const self = this;
		let hiddenDays = [],
			calendar = $('#calendarview').fullCalendar('getCalendar'),
			yearView = this.el.html(this.renderHtml()),
			user = this.getSelectedUsersCalendar(),
			date = calendar.getDate().year(),
			progressInstance = $.progressIndicator({blockInfo: {enabled: true}}),
			cvid = this.getCurrentCvId();
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
			mode: 'getEvents',
			start: date + '-01-01',
			end: date + '-12-31',
			user: user,
			yearView: true,
			cvid: cvid
		}).done(function (events) {
			yearView.find('.fc-year__month').each(function (i) {
				self.loadCalendarData($(this).fullCalendar({
					defaultView: 'month',
					titleFormat: 'MMMM',
					header: {center: 'title', left: false, right: false},
					height: 'auto',
					defaultDate: moment(calendar.getDate().year() + '-' + (i + 1), "YYYY-MM-DD"),
					eventRender: function (event, element) {
						element = '<div class="cell-calendar">';
						for (let key in event.event) {
							element += `
							<a class="" href="#" data-date="${event.date}" data-type="${key}" title="${event.event[key].label}">
								<span class="${event.event[key].className} ${event.width <= 20 ? 'small-badge' : ''} ${(event.width >= 24) ? 'big-badge' : ''} badge badge-secondary u-font-size-95per">
									${event.event[key].count}
								</span>
							</a>`;
						}
						element += '</div>';
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
			progressInstance.progressIndicator({mode: 'hide'});
		});
	},

});

FC.views.year = YearView; // register our class with the view system
