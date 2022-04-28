<?php
/**
 * Calendar module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
return [
	'WEEK_COUNT' => [
		'default' => true,
		'description' => 'Shows number of the week in the year view: true - show, false - hide',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool',
	],
	'EVENT_LIMIT' => [
		'default' => 10,
		'description' => 'Limits the number of events displayed on a day. Boolean, Integer. default: false',
		'validation' => '\App\Validator::integer',
	],
	'SHOW_TIMELINE_WEEK' => [
		'default' => true,
		'description' => 'Show calendar timeline in monthly view:  false = basicWeek, true = timeGridWeek',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool',
	],
	'SHOW_TIMELINE_DAY' => [
		'default' => true,
		'description' => 'Show calendar timeline in day view: false = basicDay, true = timeGridDay',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool',
	],
	'DASHBOARD_CALENDAR_WIDGET_FILTER_TYPE' => [
		'default' => 'list',
		'description' => 'Shows the switch button or filter list in the calendar widget: switch - Switch "To realize" and "History", list - filter list',
		'validation' => function () {
			$arg = func_get_arg(0);
			return 'list' === $arg || 'switch' === $arg;
		},
	],
	'SHOW_QUICK_CREATE_BY_STATUS' => [
		'default' => [],
		'description' => 'Show the Calendar quick create window after changing the status: array - PLL_COMPLETED, PLL_CANCELLED',
		'validation' => function () {
			$arg = func_get_arg(0);
			return \is_array($arg) && empty(array_diff($arg, ['PLL_COMPLETED', 'PLL_CANCELLED']));
		},
	],
	'SHOW_RIGHT_PANEL' => [
		'default' => true,
		'description' => 'Right calendar panel visible by default: true - show right panel, false - hide right panel',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool',
	],
	'SHOW_LIST_BUTTON' => [
		'default' => true,
		'description' => 'Button referring to the list view that includes filters: true - show, false - hide',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool',
	],
	'SHOW_COMPANIES_IN_QUICKCREATE' => [
		'default' => false,
		'description' => 'Show companies and processes in quickcreate',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool',
	],
	'HIDDEN_DAYS_IN_CALENDAR_VIEW' => [
		'default' => [0, 6],
		'description' => 'Exclude certain days-of-the-week from being displayed. The value is an array of day-of-week indices to hide. Each index is zero-base (Sunday=0) and ranges from 0-6. By default, no days are hidden',
		'validation' => function () {
			$arg = func_get_arg(0);
			return 'Extended' === $arg || 'Standard' === $arg;
		},
	],
	'SEND_REMINDER_INVITATION' => [
		'default' => true,
		'description' => 'Send mail notification to participants',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool',
	],
	'AUTO_REFRESH_REMINDERS' => [
		'default' => true,
		'description' => ' Auto refresh reminders',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool',
	],
	'SHOW_DAYS_QUICKCREATE' => [
		'default' => true,
		'description' => 'Display days below the form in quick create',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool',
	],
	'CRON_MAX_NUMBERS_ACTIVITY_STATE' => [
		'default' => 5000,
		'description' => 'Max number of records to update status in cron',
		'validation' => '\App\Validator::naturalNumber',
		'sanitization' => function () {
			return (int) func_get_arg(0);
		},
	],
	'SHOW_ONLY_CURRENT_RECORDS_COUNT' => [
		'default' => false,
		'description' => 'Show number of current records in record preview for related modules',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool',
	],
	'CALENDAR_VIEW' => [
		'default' => 'Extended',
		'description' => 'Calendar view - allowed values: Extended, Standard, refresh menu files after you change this value',
		'validation' => function () {
			$arg = func_get_arg(0);
			return 'Extended' === $arg || 'Standard' === $arg;
		},
	],
	'SHOW_ACTIVITY_BUTTONS_IN_EDIT_FORM' => [
		'default' => false,
		'description' => 'Show activity status buttons in edit form',
	],
	'SHOW_EDIT_FORM' => [
		'default' => false,
		'description' => 'Show default edit form',
	],
	'AUTOFILL_TIME' => [
		'default' => false,
		'description' => 'Select event free time automatically',
	],
	'ALL_DAY_SLOT' => [
		'default' => true,
		'description' => 'Shows "all day" row in timeGridWeek and timeGridDay view',
	],
	'EXPORT_SUPPORTED_FILE_FORMATS' => [
		'default' => ['LBL_CSV' => 'csv', 'LBL_XML' => 'xml', 'LBL_ICAL' => 'ical'],
		'description' => 'Supported file types for data export.',
	],
	'maxNumberCalendarNotifications' => [
		'default' => 20,
		'description' => 'Max number of notifications to display, 0 - no limits',
		'validation' => '\App\Validator::naturalNumber',
	],
	'SHOW_ACTIVITYTYPES_AS_BUTTONS' => [
		'default' => true,
		'description' => 'Shows activity types as buttons',
	],
	'showPinUser' => [
		'default' => true,
		'description' => 'Whether to display the add to favorite users button',
	],
];
