{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
{assign var=ACCESSIBLE_USERS value=$CURRENTUSER->getAccessibleUsers()}
{assign var=ACCESSIBLE_GROUPS value=$CURRENTUSER->getAccessibleGroups()}
{assign var=CURRENTUSERID value=$CURRENTUSER->getId()}
<div class="dashboardWidgetHeader">
	<table width="100%" cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<th class="span6">
				<div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}"><b>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle())}</b></div>
			</th>
			<th class="span5">
				{include file="dashboards/SelectAccessibleTemplate.tpl"|@vtemplate_path:$MODULE_NAME}
			</th>
			<th class="widgeticons" align="right">
				{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
			</th>
		</tr>
		<tr>
			<th class="span12 refresh" align="center">
				<span style="position:relative;"></span>
			</th>
		</tr>
	</thead>
	</table>
</div>
<div name="history" class="dashboardWidgetContent">
	{include file="dashboards/CalendarContents.tpl"|@vtemplate_path:$MODULE_NAME WIDGET=$WIDGET}
</div>
<script type='text/javascript'>
	Vtiger_Widget_Js('Vtiger_Calendar_Widget_Js',{},{
	calendarView : false,
	calendarCreateView : false,
	weekDaysArray: {
	Sunday: 0, 
	Monday: 1, 
	Tuesday: 2, 
	Wednesday: 3, 
	Thursday: 4, 
	Friday: 5, 
	Saturday: 6
	},
	registerCalendar: function () {
		var thisInstance = this;
			userDefaultActivityView = 'month';

		//Default time format
		var userDefaultTimeFormat = jQuery('#time_format').val();
		if (userDefaultTimeFormat == 24) {
			userDefaultTimeFormat = 'H(:mm)';
		} else {
			userDefaultTimeFormat = 'h(:mm)tt';
		}

		//Default first day of the week
		var defaultFirstDay = jQuery('#start_day').val();
		var convertedFirstDay = thisInstance.weekDaysArray[defaultFirstDay];

		//Default first hour of the day
		var defaultFirstHour = jQuery('#start_hour').val();
		var explodedTime = defaultFirstHour.split(':');
		defaultFirstHour = explodedTime['0'];

		thisInstance.getCalendarView().fullCalendar({
			header: {
				//left: 'today',
				//center: 'title',
				right: 'prev,next'
			},

			timeFormat: userDefaultTimeFormat,
			axisFormat: userDefaultTimeFormat,
			firstHour: defaultFirstHour,
			firstDay: convertedFirstDay,
			defaultView: userDefaultActivityView,
			editable: false,
			slotMinutes: 15,
			theme: false,
			defaultEventMinutes: 0,
			eventLimit: true,
			allDaySlot: false,
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
    },
loadCalendarData: function (allEvents) {
		var thisInstance = this;
		thisInstance.getCalendarView().fullCalendar('removeEvents');
		var view = thisInstance.getCalendarView().fullCalendar('getView');
		var start_date = view.start.format();
		var end_date = view.end.format();
		
		var parent = this.getContainer();
		var user = parent.find('#owner').val();
		if(user == 'all'){
			user = '';
		}
		var params = {
			module: 'Calendar',
			action: 'Calendar',
			mode: 'getEvents',
			start: start_date,
			end: end_date,
			user: user,
			widget: true
		}
		AppConnector.request(params).then(function (events) {
			var height = (thisInstance.getCalendarView().find('.fc-bg :first').height() - thisInstance.getCalendarView().find('.fc-day-number').height())-10;
			var width = (thisInstance.getCalendarView().find('.fc-day-number').width()/2)-10;
			for(var i in events.result){
				events.result[i]['width'] = width;
				events.result[i]['height'] = height;
			}
			thisInstance.getCalendarView().fullCalendar('addEventSource', 
			events.result
			);
		});

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

	postLoadWidget : function() {
		this.registerCalendar();
		this.loadCalendarData(true);
		this.registerChangeView();
		this.registerFilterChangeEvent();

	},
	refreshWidget : function() {
		var thisInstance = this;
		var refreshContainer = this.getContainer().find('.refresh');
		refreshContainer.progressIndicator({
			'smallLoadingImage' : true
		});
	thisInstance.loadCalendarData();
	refreshContainer.progressIndicator({
	'mode': 'hide'});
	},
});
</script>