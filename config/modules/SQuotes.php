<?php
/**
 * SQuotes module config
 * @package YetiForce.Config
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
return [
	// List of fields read from related module
	'INVENTORY_ON_SELECT_AUTO_COMPLETE' => [
		'description' => [
			'ref' => 'getInventoryListName'
		],
		'price' => [
			'ref' => 'getInventoryPrice'
		]
	]
];
