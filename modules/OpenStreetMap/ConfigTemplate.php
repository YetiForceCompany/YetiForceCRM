<?php
/**
 * OpenStreetMap module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'cronMaxUpdatedAddresses' => [
		'default' => 1000,
		'description' => 'Number of entries to be updated in one run of cron',
		'validation' => '\App\Validator::naturalNumber',
		'docTags' => ['var' => 'int'],
	],
	'mapModules' => [
		'default' => ['Accounts', 'Contacts', 'Competition', 'Vendors', 'Partners', 'Leads', 'Locations'],
		'description' => 'Allow modules.',
		'validation' => function () {
			$arg = func_get_arg(0);
			return \is_array($arg) && \count($arg) === \count(array_filter($arg, fn ($v) => \App\Validator::alnum($v)));
		},
		'docTags' => ['var' => 'string[]'],
	],
	'mapPinFields' => [
		'default' => [
			'Accounts' => ['accountname', 'email1', 'phone'],
			'Leads' => ['company', 'firstname', 'lastname', 'email'],
			'Partners' => ['subject', 'email'],
			'Competition' => ['subject', 'email'],
			'Vendors' => ['vendorname', 'email', 'website'],
			'Contacts' => ['firstname', 'lastname', 'email', 'phone'],
			'Locations' => ['subject', 'email']
		],
		'description' => 'List of fields from which to show information in the map pin',
		'validation' => function () {
			$arg = func_get_arg(0);
			return \is_array($arg) && \count($arg) === \count(array_filter($arg, fn ($v, $k) => \App\Validator::alnum($k) && \is_array($v) && \count($v) === \count(array_filter($v, fn ($i) => \App\Validator::alnum($i))), ARRAY_FILTER_USE_BOTH));
		},
		'docTags' => ['var' => 'array'],
	],
	'coordinatesServer' => [
		'default' => 'YetiForce',
		'description' => 'Name of connector to get coordinates.',
		'validation' => '\App\Validator::text',
	],
	'coordinatesServers' => [
		'default' => [
			'YetiForce' => ['driverName' => 'YetiForce', 'apiUrl' => 'YetiForce'],
		],
		'description' => "List of available coordinate servers, free list of servers is available on page https://wiki.openstreetmap.org/wiki/Search_engines\n Value: 'server name' => ['driverName' => 'Nominatim', 'apiUrl' => 'https://nominatim.openstreetmap.org', 'docUrl' => 'https://wiki.openstreetmap.org/wiki/Nominatim']",
	],
	'routingServer' => [
		'default' => 'YetiForce',
		'description' => 'Name of connector to get routing.',
		'validation' => '\App\Validator::text',
	],
	'routingServers' => [
		'default' => [
			'YetiForce' => ['driverName' => 'YetiForce', 'apiUrl' => 'YetiForce', 'params' => ['preference' => 'fastest', 'profile' => 'driving-car', 'units' => 'km']],
		],
		'description' => "List of available routing servers, free list of servers is available on page https://wiki.openstreetmap.org/wiki/Routing/online_routers\n Value: 'server name' => ['driverName' => 'xxx', 'apiUrl' => 'https://xxx.org', 'docUrl' => 'https://xxx', 'params' => []]",
	],
	'tileLayerServer' => [
		'default' => 'YetiForce',
		'description' => 'Tile layer url template, url used to load and display tile layers on the map.',
		'validation' => '\App\Validator::text',
	],
	'tileLayerServers' => [
		'default' => [
			'YetiForce' => 'YetiForce'
		],
		'description' => "List of available Tile layer servers, free list of servers is available on page https://wiki.openstreetmap.org/wiki/Tile_servers\n Value: 'server name' => 'Tile layer url template'",
	],
];
