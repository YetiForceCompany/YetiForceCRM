<?php
/**
 * Components config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'AddressFinder' => [
		'REMAPPING_OPENCAGE' => [
			'type' => 'function',
			'default' => 'return null;',
			'description' => 'Main function to remapping fields for OpenCage. It should be function.'
		],
		'REMAPPING_OPENCAGE_FOR_COUNTRY' => [
			'type' => 'function',
			'default' => "return [
		'Australia' => function (\$row) {
			return [
				'addresslevel1' => [\$row['components']['country'] ?? '', \$row['components']['ISO_3166-1_alpha-2'] ?? ''],
				'addresslevel2' => \$row['components']['state'] ?? '',
				'addresslevel3' => \$row['components']['state_district'] ?? '',
				'addresslevel4' => \$row['components']['county'] ?? '',
				'addresslevel5' => \$row['components']['suburb'] ?? \$row['components']['neighbourhood'] ?? \$row['components']['city_district'] ?? '',
				'addresslevel6' => \$row['components']['city'] ?? \$row['components']['town'] ?? \$row['components']['village'] ?? '',
				'addresslevel7' => \$row['components']['postcode'] ?? '',
				'addresslevel8' => \$row['components']['road'] ?? '',
				'buildingnumber' => \$row['components']['house_number'] ?? '',
				'localnumber' => \$row['components']['local_number'] ?? '',
			];
		},
	];",
			'description' => 'Function to remapping fields in countries for OpenCage. It should be function.'
		],
		'OPENCAGE_COUNTRY_CODE' => [
			'default' => [],
			'description' => "Restricts the results to the specified country or countries.\nThe country code is a two letter code as defined by the ISO 3166-1 Alpha 2\n(https://en.wikipedia.org/wiki/ISO_3166-1_alpha-, It should be array such like ['en','fr']"
		],
	],
	'Backup' => [
		'BACKUP_PATH' => [
			'default' => '',
			'description' => 'Backup catalog path.',
			'validation' => '\App\Fields\File::isAllowedDirectory'
		],
		'EXT_TO_SHOW' => [
			'default' => ['7z', 'bz2', 'gz', 'rar', 'tar', 'tar.bz2', 'tar.gz', 'tar.lzma', 'tbz2', 'tgz', 'zip', 'zipx'],
			'description' => 'Allowed extensions to show on the list.',
		]
	],
	'Dav' => [
		'CALDAV_DEFAULT_VISIBILITY_FROM_DAV' => [
			'default' => false,
			'description' => "Default visibility for events synchronized with CalDAV. Available values: false/'Public'/'Private'\nSetting default value will result in  skipping visibility both ways, default value for both ways will be set.",
		],
		'CALDAV_EXCLUSION_FROM_DAV' => [
			'default' => false,
			'description' => "Rules to set exclusions/omissions in synchronization\nExample. All private entries from CalDAV should not be synchronized: ['visibility' => 'Private']",
		],
		'CALDAV_EXCLUSION_TO_DAV' => [
			'default' => false,
			'description' => 'Exclusions',
		]
	]
];
