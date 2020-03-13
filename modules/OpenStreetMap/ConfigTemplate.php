<?php
/**
 * OpenStreetMap module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
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
