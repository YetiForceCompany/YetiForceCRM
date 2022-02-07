<?php
/**
 * SQuotes module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'INVENTORY_ON_SELECT_AUTO_COMPLETE' => [
		'default' => [
			'description' => [
				'ref' => 'getInventoryListName',
			],
			'price' => [
				'ref' => 'getInventoryPrice',
			],
		],
		'description' => 'List of fields read from related module'
	]
];
