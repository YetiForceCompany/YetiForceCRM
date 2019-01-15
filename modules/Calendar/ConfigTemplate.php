<?php
/**
 * Calendar module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
return [
	'WEEK_COUNT' => [
		'default' => true,
		'description' => 'Shows number of the week in the year view: true - show, false - hide',
		'validation' => '\App\Validator::bool'
	],
	'EVENT_LIMIT' => [
		'default' => 10,
		'description' => 'Limits the number of events displayed on a day. Boolean, Integer. default: false',
		'validation' => '\App\Validator::integer'
	],
	'SHOW_TIMELINE_WEEK' => [
		'default' => true,
		'description' => 'Show calendar timeline in monthly view:  false = basicWeek, true = agendaWeek',
		'validation' => '\App\Validator::bool'
	],
	'SHOW_TIMELINE_DAY' => [
		'default' => true,
		'description' => 'Show calendar timeline in day view: false = basicDay, true = agendaDay',
		'validation' => '\App\Validator::bool'
	],
	'DASHBOARD_CALENDAR_WIDGET_FILTER_TYPE' => [
		'default' => 'list',
		'description' => 'Shows the switch button or filter list in the calendar widget: switch - Switch "To realize" and "History", list - filter list',
		'validation' => function () {
			$arg = func_get_arg(0);
			return $arg === 'list' || $arg === 'switch';
		}
	],
	'SHOW_QUICK_CREATE_BY_STATUS' => [
		'default' => [],
		'description' => 'Show the Calendar quick create window after changing the status: array - PLL_COMPLETED, PLL_CANCELLED',
		'validation' => function () {
			$arg = func_get_arg(0);
			return is_array($arg) && empty(array_diff($arg, ['PLL_COMPLETED', 'PLL_CANCELLED']));
		}
	],
	'SHOW_RIGHT_PANEL' => [
		'default' => true,
		'description' => 'Right calendar panel visible by default: true - show right panel, false - hide right panel',
		'validation' => '\App\Validator::bool'
	],
	'SHOW_LIST_BUTTON' => [
		'default' => true,
		'description' => 'Button referring to the list view that includes filters: true - show, false - hide',
		'validation' => '\App\Validator::bool'
	],
	'SHOW_COMPANIES_IN_QUICKCREATE' => [
		'default' => false,
		'description' => 'Show companies and processes in quickcreate',
		'validation' => '\App\Validator::bool'
	],
	'HIDDEN_DAYS_IN_CALENDAR_VIEW' => [
		'default' => [0, 6],
		'description' => 'Exclude certain days-of-the-week from being displayed. The value is an array of day-of-week indices to hide. Each index is zero-base (Sunday=0) and ranges from 0-6. By default, no days are hidden',
		'validation' => function () {
			$arg = func_get_arg(0);
			return $arg === 'Extended' || $arg === 'Standard';
		}
	],
	'SEND_REMINDER_INVITATION' => [
		'default' => true,
		'description' => 'Send mail notification to participants',
		'validation' => '\App\Validator::bool'
	],
	'AUTO_REFRESH_REMINDERS' => [
		'default' => true,
		'description' => ' Auto refresh reminders',
		'validation' => '\App\Validator::bool'
	],
	'SHOW_DAYS_QUICKCREATE' => [
		'default' => true,
		'description' => 'Display days below the form in quick create',
		'validation' => '\App\Validator::bool'
	],
	'CRON_MAX_NUMBERS_ACTIVITY_STATE' => [
		'default' => 5000,
		'description' => 'Max number of records to update status in cron',
		'validation' => '\App\Validator::integer'
	],
	'CRON_MAX_NUMBERS_ACTIVITY_STATS' => [
		'default' => 5000,
		'description' => ' Max number of records to update calendar activity fields in related modules (in cron)',
		'validation' => '\App\Validator::integer'
	],
	'SHOW_ONLY_CURRENT_RECORDS_COUNT' => [
		'default' => false,
		'description' => 'Show number of current records in record preview for related modules',
		'validation' => '\App\Validator::bool'
	],
	'CALENDAR_VIEW' => [
		'default' => 'Extended',
		'description' => 'Calendar view - allowed values: Extended, Standard',
		'validation' => function () {
			$arg = func_get_arg(0);
			return $arg === 'Extended' || $arg === 'Standard';
		}
	],
	'SHOW_ACTIVITY_BUTTONS_IN_EDIT_FORM' => [
		'default' => false,
		'description' => 'Show activity status buttons in edit form',
		'validation' => '\App\Validator::bool'
	],
	'SHOW_EDIT_FORM' => [
		'default' => false,
		'description' => 'Show default edit form',
		'validation' => '\App\Validator::bool'
	],
	'AUTOFILL_TIME' => [
		'default' => false,
		'description' => 'Select event free time automatically',
		'validation' => '\App\Validator::bool'
	],
	'ALL_DAY_SLOT' => [
		'default' => true,
		'description' => 'Shows "all day" row in agendaWeek and agendaDay view',
		'validation' => '\App\Validator::bool'
	],
];
