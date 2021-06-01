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
		'description' => 'Columns visible in Account hierarchy [$label => $columnName]'
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
		'description' => 'Count Accounts in hierarchy',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'SHOW_SUMMARY_PRODUCTS_SERVICES' => [
		'default' => true,
		'description' => 'Show summary products services bookmark',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'defaultViewName' => [
		'default' => 'List',
		'description' => 'Default module view. Values: List, ListPreview or DashBoard, refresh menu files after you change this value',
		'validation' => function () {
			$arg = func_get_arg(0);
			return 'List' === $arg || 'ListPreview' === $arg || 'DashBoard' === $arg;
		}
	],
	'defaultDetailViewName' => [
		'default' => 'full',
		'description' => 'Default record view for list preview. Values: full or summary',
		'validation' => function () {
			$arg = func_get_arg(0);
			return 'full' === $arg || 'summary' === $arg;
		}
	],
];
