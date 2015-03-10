<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
$GEBUG_CONFIG = Array(
	// enable log4php -> cache/logs/system.log
	'LOG4PHP_DEBUG' => false,
	
	// display PHP errors,
	'DISPLAY_PHP_ERRORS' => false,
	
	// display sql query in the browser when you call
	'DISPLAY_SQL_QUERY' => false,

	//
	'SQL_DIE_ON_ERROR' => false,
	
	// debug Viewer => cache/logs/viewer-debug.log
	'DEBUG_VIEWER' => false,
	
	// display debug popup 
	'DISPLAY_DEBUG_VIEWER' => false,
	
	/*+***************************************************************
	 *	ROUNDCUBE
	 ****************************************************************/
	// system error reporting, sum of: 1 = log; 4 = show, 8 = trace
	'ROUNDCUBE_DEBUG_LEVEL' => 1,
	
	// Activate this option if logs should be written to per-user directories.
	// Data will only be logged if a directry <log_dir>/<username>/ exists and is writable.
	'ROUNDCUBE_PER_USER_LOGGING' => false,

	// Log sent messages to <log_dir>/sendmail or to syslog
	'ROUNDCUBE_SMTP_LOG' => false,

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
);
if($GEBUG_CONFIG['DISPLAY_PHP_ERRORS']){
	ini_set('display_errors','on'); version_compare(PHP_VERSION, '5.4.0') <= 0 ? error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED) : error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
}else{
	ini_set('display_errors','off');version_compare(PHP_VERSION, '5.4.0') <= 0 ? error_reporting(E_WARNING & ~E_NOTICE & ~E_DEPRECATED) : error_reporting(E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
}
class SysDebug {
	static function get($key, $defvalue=false) {
		global $GEBUG_CONFIG;
		if(isset($GEBUG_CONFIG)){
			if(isset($GEBUG_CONFIG[$key])) {
				return $GEBUG_CONFIG[$key];
			}
		}
		return $defvalue;
	}
	/** Get boolean value */
	static function getBoolean($key, $defvalue=false) {
		return self::get($key, $defvalue);
	}
}