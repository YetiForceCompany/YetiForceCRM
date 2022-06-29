<?php
/**
 * Components config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'AddressFinder' => [
		'remappingOpenCage' => [
			'type' => 'function',
			'default' => 'return null;',
			'description' => 'The main function to remapping fields for OpenCage. It should be a function.',
		],
		'remappingOpenCageForCountry' => [
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
			'description' => 'Function to remapping fields in countries for OpenCage. It should be function.',
		],
		'nominatimMapUrlCustomOptions' => [
			'default' => [],
			'description' => "Additional headers for connections with NominatimGeocoder API e.g. \n['auth' => ['username', 'password']]\n['auth' => ['username', 'password', 'digest']]\n['headers' => 'X-KAY' => 'key-x']",
		],
		'nominatimRemapping' => [
			'type' => 'function',
			'default' => 'return null;',
			'description' => 'Main function to remapping fields for NominatimGeocoder. It should be function.',
		],
		'nominatimRemappingForCountry' => [
			'type' => 'function',
			'default' => "return [
			'AU' => function (\$row) {
				return [
					'addresslevel1' => [\$row['address']['country'] ?? '', \$row['address']['country_code'] ?? ''],
					'addresslevel2' => \$row['address']['state'] ?? '',
					'addresslevel3' => \$row['address']['state_district'] ?? '',
					'addresslevel4' => \$row['address']['county'] ?? '',
					'addresslevel5' => \$row['address']['suburb'] ?? \$row['address']['neighbourhood'] ?? \$row['address']['city_district'] ?? '',
					'addresslevel6' => \$row['address']['city'] ?? \$row['address']['town'] ?? \$row['address']['village'] ?? '',
					'addresslevel7' => \$row['address']['postcode'] ?? '',
					'addresslevel8' => \$row['address']['road'] ?? '',
					'buildingnumber' => \$row['address']['house_number'] ?? '',
					'localnumber' => \$row['address']['local_number'] ?? '',
				];
			},
		];",
			'description' => 'Function to remapping fields in countries for Nominatim. It should be a function.',
		],
		'yetiForceRemapping' => [
			'type' => 'function',
			'default' => 'return null;',
			'description' => 'Main function to remapping fields for YetiForceGeocoder. It should be a function.',
		],
		'yetiForceRemappingForCountry' => [
			'type' => 'function',
			'default' => "return [
			'AU' => function (\$row) {
				return [
					'addresslevel1' => [\$row['address']['country'] ?? '', \$row['address']['country_code'] ?? ''],
					'addresslevel2' => \$row['address']['state'] ?? '',
					'addresslevel3' => \$row['address']['state_district'] ?? '',
					'addresslevel4' => \$row['address']['county'] ?? '',
					'addresslevel5' => \$row['address']['suburb'] ?? \$row['address']['neighbourhood'] ?? \$row['address']['city_district'] ?? '',
					'addresslevel6' => \$row['address']['city'] ?? \$row['address']['town'] ?? \$row['address']['village'] ?? '',
					'addresslevel7' => \$row['address']['postcode'] ?? '',
					'addresslevel8' => \$row['address']['road'] ?? '',
					'buildingnumber' => \$row['address']['house_number'] ?? '',
					'localnumber' => \$row['address']['local_number'] ?? '',
				];
			},
		];",
			'description' => 'Function to remapping fields in countries for YetiForceGeocoder. It should be a function.',
		],
	],
	'Backup' => [
		'BACKUP_PATH' => [
			'default' => '',
			'description' => 'Backup catalog path.',
			'validation' => function () {
				$arg = func_get_arg(0);
				return '' === $arg || \App\Fields\File::isAllowedDirectory($arg);
			},
		],
		'EXT_TO_SHOW' => [
			'default' => ['7z', 'bz2', 'gz', 'rar', 'tar', 'tar.bz2', 'tar.gz', 'tar.lzma', 'tbz2', 'tgz', 'zip', 'zipx'],
			'description' => 'Allowed extensions to show on the list.',
		],
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
		],
	],
	'Export' => [
		'BLOCK_NAME' => [
			'default' => true,
			'description' => 'Block names are added to headers',
		],
	],
	'Mail' => [
		'MAILTO_LIMIT' => [
			'default' => 2030,
			'description' => "Recommended configuration\nOutlook = 2030\nThunderbird = 8036\nGMAIL = 8036",
		],
		'RC_COMPOSE_ADDRESS_MODULES' => [
			'default' => ['Accounts', 'Contacts', 'OSSEmployees', 'Leads', 'Vendors', 'Partners', 'Competition'],
			'description' => 'List of modules from which you can choose e-mail address in the mail.',
		],
		'rcListCheckRbl' => [
			'default' => true,
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
			'description' => 'Check the sender on the email list in the mail client',
		],
		'rcDetailCheckRbl' => [
			'default' => true,
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
			'description' => 'Check the sender in the message preview in the mail client',
		],
		'rcListAcceptAutomatically' => [
			'default' => false,
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
			'description' => 'Should the system accept spam reports automatically ?',
		],
		'rcListSendReportAutomatically' => [
			'default' => false,
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
			'description' => 'Should the system send reports automatically to https://soc.yetiforce.com ?',
		],
		'MAILER_REQUIRED_ACCEPTATION_BEFORE_SENDING' => [
			'default' => false,
			'description' => 'Required acceptation before sending mails.',
		],
		'defaultRelationModule' => [
			'default' => '',
			'description' => "Default selected relation module in mail bar.\n@var string Module name",
		],
		'autoCompleteFields' => [
			'default' => [
				'Accounts' => ['accountname' => 'subject'],
				'Leads' => ['lastname' => 'fromNameSecondPart', 'company' => 'fromName'],
				'Vendors' => ['vendorname' => 'subject'],
				'Partners' => ['subject' => 'subject'],
				'Competition' => ['subject' => 'subject'],
				'OSSEmployees' => ['name' => 'fromNameFirstPart', 'last_name' => 'fromNameSecondPart'],
				'Contacts' => ['firstname' => 'fromNameFirstPart', 'lastname' => 'fromNameSecondPart'],
				'SSalesProcesses' => ['subject' => 'subject'],
				'Project' => ['projectname' => 'subject'],
				'ServiceContracts' => ['subject' => 'subject'],
				'Campaigns' => ['campaignname' => 'subject'],
				'FBookkeeping' => ['subject' => 'subject'],
				'HelpDesk' => ['ticket_title' => 'subject'],
				'ProjectMilestone' => ['projectmilestonename' => 'subject'],
				'SQuoteEnquiries' => ['subject' => 'subject'],
				'SRequirementsCards' => ['subject' => 'subject'],
				'SCalculations' => ['subject' => 'subject'],
				'SQuotes' => ['subject' => 'subject'],
				'SSingleOrders' => ['subject' => 'subject'],
				'SRecurringOrders' => ['subject' => 'subject'],
				'FInvoice' => ['subject' => 'subject'],
				'SVendorEnquiries' => ['subject' => 'subject'],
				'ProjectTask' => ['projecttaskname' => 'subject'],
				'Services' => ['servicename' => 'subject'],
				'Products' => ['productname' => 'subject'],
			],
			'description' => "Default auto-complete data from mail bar.\n@var array Map. Example ['Accounts' => ['accountname' => 'subject']]",
		],
		'showEmailsInMassMail' => [
			'default' => false,
			'description' => "Show emails in Mass mail view.\n@var bool",
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
	],
	'YetiForce' => [
		'watchdogUrl' => [
			'default' => '',
			'description' => 'YetiForce watchdog monitor URL',
			'validation' => function () {
				$arg = func_get_arg(0);
				return empty($arg) || \App\Validator::url($arg);
			},
		],
		'domain' => [
			'default' => false,
			'description' => 'CRM system URL',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'phpVersion' => [
			'default' => false,
			'description' => 'PHP version',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'crmVersion' => [
			'default' => false,
			'description' => 'CRM version',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'dbVersion' => [
			'default' => false,
			'description' => 'Database version',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'osVersion' => [
			'default' => false,
			'description' => 'System version',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'sapiVersion' => [
			'default' => false,
			'description' => 'API server version',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'lastCronTime' => [
			'default' => false,
			'description' => 'Last Cron time',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'spaceRoot' => [
			'default' => false,
			'description' => 'Root CRM directory space',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'spaceStorage' => [
			'default' => false,
			'description' => 'Storage directory space',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'spaceTemp' => [
			'default' => false,
			'description' => 'Temporary directory space',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'spaceBackup' => [
			'default' => false,
			'description' => 'Backup directory space',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'updates' => [
			'default' => false,
			'description' => 'System update history',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'security' => [
			'default' => false,
			'description' => 'Security',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'stability' => [
			'default' => false,
			'description' => 'System stability configuration',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'libraries' => [
			'default' => false,
			'description' => 'Support for libraries',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'performance' => [
			'default' => false,
			'description' => 'Performance verification',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'publicDirectoryAccess' => [
			'default' => false,
			'description' => 'Public directory',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'environment' => [
			'default' => false,
			'description' => 'Environment information',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'writableFilesAndFolders' => [
			'default' => false,
			'description' => 'Writable files and folders',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'database' => [
			'default' => false,
			'description' => 'Database information',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'pathVerification' => [
			'default' => false,
			'description' => 'Path verification',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
	],
	'Branding' => [
		'footerName' => [
			'default' => '',
			'description' => 'Footer\'s name',
			'validation' => fn () => true,
			'sanitization' => fn () => \App\Purifier::purify(func_get_arg(0)),
		],
		'urlLinkedIn' => [
			'default' => 'https://www.linkedin.com/groups/8177576',
			'description' => 'LinkedIn URL',
			'validation' => fn () => true,
			'sanitization' => fn () => \App\Purifier::purify(func_get_arg(0)),
		],
		'urlTwitter' => [
			'default' => 'https://twitter.com/YetiForceEN',
			'description' => 'Twitter URL',
			'validation' => fn () => true,
			'sanitization' => fn () => \App\Purifier::purify(func_get_arg(0)),
		],
		'urlFacebook' => [
			'default' => 'https://www.facebook.com/YetiForce-CRM-158646854306054/',
			'description' => 'Facebook URL',
			'validation' => fn () => true,
			'sanitization' => fn () => \App\Purifier::purify(func_get_arg(0)),
		],
	],
	'MeetingService' => [
		'emailTemplateDefault' => [
			'default' => 0,
			'description' => 'Default email templates.',
		],
		'emailTemplateModule' => [
			'default' => [],
			'description' => "List of default email templates.\n@example ['Calendar'=>1]",
		],
	],
	'Phone' => [
		'defaultPhoneCountry' => [
			'default' => true,
			'description' => 'Determines the way the default country in the phone field is downloaded. True retrieves the value from the countries panel, false retrieves the country from the users default language.',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
	],
	'InterestsConflict' => [
		'isActive' => [
			'default' => false,
			'description' => 'Is the conflict of interests functionality enabled?.',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'confirmationTimeInterval' => [
			'default' => '30 day',
			'description' => "Time interval that defines how often the system should force a confirmation about the absence of conflict of interests.\n30 day, 5 weeks, 2 month, 2 years.",
			'validation' => '\App\Validator::alnumSpace',
		],
		'confirmUsersAccess' => [
			'default' => [],
			'description' => 'Access to confirmation panel, users ids',
			'loopValidate' => true,
			'validation' => '\App\Validator::integer',
		],
		'unlockUsersAccess' => [
			'default' => [],
			'description' => 'Email addresses for notifications, users ids',
			'loopValidate' => true,
			'validation' => '\App\Validator::integer',
		],
		'notificationsEmails' => [
			'default' => '',
			'description' => 'Email addresses for notifications.',
			'validation' => '\App\Validator::emails',
		],
		'sendMailAccessRequest' => [
			'default' => false,
			'description' => 'E-mail sent to the person requesting access.',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'sendMailAccessResponse' => [
			'default' => false,
			'description' => 'E-mail sent to the above people.',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool',
		],
		'modules' => [
			'default' => [],
			'description' => 'List of modules where the conflict of interests mechanism is enabled.',
			'validation' => fn () => true,
		],
	],
	'Pdf' => [
		'chromiumBinaryPath' => [
			'default' => '',
			'description' => 'The name or path of the chrome/chromium engine.',
			'docTags' => ['see' => 'https://www.chromium.org/getting-involved/download-chromium', 'var' => 'string'],
		],
		'chromiumBrowserOptions' => [
			'default' => ['noSandbox' => true],
			'description' => 'Chromium browser options available for the browser factory.',
			'docTags' => ['see' => 'https://github.com/chrome-php/chrome#available-options', 'var' => 'array'],
		],
	],
];
