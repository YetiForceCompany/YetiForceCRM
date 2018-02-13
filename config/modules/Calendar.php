<?php
/**
 * Calender module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
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
    // list - filter list
    'DASHBOARD_CALENDAR_WIDGET_FILTER_TYPE' => 'list',
    // Show the Event/To Do quick create window after changing the status
    // array - PLL_COMPLETED, PLL_CANCELLED
    'SHOW_QUICK_CREATE_BY_STATUS' => [],
    // Right calendar panel visible by default
    // true - show right panel, false - hide right panel;
    'SHOW_RIGHT_PANEL' => true, // Boolean
    // Button referring to the list view that includes filters
    // true - show, false - hide;
    'SHOW_LIST_BUTTON' => true, // Boolean
    // Show companies and processes in quickcreate
    'SHOW_COMPANIES_IN_QUICKCREATE' => false, // Boolean
    // Exclude certain days-of-the-week from being displayed.
    // The value is an array of day-of-week indices to hide. Each index is zero-base (Sunday=0) and ranges from 0-6.
    // By default, no days are hidden
    'HIDDEN_DAYS_IN_CALENDAR_VIEW' => [0, 6],
    // Send mail notification to participants
    'SEND_REMINDER_INVITATION' => true, // Boolean
    // Auto refresh reminders
    'AUTO_REFRESH_REMINDERS' => true, // Boolean
    // Display days below the form in quick create
    'SHOW_DAYS_QUICKCREATE' => true, // Boolean
    // Max number of records to update status in cron
    'CRON_MAX_NUMBERS_ACTIVITY_STATE' => 5000,
    // Max number of records to update calendar activity fields in related modules (in cron)
    'CRON_MAX_NUMBERS_ACTIVITY_STATS' => 5000,
    // Show number of current records in record preview for related modules
    'SHOW_ONLY_CURRENT_RECORDS_COUNT' => false,
];
