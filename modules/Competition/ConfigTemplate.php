<?php
/**
 * Competition module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'COLUMNS_IN_HIERARCHY' => [
		'default' => ['LBL_SUBJECT' => 'subject', 'Email' => 'email', 'Vat ID' => 'vat_id', 'LBL_ASSIGNED_TO' => 'assigned_user_id'],
		'description' => 'Columns visible in hierarchy [$label => $columnName]'
	],
	'MAX_HIERARCHY_DEPTH' => [
		'default' => 50,
		'description' => 'Max depth of hierarchy',
		'validation' => '\App\Validator::naturalNumber'
	],
	'COUNT_IN_HIERARCHY' => [
		'default' => true,
		'description' => 'Count Sales in hierarchy',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	]
];
