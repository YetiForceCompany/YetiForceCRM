<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
$CONFIG = [
	// Address URL to route API
	'ADDRESS_TO_ROUTE' => 'http://www.yournavigation.org/api/1.0/gosmore.php',
	// Address URL to seaching API
	'ADDRESS_TO_SEARCH' => 'http://nominatim.openstreetmap.org',
	// Max number to update addresses
	'CRON_MAX_UPDATED_ADDRESSES' => 1000,
	// Allow modules
	'ALLOW_MODULES' => ['Accounts', 'Contacts', 'Competition', 'Vendors', 'Partners', 'Leads'],
	'FIELDS_IN_POPUP' => [
		'Accounts' => ['accountname', 'email1', 'phone'],
		'Leads' => ['company', 'firstname', 'lastname', 'email'],
		'Partners' => ['subject', 'email'],
		'Competition' => ['subject', 'email'],
		'Vendors' => ['vendorname', 'email', 'website'],
		'Contacts' => ['firstname', 'lastname', 'email', 'phone']
	]
];
