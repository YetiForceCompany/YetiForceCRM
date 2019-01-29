<?php
/**
 * Notification module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	]
];
