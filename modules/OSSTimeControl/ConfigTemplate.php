<?php
/**
 * OSSTimeControl module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'SHOW_RIGHT_PANEL' => [
		'default' => true,
		'description' => 'Right calendar panel visible by default. true - show right panel, false - hide right panel',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'DISALLOW_LONGER_THAN_24_HOURS' => [
		'default' => true,
		'description' => 'Block creating record if duration exceeds 24 hours',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	]
];
