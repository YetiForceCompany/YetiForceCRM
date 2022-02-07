<?php
/**
 * HelpDesk module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'CHECK_ACCOUNT_EXISTS' => [
		'default' => true,
		'description' => 'Check if account exists',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'CHECK_SERVICE_CONTRACTS_EXISTS' => [
		'default' => true,
		'description' => 'Check if service contracts exists',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'SHOW_SUMMARY_PRODUCTS_SERVICES' => [
		'default' => true,
		'description' => 'Show summary products and services',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'CONTACTS_CHECK_EMAIL_OPTOUT' => [
		'default' => true,
		'description' => 'Check email opt-out',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'COLUMNS_IN_HIERARCHY' => [
		'default' => [
			'Ticket No' => 'ticket_no',
			'Subject' => 'ticket_title',
			'Status' => 'ticketstatus',
			'Priority' => 'ticketpriorities',
			'Assigned To' => 'assigned_user_id',
			'FL_TOTAL_TIME_H' => 'sum_time',
		],
		'description' => 'Columns visible in HelpDesk hierarchy [$label => $columnName]'
	],
	'MAX_HIERARCHY_DEPTH' => [
		'default' => 50,
		'description' => 'Max depth of hierarchy',
		'validation' => '\App\Validator::naturalNumber',
		'sanitization' => function () {
			return (int) func_get_arg(0);
		}
	],
	'COUNT_IN_HIERARCHY' => [
		'default' => true,
		'description' => 'Count HelpDesk records in hierarchy',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'CHECK_IF_RELATED_TICKETS_ARE_CLOSED' => [
		'default' => true,
		'description' => 'When closing the ticket, check if related tickets are closed',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'CHECK_IF_RECORDS_HAS_TIME_CONTROL' => [
		'default' => true,
		'description' => 'When closing the ticket, check if has time control',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
];
