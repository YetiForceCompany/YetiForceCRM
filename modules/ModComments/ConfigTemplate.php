<?php
/**
 * ModComments module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'defaultSource' => [
		'type' => 'string[]',
		'default' => ['current'],
		'description' => "Default record loading.\nValues available: ['current', 'related'].",
	],
	'dateFormat' => [
		'type' => 'string',
		'default' => 'user',
		'description' => "Date display mode in comments, values available: \n`user` -  based on user settings, the view_date_format field \n`displayDate` - date and time in user format.",
	],
];
