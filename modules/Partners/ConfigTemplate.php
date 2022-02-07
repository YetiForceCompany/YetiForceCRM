<?php
/**
 * Module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'COLUMNS_IN_HIERARCHY' => [
		'default' => [],
		'description' => 'Columns visible in hierarchy [$label => $columnName]',
	],
	'MAX_HIERARCHY_DEPTH' => [
		'default' => 50,
		'description' => 'Max depth of hierarchy',
		'validation' => '\App\Validator::naturalNumber'
	],
	'COUNT_IN_HIERARCHY' => [
		'default' => true,
		'description' => 'Count in hierarchy',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	]
];
