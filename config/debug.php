<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

$DEBUG_CONFIG = Array(
	// enable log4php -> cache/logs/system.log
	'LOG4PHP_DEBUG' => FALSE,
	
	// display sql queries in the browser during triggering
	'DISPLAY_SQL_QUERY' => FALSE,

	// stop the running process of the system if there is and error in sql query
	'SQL_DIE_ON_ERROR' => FALSE,
	
	// debug Viewer => cache/logs/viewer-debug.log
	'DEBUG_VIEWER' => FALSE,
	
	// Display Smarty Debug Console
	'DISPLAY_DEBUG_VIEWER' => FALSE,
	
	// sabre dav - This is a flag that allow or not showing file, line and code of the exception in the returned XML
	'DAV_DEBUG_EXCEPTIONS' => FALSE,
	
	'DAV_DEBUG_PLUGIN' => FALSE,
	
	/*+***************************************************************
	 *	ROUNDCUBE 
	 ****************************************************************/
	// system error reporting, sum of: 1 = log; 4 = show, 8 = trace
	'ROUNDCUBE_DEBUG_LEVEL' => 1,
	
	// devel_mode this will print real PHP memory usage into logs/console and do not compress JS libraries
	'ROUNDCUBE_DEVEL_MODE' => FALSE,
	
	// Activate this option if logs should be written to per-user directories.
	// Data will only be logged if a directry cache/logs/<username>/ exists and is writable.
	'ROUNDCUBE_PER_USER_LOGGING' => FALSE,

	// Log sent messages to cache/logs/sendmail or to syslog
	'ROUNDCUBE_SMTP_LOG' => FALSE,

	// Log successful/failed logins to cache/logs/userlogins or to syslog
	'ROUNDCUBE_LOG_LOGINS' => FALSE,

	// Log session authentication errors to cache/logs/session or to syslog
	'ROUNDCUBE_LOG_SESSION' => FALSE,

	// Log SQL queries to cache/logs/sql or to syslog
	'ROUNDCUBE_SQL_DEBUG' => FALSE,

	// Log IMAP conversation to cache/logs/imap or to syslog
	'ROUNDCUBE_IMAP_DEBUG' => FALSE,

	// Log LDAP conversation to cache/logs/ldap or to syslog
	'ROUNDCUBE_LDAP_DEBUG' => FALSE,

	// Log SMTP conversation to cache/logs/smtp or to syslog
	'ROUNDCUBE_SMTP_DEBUG' => FALSE,
);
class SysDebug {
	static function get($key, $defvalue=FALSE) {
		global $DEBUG_CONFIG;
		if(isset($DEBUG_CONFIG)){
			if(isset($DEBUG_CONFIG[$key])) {
				return $DEBUG_CONFIG[$key];
			}
		}
		return $defvalue;
	}
	/** Get boolean value */
	static function getBoolean($key, $defvalue=FALSE) {
		return self::get($key, $defvalue);
	}
}
