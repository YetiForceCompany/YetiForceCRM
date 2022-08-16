<?php
/**
 * ModComments module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'defaultSource' => [
		'default' => ['current'],
		'description' => "Default record loading.\nValues available: ['current', 'related'].",
		'validation' => function () {
			$arg = func_get_arg(0);
			return \is_array($arg) && !array_diff($arg, ['current', 'related']);
		},
		'docTags' => ['var' => 'string[]'],
	],
	'dateFormat' => [
		'default' => 'user',
		'description' => "Date display mode in comments, values available: \n`user` -  based on user settings, the view_date_format field \n`displayDate` - date and time in user format.",
		'validation' => function () {
			$arg = func_get_arg(0);
			return \is_array($arg) && !array_diff($arg, ['user', 'displayDate']);
		},
		'docTags' => ['var' => 'string'],
	],
];
