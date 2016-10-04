<?php
/* The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html */
$CONFIG = [
	// Default visibility for events synchronized with CalDAV. Available values: false/'Public'/'Private'
	// Setting default value will result in  skipping visibility both ways, default value for both ways will be set.
	'CALDAV_DEFAULT_VISIBILITY_FROM_DAV' => false,
	// Rules to set exclusions/omissions in synchronization
	// Example. All private entries from CalDAV should not be synchronized: ['visibility' => 'Private']
	'CALDAV_EXCLUSION_FROM_DAV' => false,
	'CALDAV_EXCLUSION_TO_DAV' => false,
];
