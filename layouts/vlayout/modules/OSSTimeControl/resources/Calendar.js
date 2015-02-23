/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery.Class("OSSTimeControl_Calendar_Js",{
	registerUserListWidget : function(){
		jQuery('#OSSTimeControl_sideBar_LBL_USERS').css('overflow','visible');
		app.changeSelectElementView(jQuery('.calendarUserList').find('select'));
		$(".calendarUserList .refreshCalendar").click(function () {
			var thisInstance = new OSSTimeControl_Calendar_Js();
			thisInstance.loadCalendarData();
		});
	},
},{
	calendarView : false,
	calendarCreateView : false,
	registerCalendar: function () {
		var thisInstance = this;
		var weekDaysArray = {Sunday: 0, Monday: 1, Tuesday: 2, Wednesday: 3, Thursday: 4, Friday: 5, Saturday: 6};
		//User preferred default view
		var userDefaultActivityView = jQuery('#activity_view').val();
		if (userDefaultActivityView == 'Today') {
			userDefaultActivityView = 'agendaDay';
		} else if (userDefaultActivityView == 'This Week') {
			userDefaultActivityView = 'agendaWeek';
		} else {
			userDefaultActivityView = 'month';
		}

		//Default time format
		var userDefaultTimeFormat = jQuery('#time_format').val();
		if (userDefaultTimeFormat == 24) {
			userDefaultTimeFormat = 'H(:mm)';
		} else {
			userDefaultTimeFormat = 'h(:mm)tt';
		}

		//Default first day of the week
		var defaultFirstDay = jQuery('#start_day').val();
		var convertedFirstDay = weekDaysArray[defaultFirstDay];

		//Default first hour of the day
		var defaultFirstHour = jQuery('#start_hour').val();
		var explodedTime = defaultFirstHour.split(':');
		defaultFirstHour = explodedTime['0'];

		//Date format in agenda view must respect user preference
		var dateFormat = jQuery('#date_format').val();
		//Converting to fullcalendar accepting date format
		thisInstance.getCalendarView().fullCalendar({
			header: {
				left: 'month,agendaWeek,agendaDay',
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
			eventLimit: true,
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
			eventLimitText: app.vtranslate('JS_MORE'),
			dayClick : function(date, allDay, jsEvent, view){
				thisInstance.dayClick(date.format(), allDay, jsEvent, view);
			},
			eventDrop: function (event, delta, revertFunc) {
				//thisInstance.updateEvent(event);
			},
		});
    },
	loadCalendarData : function() {
		var progressInstance = jQuery.progressIndicator();
		var thisInstance = this;
		thisInstance.getCalendarView().fullCalendar('removeEvents');
		var view = thisInstance.getCalendarView().fullCalendar('getView');
		var start_date = view.start.format();
		var end_date  = view.end.format();
		console.log();
		var user = '';
		if(jQuery('#calendarUserList').length == 0){
			user = jQuery('#current_user_id').val();
		}else{
			user = jQuery('#calendarUserList').val();
		}
		var params = {
			module: 'OSSTimeControl',
			action: 'Calendar',
			start: start_date,
			end: end_date,
			user: user,
		}
		AppConnector.request(params).then(function(events){
			thisInstance.getCalendarView().fullCalendar('addEventSource', events.result);
			progressInstance.hide();
		});
	},
	dayClick: function (date, allDay, jsEvent, view) {
		var thisInstance = this;
		this.getCalendarCreateView().then(function (data) {
			if (data.length <= 0) {
				return;
			}
			var dateFormat = data.find('[name="date_start"]').data('dateFormat');
			var startDateInstance = Date.parse(date);
			var startDateString = app.getDateInVtigerFormat(dateFormat, startDateInstance);
			var startTimeString = startDateInstance.toString('hh:mm tt');
			var endDateInstance = Date.parse(date);
			var endDateString = app.getDateInVtigerFormat(dateFormat, endDateInstance);
			var endTimeString = endDateInstance.toString('hh:mm tt');

			data.find('[name="date_start"]').val(startDateString);
			data.find('[name="due_date"]').val(endDateString);
			data.find('[name="time_start"]').val(startTimeString);
			data.find('[name="time_end"]').val(endTimeString);

			var headerInstance = new Vtiger_Header_Js();
			headerInstance.handleQuickCreateData(data, {callbackFunction: function (data) {
				thisInstance.addCalendarEvent(data.result);
			}});
			jQuery('.modal-body').css({'max-height': '500px', 'overflow-y': 'auto'});
		});
	},
	getCalendarCreateView : function() {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();

		if(this.calendarCreateView !== false) {
			aDeferred.resolve(this.calendarCreateView.clone(true,true));
			return aDeferred.promise();
		}
		var progressInstance = jQuery.progressIndicator();
		this.loadCalendarCreateView().then(
			function(data){
				progressInstance.hide();
				thisInstance.calendarCreateView = data;
				aDeferred.resolve(data.clone(true,true));
			},
			function(){
				progressInstance.hide();
			}
		);
		return aDeferred.promise();
	},

	loadCalendarCreateView : function() {
		var aDeferred  = jQuery.Deferred();
		var quickCreateCalendarElement = jQuery('#quickCreateModules').find('[data-name="OSSTimeControl"]');
		var url = quickCreateCalendarElement.data('url');
		var name = quickCreateCalendarElement.data('name');
		var headerInstance = new Vtiger_Header_Js();
		headerInstance.getQuickCreateForm(url,name).then(
			function(data){
				aDeferred.resolve(jQuery(data));
			},
			function(){
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},
	
	updateEvent : function(event){
		 console.log(event);
	},
	getCalendarView : function(){
		if(this.calendarView == false) {
			this.calendarView = jQuery('#calendarview');
		}
		return this.calendarView;
	},
	registerChangeView : function(){
		var thisInstance = this;
		$("#calendarview button.fc-button").click(function () {
			thisInstance.loadCalendarData();
		});
	},
	registerEvents : function() {
		this.registerCalendar();
		this.loadCalendarData();
		this.registerChangeView();
	}
});