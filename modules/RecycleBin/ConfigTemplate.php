<?php
/**
 * RecycleBin module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'DELETE_MAX_COUNT' => [
		'default' => 1000,
		'description' => 'Maximal value of records to delete',
		'validation' => '\App\Validator::naturalNumber'
	]
];
