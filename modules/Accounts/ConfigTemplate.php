<?php
/**
 * Accounts module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
return [
	'COLUMNS_IN_HIERARCHY' => [
		'default' => [],
		'description' => 'Columns visible in Account hierarchy [$label => $columnName]',
		'validation' => '\App\Validator::isArray' // TODO
	],
	'MAX_HIERARCHY_DEPTH' => [
		'default' => 50,
		'description' => 'Max depth of hierarchy',
		'validation' => '\App\Validator::isInteger' // TODO
	],
	'COUNT_IN_HIERARCHY' => [
		'default' => true,
		'description' => 'Count Accounts in hierarchy',
		'validation' => '\App\Validator::isBool'
	],
	'SHOW_SUMMARY_PRODUCTS_SERVICES' => [
		'default' => true,
		'description' => 'Show summary products services bookmark',
		'validation' => '\App\Validator::isBool'
	],
	'DEFAULT_VIEW_RECORD' => [
		'default' => 'LBL_RECORD_PREVIEW',
		'description' => 'Default view for record detail view. Values: LBL_RECORD_DETAILS or LBL_RECORD_SUMMARY',
		'validation' => '\App\Validator::standard'
	],
	'defaultViewName' => [
		'default' => 'List',
		'description' => 'Default module view. Values: List, ListPreview or DashBoard, refresh menu files after you change this value, refresh menu files after you change this value',
		'validation' => '\App\Validator::standard'
	],
	'defaultDetailViewName' => [
		'default' => 'full',
		'description' => 'Default record view for list preview. Values: full or summary',
		'validation' => '\App\Validator::standard'
	],
];
