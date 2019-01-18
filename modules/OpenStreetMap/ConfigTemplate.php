<?php
/**
 * OpenStreetMap module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'ADDRESS_TO_ROUTE' => [
		'default' => 'http://www.yournavigation.org/api/1.0/gosmore.php',
		'description' => 'Address URL to route API',
		'validation' => '\App\Validator::standard'
	],
	'ADDRESS_TO_SEARCH' => [
		'default' => 'https://nominatim.openstreetmap.org',
		'description' => 'Address URL to searching API',
		'validation' => '\App\Validator::standard'
	],
	'CRON_MAX_UPDATED_ADDRESSES' => [
		'default' => 1000,
		'description' => 'Max number to update addresses',
		'validation' => '\App\Validator::naturalNumber'
	],
	'ALLOW_MODULES' => [
		'default' => ['Accounts', 'Contacts', 'Competition', 'Vendors', 'Partners', 'Leads'],
		'description' => 'Allow modules'
	],
	'FIELDS_IN_POPUP' => [
		'default' => [
			'Accounts' => ['accountname', 'email1', 'phone'],
			'Leads' => ['company', 'firstname', 'lastname', 'email'],
			'Partners' => ['subject', 'email'],
			'Competition' => ['subject', 'email'],
			'Vendors' => ['vendorname', 'email', 'website'],
			'Contacts' => ['firstname', 'lastname', 'email', 'phone'],
		],
		'description' => 'List of fields to appear in POP-UP'
	],
	'COORDINATE_CONNECTOR' => [
		'default' => 'OpenStreetMap',
		'description' => 'Name of connector to get coordinates. Value - OpenStreetMap or Base',
		'validation' => function () {
			$arg = func_get_arg(0);
			return in_array($arg, ['OpenStreetMap', 'Base']);
		}
	],
	'ROUTE_CONNECTOR' => [
		'default' => 'Yours',
		'description' => 'Name of connector to get coordinates  Value - Yours or Base',
		'validation' => function () {
			$arg = func_get_arg(0);
			return in_array($arg, ['Yours', 'Base']);
		}
	]
];
