<?php
/**
 * OpenStreetMap module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
    // Address URL to route API
    'ADDRESS_TO_ROUTE' => 'http://www.yournavigation.org/api/1.0/gosmore.php',
    // Address URL to seaching API
    'ADDRESS_TO_SEARCH' => 'https://nominatim.openstreetmap.org',
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
        'Contacts' => ['firstname', 'lastname', 'email', 'phone'],
    ],
];
