<?php
/**
 * HelpDesk module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	'DEFAULT_VIEW_RECORD' => [
		'default' => 'LBL_RECORD_PREVIEW',
		'description' => 'Default record view: Value - LBL_RECORD_PREVIEW or LBL_RECORD_SUMMARY or LBL_RECORD_DETAILS',
		'validation' => function () {
			$arg = func_get_arg(0);
			return in_array($arg, ['LBL_RECORD_PREVIEW', 'LBL_RECORD_SUMMARY', 'LBL_RECORD_DETAILS']);
		}
	],
	'CONTACTS_CHECK_EMAIL_OPTOUT' => [
		'default' => true,
		'description' => 'Check email opt-out',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	]
];
