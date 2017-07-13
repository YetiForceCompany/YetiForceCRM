<?php
/**
 * Accounts module config
 * @package YetiForce.Config
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
return[
	// List of date and time fields that can be updated by current system time, via button visible in record preview.
	// [Label => Name] 
	'FIELD_TO_UPDATE_BY_BUTTON' => [
	],
	// Columns visible in Account hierarchy [$label => $columnName]
	'COLUMNS_IN_HIERARCHY' => [],
	// Max depth of hierarchy
	'MAX_HIERARCHY_DEPTH' => 50,
	// Count Accounts in hierarchy
	'COUNT_IN_HIERARCHY' => true,
	'HIDE_SUMMARY_PRODUCTS_SERVICES' => false,
	'DEFAULT_VIEW_RECORD' => 'LBL_RECORD_PREVIEW',
];
