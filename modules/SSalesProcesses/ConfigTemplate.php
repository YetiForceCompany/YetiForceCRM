<?php
/**
 * SSalesProcesses module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	],
	'SHOW_SUMMARY_PRODUCTS_SERVICES' => [
		'default' => true,
		'description' => 'Show summary products services bookmark',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'DEFAULT_VIEW_RECORD' => [
		'default' => 'LBL_RECORD_PREVIEW',
		'description' => 'Default view for record detail view. Values: LBL_RECORD_DETAILS or LBL_RECORD_SUMMARY',
		'validation' => function () {
			$arg = func_get_arg(0);
			return in_array($arg, ['LBL_RECORD_PREVIEW', 'LBL_RECORD_SUMMARY', 'LBL_RECORD_DETAILS']);
		}
	]
];
