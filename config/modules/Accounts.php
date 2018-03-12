<?php
/**
 * Accounts module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	// Columns visible in Account hierarchy [$label => $columnName]
	'COLUMNS_IN_HIERARCHY' => [],
	// Max depth of hierarchy
	'MAX_HIERARCHY_DEPTH' => 50,
	// Count Accounts in hierarchy
	'COUNT_IN_HIERARCHY' => true,
	// Show summary products services bookmark
	'SHOW_SUMMARY_PRODUCTS_SERVICES' => true,
	// Default view for record detail view. Values: LBL_RECORD_DETAILS or LBL_RECORD_SUMMARY
	'DEFAULT_VIEW_RECORD' => 'LBL_RECORD_PREVIEW',
	// Default module view. Values: List, ListPreview or DashBoard, refresh menu files after you change this value
	'defaultViewName' => 'List',
	// Default record view for list preview. Values: full or summary
	'defaultDetailViewName' => 'full',
];
