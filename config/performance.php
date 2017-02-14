<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */
/* Performance paramters can be configured to fine tune vtiger CRM runtime */
$PERFORMANCE_CONFIG = [
	//Data caching is about storing some PHP variables in cache and retrieving it later from cache. Drivers: Base, Apcu
	'CACHING_DRIVER' => 'Base',
	// Enable caching of user data
	'ENABLE_CACHING_USERS' => false,
	// Enable caching database instance, accelerate time database connection
	'ENABLE_CACHING_DB_CONNECTION' => false,
	// Should the caller information be captured in SQL Logging?
	// It adds little overhead for performance but will be useful to debug. All data can be found in the table "l_yf_sqltime"
	'SQL_LOG_INCLUDE_CALLER' => false,
	// If database default charset is UTF-8, set this to true 
	// This avoids executing the SET NAMES SQL for each query!
	'DB_DEFAULT_CHARSET_UTF8' => true,
	// Turn-off default sorting in ListView, could eat up time as data grows
	'LISTVIEW_DEFAULT_SORTING' => false,
	// Compute list view record count while loading listview everytime.
	// Recommended value false
	'LISTVIEW_COMPUTE_PAGE_COUNT' => false,
	// Enable automatic records list refreshing while changing the value of the selection list
	'AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE' => true,
	// Show in search engine/filters only users and groups available in records list. It might result in a longer search time.
	'SEARCH_SHOW_OWNER_ONLY_IN_LIST' => true,
	// Time to update number of notifications in seconds
	'INTERVAL_FOR_NOTIFICATION_NUMBER_CHECK' => 10,
	// Search owners by AJAX. We recommend selecting the "true" value if there are numerous users in the system.
	'SEARCH_OWNERS_BY_AJAX' => false,
	// Search roles by AJAX
	'SEARCH_ROLES_BY_AJAX' => false,
	// Search reference by AJAX. We recommend selecting the "true" value if there are numerous users in the system.
	'SEARCH_REFERENCE_BY_AJAX' => false,
	// Max number of exported records
	'MAX_NUMBER_EXPORT_RECORDS' => 500,
	// Minimum number of characters to search for record owner
	'OWNER_MINIMUM_INPUT_LENGTH' => 2,
	// Minimum number of characters to search for role
	'ROLE_MINIMUM_INPUT_LENGTH' => 2,
	// The numbers of emails downloaded during one scanning
	'NUMBERS_EMAILS_DOWNLOADED_DURING_ONE_SCANNING' => 100,
	// In how many records should the global search permissions be updated in cron
	'CRON_MAX_NUMERS_RECORD_PRIVILEGES_UPDATER' => 1000000,
	// In how many records should the address boock be updated in cron
	'CRON_MAX_NUMERS_RECORD_ADDRESS_BOOCK_UPDATER' => 10000,
	// In how many records should the label be updated in cron
	'CRON_MAX_NUMERS_RECORD_LABELS_UPDATER' => 1000,
	// In how many mails should the send in cron (Mailer).
	'CRON_MAX_NUMERS_SENDING_MAILS' => 1000,
	// Parameter that allows to disable file overwriting. After enabling it the system will additionally check whether the file exists in the custom directory.
	// Ex. custom/modules/Assets/Assets.php 
	'LOAD_CUSTOM_FILES' => false,
	//Parameter that determines whether admin panel should be available to admin by default
	'SHOW_ADMIN_PANEL' => false,
	// Display administrators in the list of users (Assigned To)
	'SHOW_ADMINISTRATORS_IN_USERS_LIST' => true,
	//Global search: true/false
	'GLOBAL_SEARCH' => true,
];
