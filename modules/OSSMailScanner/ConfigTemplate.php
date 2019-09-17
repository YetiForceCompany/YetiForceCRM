<?php
/**
 * OSSMailScanner module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'ONE_MAIL_FOR_MULTIPLE_RECIPIENTS' => [
		'default' => false,
		'description' => 'Add only one mail for multiple recipients, type: boolean',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'SEARCH_PREFIX_IN_BODY' => [
		'default' => true,
		'description' => 'Search prefix in body, type: boolean',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'CREATE_TICKET_WITHOUT_CONTACT' => [
		'default' => true,
		'description' => 'Create ticket when contact and account does not exist, type: boolean',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
];
