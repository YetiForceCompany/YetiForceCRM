<?php
/**
 * OSSTimeControl module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'SHOW_RIGHT_PANEL' => [
		'default' => true,
		'description' => 'Right calendar panel visible by default. true - show right panel, false - hide right panel',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool',
	],
	'showPinUser' => [
		'default' => true,
		'description' => 'Whether to display the add to favorite users button',
	],
];
