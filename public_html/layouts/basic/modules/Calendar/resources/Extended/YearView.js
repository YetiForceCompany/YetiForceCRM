/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
let FC = $.fullCalendar; // a reference to FullCalendar's root namespace
let View = FC.View;      // the class that all views must inherit from

let YearView = View.extend({
	initialize: function () {

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
	loadCalendarData: function (calendar) {
		var thisInstance = this;
		var view = calendar.fullCalendar('getView');
		var formatDate = CONFIG.dateFormat.toUpperCase();
		var start_date = view.start.format(formatDate);
		var end_date = view.end.format(formatDate);
		// let user = Calendar_CalendarExtendedView_Js.getSelectedUsersCalendar();
		// if (user.length === 0) {
		// }
		let user = [app.getMainParams('current_user_id')];

		var params = {
			module: 'Calendar',
			action: 'Calendar',
			mode: 'getEvents',
			start: start_date,
			end: end_date,
			user: user,
			yearView: true
		}
		AppConnector.request(params).done(function (events) {
			var height = (calendar.find('.fc-bg :first').height() - calendar.find('.fc-day-number').height()) - 10;
			var width = (calendar.find('.fc-day-number').width() / 2) - 10;
			for (var i in events.result) {
				events.result[i]['width'] = width;
				events.result[i]['height'] = height;
			}
			calendar.fullCalendar('addEventSource',
				events.result
			);
			calendar.find(".cell-calendar a").on('click', function () {
				var container = thisInstance.getContainer();
				var url = 'index.php?module=Calendar&view=List';
				if (customFilter) {
					url += '&viewname=' + calendar.find('select.widgetFilter.customFilter').val();
				} else {
					url += '&viewname=All';
				}
				url += '&search_params=[[';
				// var owner = calendar.find('.widgetFilter.owner option:selected');
				// if (owner.val() != 'all') {
				// 	url += '["assigned_user_id","e","' + owner.val() + '"],';
				// }
				var date = moment($(this).data('date')).format(CONFIG.dateFormat.toUpperCase())
				window.location.href = url + '["activitytype","e","' + $(this).data('type') + '"],["date_start","bw","' + date + ',' + date + '"]]]';
			});
		});
	},
	render: function () {
		const self = this;
		//common
		let hiddenDays = [];
		if (app.getMainParams('switchingDays') === 'workDays') {
			hiddenDays = app.getMainParams('hiddenDays', true);
		}
		//
		let calendar = $('#calendarview').fullCalendar('getCalendar');
		let yearView = this.el.html(this.renderHtml());
		yearView.find('.fc-year__month').each(function (i) {
			let date = moment(calendar.getDate().year() + '-' + (i + 1), "YYYY-MM-DD");
			let options = {
				defaultView: 'month',
				titleFormat: 'MMMM',
				header: {center: 'title', left: false, right: false},
				height: 'auto',
				defaultDate: date,
				eventRender: function (event, element) {
					element = '<div class="cell-calendar">';
					for (var key in event.event) {
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
			}
			let calendarInstance = $(this).fullCalendar(options);
			self.loadCalendarData(calendarInstance);
		});
	},

});

FC.views.year = YearView; // register our class with the view system
