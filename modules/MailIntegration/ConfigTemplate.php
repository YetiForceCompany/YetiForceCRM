<?php
/**
 * MailIntegration module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
return [
	'modulesListQuickCreate' => [
		'default' => [],
		'description' => 'Quick creation of records in the module list',
		'validation' => function () {
			$arg = func_get_arg(0);
			return \is_array($arg) && !array_diff($arg, App\Module::getAllModuleNames());
		}
	],
	'outlookUrls' => [
		'default' => [],
		'description' => 'List of allowed addresses for integration with Outlook',
		'loopValidate' => true,
		'validation' => '\App\Validator::url',
	],
];
