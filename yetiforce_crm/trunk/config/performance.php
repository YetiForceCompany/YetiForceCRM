<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/* Performance paramters can be configured to fine tune vtiger CRM runtime */
$PERFORMANCE_CONFIG = Array(
	// Enable log4php debugging only if requried 
	'LOG4PHP_DEBUG' => false,

	// Should the caller information be captured in SQL Logging?
	// It adds little overhead for performance but will be useful to debug
	'SQL_LOG_INCLUDE_CALLER' => false,

	// If database default charset is UTF-8, set this to true 
	// This avoids executing the SET NAMES SQL for each query!
	'DB_DEFAULT_CHARSET_UTF8' => true,

	// Compute record change indication for each record shown on listview
	'LISTVIEW_RECORD_CHANGE_INDICATOR' => false,

	// Turn-off default sorting in ListView, could eat up time as data grows
	'LISTVIEW_DEFAULT_SORTING' => false,
	
	// Compute list view record count while loading listview everytime.
	// Recommended value false
	'LISTVIEW_COMPUTE_PAGE_COUNT' => false,

	// Control DetailView Record Navigation
	'DETAILVIEW_RECORD_NAVIGATION' => true,

	// To control the Email Notifications being sent to the Owner
	'NOTIFY_OWNER_EMAILS' => true,		//By default it is set to true, if it is set to false, then notifications will not be sent
	// reduce number of ajax requests on home page, reduce this value if home page widget dont
	// show value.
	'HOME_PAGE_WIDGET_GROUP_SIZE' => 12,
	//take backup legacy style, whenever an admin user logs out.
	'LOGOUT_BACKUP' => true,
);
?>