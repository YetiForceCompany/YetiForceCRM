<?php
/**
 * Home module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'MAX_NUMBER_NOTIFICATIONS' => [
		'default' => 200,
		'description' => 'Max number of notifications to display, 0 - no limits',
		'validation' => '\App\Validator::naturalNumber',
	]
];
