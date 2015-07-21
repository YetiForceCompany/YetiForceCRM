<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 ************************************************************************************/
/* Performance paramters can be configured to fine tune vtiger CRM runtime */
$PERFORMANCE_CONFIG = Array(
	// Should the caller information be captured in SQL Logging?
	// It adds little overhead for performance but will be useful to debug
	'SQL_LOG_INCLUDE_CALLER' => false,

	// If database default charset is UTF-8, set this to true 
	// This avoids executing the SET NAMES SQL for each query!
	'DB_DEFAULT_CHARSET_UTF8' => true,

	// Turn-off default sorting in ListView, could eat up time as data grows
	'LISTVIEW_DEFAULT_SORTING' => false,
	
	// Compute list view record count while loading listview everytime.
	// Recommended value false
	'LISTVIEW_COMPUTE_PAGE_COUNT' => false,
	
	// Display administrators in the list of users (Assigned To)
	'SHOW_ADMINISTRATORS_IN_USERS_LIST' => true,
);
/**
 * Performance perference API
 */
class PerformancePrefs {
	/**
	 * Get performance parameter configured value or default one
	 */
	static function get($key, $defvalue=false) {
		global $PERFORMANCE_CONFIG;
		if(isset($PERFORMANCE_CONFIG)){
			if(isset($PERFORMANCE_CONFIG[$key])) {
				return $PERFORMANCE_CONFIG[$key];
			}
		}
		return $defvalue;
	}
	/** Get boolean value */
	static function getBoolean($key, $defvalue=false) {
		return self::get($key, $defvalue);
	}
	/** Get Integer value */
	static function getInteger($key, $defvalue=false) {
		return intval(self::get($key, $defvalue));
	}
}
