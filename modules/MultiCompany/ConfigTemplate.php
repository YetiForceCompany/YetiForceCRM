<?php
/**
 * MultiCompany module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'COLUMNS_IN_HIERARCHY' => [
		'default' => [],
		'description' => 'Columns visible in Sales hierarchy [$label => $columnName]',
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
