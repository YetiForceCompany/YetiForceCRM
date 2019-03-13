/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
var FC = $.fullCalendar, // a reference to FullCalendar's root namespace
	View = FC.View;      // the class that all views must inherit from

/**
 * Creates fullcalendar's View year subclass
 */
FC.views.year = View.extend({
	calendarView: false,
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
	loadMonthData: function (calendar, events) {
		const thisInstance = this;
		let height = (calendar.find('.fc-bg :first').height() - calendar.find('.fc-day-number').height()) - 10,
			width = (calendar.find('.fc-day-number').width() / 2) - 10,
			i;
		for (i in events.result) {
			events.result[i]['width'] = width;
			events.result[i]['height'] = height;
		}
		calendar.fullCalendar('addEventSource', events.result);
		calendar.find(".js-show-day").on('click', function () {
			let date = moment($(this).data('date')).format(CONFIG.dateFormat.toUpperCase());
			thisInstance.getCalendarView().fullCalendar('changeView', 'agendaDay', date);
			$(".js-sub-record .active").click();
		});
		calendar.find(".fc-center").on('click', function () {
			let date = moment($(this).closest('[data-date]').data('date')).format(CONFIG.dateFormat.toUpperCase());
			thisInstance.getCalendarView().fullCalendar('changeView', 'month', date);
			$(".js-sub-record .active").click();
		});
	},
	appendWeekButton() {
		$('.fc-row.fc-week.fc-widget-content').each(function () {
			let date = $(this).find('.fc-day-top').first().data('date');
			let actualWeek = moment(date).format('WW');
			$(this).prepend(`<div class="js-show-week js-popover-tooltip fc-year__show-week-btn" data-toggle="popover" data-date="${date}" data-content="${app.vtranslate('JS_WEEK')} ${actualWeek}" role="tooltip" data-js="click | popover">${actualWeek}</div>`);
		});
		this.getCalendarView().find(".js-show-week").on('click', (e) => {
			$(e.currentTarget).popover('hide');
			let date = moment($(e.currentTarget).data('date')).format(CONFIG.dateFormat.toUpperCase());
			this.getCalendarView().fullCalendar('changeView', 'agendaWeek', date);
			$(".js-sub-record .active").click();
		});
	},
	render: function () {
		const self = this;
		let calendar = self.getCalendarView().fullCalendar('getCalendar'),
			date = calendar.getDate().year(),
			yearView = this.el.html(this.renderHtml(date)),
			user = this.getSelectedUsersCalendar(),
			progressInstance = $.progressIndicator({blockInfo: {enabled: true}}),
			cvid = this.getCurrentCvId();
		if (user.length === 0) {
			user = [app.getMainParams('userId')];
		}
		let dateFormat = CONFIG.dateFormat.toUpperCase();
		this.clearFilterButton(user, cvid);
		let options = {
			module: 'Calendar',
			action: 'Calendar',
			mode: 'getEventsYear',
			start: moment(date + '-01-01').format(dateFormat),
			end: moment(date + '-12-31').format(dateFormat),
			user: user,
			yearView: true,
			time: app.getMainParams('showType'),
			cvid: cvid,
			historyUrl: `index.php?module=Calendar&view=CalendarExtended&history=true&viewType=${calendar.view.type}&start=${moment(date + '-01-01').format(dateFormat)}&end=${moment(date + '-12-31').format(dateFormat)}&user=${user}&time=${app.getMainParams('showType')}&cvid=${cvid}&hiddenDays=${calendar.view.options.hiddenDays}`
		};
		let connectorMethod = window["AppConnector"]["request"];
		if (!this.readonly && window.calendarLoaded) {
			connectorMethod = window["AppConnector"]["requestPjax"];
		}
		if (this.browserHistoryConfig && Object.keys(this.browserHistoryConfig).length && window.calendarLoaded) {
			options = Object.assign(options, {
				start: moment(this.browserHistoryConfig.start).format(dateFormat),
				end: moment(this.browserHistoryConfig.end).format(dateFormat),
				user: this.browserHistoryConfig.user,
				time: this.browserHistoryConfig.time,
				cvid: this.browserHistoryConfig.cvid
			});
			connectorMethod = window["AppConnector"]["request"];
			app.setMainParams('showType', this.browserHistoryConfig.time);
			app.setMainParams('usersId', this.browserHistoryConfig.user);
		}
		connectorMethod(options).done(function (events) {
			yearView.find('.fc-year__month').each(function (i) {
				let calendarInstance = new Calendar_Calendar_Js(self.container, self.readonly);
				let basicOptions = calendarInstance.setCalendarMinimalOptions(),
					monthOptions = {
						defaultView: 'month',
						titleFormat: 'MMMM',
						header: {center: 'title', left: false, right: false},
						height: 'auto',
						select: function (start, end) {
							self.selectDays(start, end);
						},
						hiddenDays: calendar.view.options.hiddenDays,
						defaultDate: moment(calendar.getDate().year() + '-' + (i + 1), "YYYY-MM-DD"),
						eventRender: function (event, element) {
							if (event.rendering === 'background') {
								element.append(`<span class="js-popover-tooltip" data-content="${event.title}" data-toggle="popover"><span class="${event.icon}"></span></span>`);
								return element;
							}
							event.countShow = '99+';
							if (event.count < 100) {
								event.countShow = event.count;
							}
							element = `<div class="js-show-day cell-calendar u-cursor-pointer d-flex" data-date="${event.date}" data-js="click">
							<a class="fc-year__show-day-btn mx-auto" href="#" data-date="${event.date}" title="${event.count}">
								  <span class="fc-year__show-day-btn__container">
									<span class="fas fa-calendar fa-lg"></span>
									<span class="fc-year__show-day-btn__text fa-inverse u-font-weight-700">${event.countShow}</span>
								  </span>	
							</a>
						</div>`;
							return element;
						},
					};
				self.loadMonthData($(this).fullCalendar($.extend(basicOptions, monthOptions)), events);
			});
			if (app.getMainParams('weekCount') === '1') {
				self.appendWeekButton();
			}
			let yearViewContainer = self.container.find('.fc-view-container').first();
			yearViewContainer.height($(window).height() - yearViewContainer.offset().top - $('.js-footer').height()).addClass('u-overflow-y-auto u-overflow-x-hidden');
			progressInstance.progressIndicator({mode: 'hide'});
		});
		this.registerTodayButtonYearChange(calendar);
		this.registerViewRenderEvents(calendar.view);
		window.calendarLoaded = true;
	},

	/**
	 * Function extends today button functionality for year view
	 * @param {jQuery} calendar
	 */
	registerTodayButtonYearChange(calendar) {
		if (calendar.currentDate.format('YYYY') === moment().format('YYYY')) {
			calendar.el.find('.fc-today-button').addClass('fc-state-disabled');
		} else {
			calendar.el.find('.fc-today-button').removeClass('fc-state-disabled');
		}
	}
});
