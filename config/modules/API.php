<?php
/**
 * API module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	// Default visibility for events synchronized with CalDAV. Available values: false/'Public'/'Private'
	// Setting default value will result in  skipping visibility both ways, default value for both ways will be set.
	'CALDAV_DEFAULT_VISIBILITY_FROM_DAV' => false,
	// Rules to set exclusions/omissions in synchronization
	// Example. All private entries from CalDAV should not be synchronized: ['visibility' => 'Private']
	'CALDAV_EXCLUSION_FROM_DAV' => false,
	'CALDAV_EXCLUSION_TO_DAV' => false,
];
