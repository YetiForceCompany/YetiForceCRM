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
	
	// display PHP errors,
	'DISPLAY_PHP_ERRORS' => true,
	
	// ************  LOGGING/DEBUGGING  ************
	// --- ROUNDCUBE ---
	// system error reporting, sum of: 1 = log; 4 = show, 8 = trace
	'ROUNDCUBE_DEBUG_LEVEL' => 1,
	
	// Activate this option if logs should be written to per-user directories.
	// Data will only be logged if a directry <log_dir>/<username>/ exists and is writable.
	'ROUNDCUBE_PER_USER_LOGGING' => false,

	// Log sent messages to <log_dir>/sendmail or to syslog
	'ROUNDCUBE_SMTP_LOG' => true,

	// Log successful/failed logins to <log_dir>/userlogins or to syslog
	'ROUNDCUBE_LOG_LOGINS' => false,

	// Log session authentication errors to <log_dir>/session or to syslog
	'ROUNDCUBE_LOG_SESSION' => false,

	// Log SQL queries to <log_dir>/sql or to syslog
	'ROUNDCUBE_SQL_DEBUG' => false,

	// Log IMAP conversation to <log_dir>/imap or to syslog
	'ROUNDCUBE_IMAP_DEBUG' => false,

	// Log LDAP conversation to <log_dir>/ldap or to syslog
	'ROUNDCUBE_LDAP_DEBUG' => false,

	// Log SMTP conversation to <log_dir>/smtp or to syslog
	'ROUNDCUBE_SMTP_DEBUG' => false,
	
	// ************************
	
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
);
if($PERFORMANCE_CONFIG['DISPLAY_PHP_ERRORS']){
	ini_set('display_errors','on'); version_compare(PHP_VERSION, '5.4.0') <= 0 ? error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED) : error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
}else{
	ini_set('display_errors','off');version_compare(PHP_VERSION, '5.4.0') <= 0 ? error_reporting(E_WARNING & ~E_NOTICE & ~E_DEPRECATED) : error_reporting(E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
}
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