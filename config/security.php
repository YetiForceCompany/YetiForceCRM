<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
$SECURITY_CONFIG = [
// Possible to reset the password while logging in (true/false)
	'RESET_LOGIN_PASSWORD' => false,
	// Show my preferences
	'SHOW_MY_PREFERENCES' => false,
	// Changing the settings by the user is possible true/false
	'CHANGE_LOGIN_PASSWORD' => true,
	/* Permissions mechanism
	  The list of system permission levels can be found below */
	'PERMITTED_BY_ROLES' => true,
	'PERMITTED_BY_SHARING' => true,
	'PERMITTED_BY_SHARED_OWNERS' => true,
	'PERMITTED_BY_RECORD_HIERARCHY' => true,
	// Restricted domains allow you to block saving an email address from a given domain in the system. 
	// Restricted domains work only for email address type fields.
	'RESTRICTED_DOMAINS_ACTIVE' => false,
	// Restricted domains
	'RESTRICTED_DOMAINS_VALUES' => [],
	// List of modules where restricted domains are enabled, if empty it will be enabled everywhere.
	'RESTRICTED_DOMAINS_ALLOWED' => [],
	//List of modules excluded from restricted domains validation.
	'RESTRICTED_DOMAINS_EXCLUDED' => ['OSSEmployees'],
];
