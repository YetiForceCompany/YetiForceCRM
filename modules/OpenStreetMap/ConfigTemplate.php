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
		'default' => ['Accounts', 'Contacts', 'Competition', 'Vendors', 'Partners', 'Leads', 'Locations'],
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
			'Locations' => ['subject', 'email']
		],
		'description' => 'List of fields to appear in POP-UP'
	],
	'COORDINATE_CONNECTOR' => [
		'default' => 'OpenStreetMap',
		'description' => 'Name of connector to get coordinates. Value - OpenStreetMap or Base',
		'validation' => function () {
			$arg = func_get_arg(0);
			return \in_array($arg, ['OpenStreetMap', 'Base']);
		}
	],
	'ROUTE_CONNECTOR' => [
		'default' => 'Yours',
		'description' => 'Name of connector to get coordinates  Value - Yours or Base',
		'validation' => function () {
			$arg = func_get_arg(0);
			return \in_array($arg, ['Yours', 'Base']);
		}
	],
	'tileLayerUrlTemplate' => [
		'default' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
		'description' => "Tile layer url template, url used to load and display tile layers on the map.\nFree public servers:.\n\n@see https://wiki.openstreetmap.org/wiki/Tile_servers\n- YetiForce Map: https://yetiforce.com\n- OpenStreetMap: https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png\n- OpenStreetMap German: https://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png\n- OpenStreetMap H.O.T.: https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png\n- OpenStreetMap France: https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png\n- OpenTopoMap: https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png\n\nIn order for the map to be properly loaded the address has to be whitelisted.\n" . '@see \\Config\\Security::$allowedImageDomains in config\\Security.php',
		'validation' => '\App\Validator::url',
	]
];
