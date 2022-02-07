<?php
/**
 * Home module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'MAX_NUMBER_NOTIFICATIONS' => [
		'default' => 200,
		'description' => 'Max number of notifications to display, 0 - no limits',
		'validation' => '\App\Validator::naturalNumber',
	]
];
