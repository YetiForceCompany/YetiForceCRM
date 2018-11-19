<?php
/**
 * Address Finder config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	// Main function to remapping fields for OpenCage. It should be function
	'REMAPPING_OPENCAGE' => null,
	// Function to remapping fields in countries for OpenCage. It should be function
	'REMAPPING_OPENCAGE_FOR_COUNTRY' => [
		'Australia' => function ($row) {
			return [
				'addresslevel1' => [$row['components']['country'] ?? '', $row['components']['ISO_3166-1_alpha-2'] ?? ''],
				'addresslevel2' => $row['components']['state'] ?? '',
				'addresslevel3' => $row['components']['state_district'] ?? '',
				'addresslevel4' => $row['components']['county'] ?? '',
				'addresslevel5' => $row['components']['suburb'] ?? $row['components']['neighbourhood'] ?? $row['components']['city_district'] ?? '',
				'addresslevel6' => $row['components']['city'] ?? $row['components']['town'] ?? $row['components']['village'] ?? '',
				'addresslevel7' => $row['components']['postcode'] ?? '',
				'addresslevel8' => $row['components']['road'] ?? '',
				'buildingnumber' => $row['components']['house_number'] ?? '',
				'localnumber' => $row['components']['local_number'] ?? '',
			];
		},
	],
];
