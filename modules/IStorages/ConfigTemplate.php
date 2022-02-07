<?php
/**
 * IStorages module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'COLUMNS_IN_HIERARCHY' => [
		'default' => [],
		'description' => 'Columns visible in Storage hierarchy',
	],
	'MAX_HIERARCHY_DEPTH' => [
		'default' => 50,
		'description' => 'Max depth of hierarchy',
		'validation' => '\App\Validator::naturalNumber',
	],
	'COUNT_IN_HIERARCHY' => [
		'default' => true,
		'description' => 'Count in hierarchy',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'allowSetQtyProducts' => [
		'default' => false,
		'description' => 'Does the system allow to edit the product inventory without creating any documents? In order to allow a user to perform changes, you have to grant privileges in the profile.',
		'validation' => '\App\Validator::bool',
	],
];
