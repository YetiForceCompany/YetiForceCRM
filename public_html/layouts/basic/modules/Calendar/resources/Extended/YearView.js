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
				<div class="row no-gutters">
					<div class="fc-january fc-year__month col-sm-6 col-lg-4"></div>
					<div class="fc-february fc-year__month col-sm-6 col-lg-4"></div>
					<div class="fc-march fc-year__month col-sm-6 col-lg-4"></div>
					<div class="fc-aprill fc-year__month col-sm-6 col-lg-4"></div>
					<div class="fc-mai fc-year__month col-sm-6 col-lg-4"></div>
					<div class="fc-juni fc-year__month col-sm-6 col-lg-4"></div>
					<div class="fc-juli fc-year__month col-sm-6 col-lg-4"></div>
					<div class="fc-august fc-year__month col-sm-6 col-lg-4"></div>
					<div class="fc-septempber fc-year__month col-sm-6 col-lg-4"></div>
					<div class="fc-october fc-year__month col-sm-6 col-lg-4"></div>
					<div class="fc-november fc-year__month col-sm-6 col-lg-4"></div>
					<div class="fc-december fc-year__month col-sm-6 col-lg-4"></div>
				</div>
			</div>
		`;
	},
	render: function () {

		//common
		let hiddenDays = [];
		if (app.getMainParams('switchingDays') === 'workDays') {
			hiddenDays = app.getMainParams('hiddenDays', true);
		}
		//
		let calendar = $('#calendarview').fullCalendar('getCalendar');
		console.log(this.el);
		console.log($('#calendarview').fullCalendar('getCalendar').getDate().year());
		console.log($('#calendarview').fullCalendar('getCalendar').moment().year());
		// calendar.titleFormat = 'MMMM';
		let yearView = this.el.html(this.renderHtml());
		yearView.find('.fc-year__month').each(function (i) {
			let date = moment(calendar.getDate().year() + '-' + (i + 1), "YYYY-MM-DD");
			let options = {
				defaultView: 'month',
				titleFormat: 'MMMM',
				header: {center: 'title', left: false, right: false},
				height: 'auto',
				defaultDate: date,


				///common
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
				//
			}
			$(this).fullCalendar(options);
		});
	},

});


FC.views.year = YearView; // register our class with the view system
