<?php
/**
 * Import module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
return [
	'IMMEDIATE_IMPORT_LIMIT' => [
		'default' => 1000,
		'description' => 'Threshold record limit for immediate import. If record count is more than this, then the import is scheduled through cron job'
	],
	'BATCH_LIMIT' => [
		'default' => 250,
		'description' => 'Individual batch limit - Specified number of records will be imported at one shot and the cycle will repeat till all records are imported'
	],
	'CREATE_REFERENCE_RECORD' => [
		'default' => false,
		'description' => 'Records for reference fields modules are created while importing, when record is not found.'
	],
	'SAVE_BY_HANDLERS' => [
		'default' => true,
		'description' => 'Save record including handlers'
	],
	'ADD_PICKLIST_VALUE' => [
		'default' => true,
		'description' => 'Missing picklist values are added'
	],
];
