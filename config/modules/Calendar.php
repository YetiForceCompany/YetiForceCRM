<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
$CONFIG = [
	// Limits the number of events displayed on a day.
	// Boolean, Integer. default: false
	'EVENT_LIMIT' => 10,
	// Show calendar timeline in monthly view
	// false = basicWeek, true = agendaWeek
	'SHOW_TIMELINE_WEEK' => false, // Boolean
	// Show calendar timeline in day view
	// false = basicDay, true = agendaDay
	'SHOW_TIMELINE_DAY' => false, //  Boolean
	// switch - Switch "To realize" and "History", 
	// list - list of status
	'DASHBOARD_CALENDAR_WIDGET_FILTER_TYPE' => 'list',
	// Show the Event/To Do quick create window after changing the status
	// array - PLL_COMPLETED, PLL_CANCELLED
	'SHOW_QUICK_CREATE_BY_STATUS' => [],
	// Right calendar panel visible by default
	// true - show right panel, false - hide right panel;
	'SHOW_RIGHT_PANEL' => true, // Boolean
];
