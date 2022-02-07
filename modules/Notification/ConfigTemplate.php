<?php
/**
 * Notification module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'AUTO_REFRESH_REMINDERS' => [
		'default' => true,
		'description' => 'Auto refresh reminders in header',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'AUTO_MARK_NOTIFICATIONS_READ_AFTER_EMAIL_SEND' => [
		'default' => true,
		'description' => 'Auto mark notifications as readed after send emails to users',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'MAX_NUMBER_NOTIFICATIONS' => [
		'default' => 200,
		'description' => 'Max number of notifications to display, 0 - no limits',
		'validation' => '\App\Validator::naturalNumber',
	]
];
