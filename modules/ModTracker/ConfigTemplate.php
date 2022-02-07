<?php
/**
 * ModTracker module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'DEFAULT_VIEW' => [
		'default' => 'TimeLine',
		'description' => 'default view in History (Timeline/List)',
		'validation' => function () {
			$arg = func_get_arg(0);
			return \in_array($arg, ['Timeline', 'List']);
		}
	],
	'NUMBER_RECORDS_ON_PAGE' => [
		'default' => 50,
		'description' => 'Number of records on one page',
		'validation' => '\App\Validator::naturalNumber'
	],
	'WATCHDOG' => [
		'default' => true,
		'description' => 'Enable sending notifications for all actions available in changes history. Tracking requires enabling module or record tracking.',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'UNREVIEWED_COUNT' => [
		'default' => true,
		'description' => 'Displays the number of unreviewed changes in record.',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'TEASER_TEXT_LENGTH' => [
		'default' => 100,
		'description' => 'Maximum length of text, only applies to text fields',
		'validation' => '\App\Validator::naturalNumber',
	],
	'REVIEW_CHANGES_LIMIT' => [
		'default' => 50,
		'description' => 'Max number to update records',
		'validation' => '\App\Validator::naturalNumber'
	],
	'REVIEWED_SCHEDULE_LIMIT' => [
		'default' => 1000,
		'description' => 'Max number to update records by cron',
		'validation' => '\App\Validator::naturalNumber'
	],
	'SHOW_TIMELINE_IN_LISTVIEW' => [
		'default' => [],
		'description' => 'Show timeline in list view [module name, ...]'
	],
	'TIMELINE_IN_LISTVIEW_LIMIT' => [
		'default' => 5,
		'description' => 'Limit of records displayed in timeline popup',
		'validation' => '\App\Validator::naturalNumber'
	],
];
