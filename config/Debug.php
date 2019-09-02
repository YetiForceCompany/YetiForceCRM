<?php

/**
 * Configuration file.
 * This file is auto-generated.
 *
 * @package Config
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

namespace Config;

/**
 * Configuration Class.
 */
class Debug
{
	/** Enable saving logs to file. Values: false/true */
	public static $LOG_TO_FILE = false;

	/** Enable displaying logs in debug console. Values: false/true */
	public static $LOG_TO_CONSOLE = false;

	/** Enable saving logs profiling.  Values: false/true */
	public static $LOG_TO_PROFILE = false;

	/** Level of saved/displayed logs. Values: false = All / 3 = error and warning / ["error", "warning", "info", "trace", "profile"] */
	public static $LOG_LEVELS = false;

	/** Level of saved/displayed tracerts. // Values: int */
	public static $LOG_TRACE_LEVEL = 0;

	/** Display main debug console */
	public static $DISPLAY_DEBUG_CONSOLE = false;

	/** List of IP addresses allowed to display debug console. Values: false = All IPS / "192.168.1.10" / ["192.168.1.10","192.168.1.11"] */
	public static $DEBUG_CONSOLE_ALLOWED_IPS = false;

	/** Stop the running process of the system if there is and error in sql query */
	public static $SQL_DIE_ON_ERROR = false;

	/** Debug cron => cache/logs/cron/ */
	public static $DEBUG_CRON = false;

	/** Debug Viewer => cache/logs/viewer-debug.log */
	public static $DEBUG_VIEWER = false;

	/** Display Smarty Debug Console */
	public static $DISPLAY_DEBUG_VIEWER = false;

	/** Do not show Smarty Notice in phpError.log */
	public static $SMARTY_ERROR_REPORTING = E_ALL & ~E_NOTICE;

	/** Turn on/off debug errors javascript */
	public static $JS_DEBUG = true;

	/** Displays information about the tracking code when an error occurs. Available only with the active SQL_DIE_ON_ERROR = true */
	public static $DISPLAY_EXCEPTION_BACKTRACE = false;

	/** Display logs when error exception occurs */
	public static $DISPLAY_EXCEPTION_LOGS = false;

	/** Turn on/off the error handler */
	public static $EXCEPTION_ERROR_HANDLER = false;

	/** Save logs to file (cache/logs/errors.log) */
	public static $EXCEPTION_ERROR_TO_FILE = false;

	/** Display errors */
	public static $EXCEPTION_ERROR_TO_SHOW = false;

	/**
	 * Set the error reporting level. The parameter is either an integer representing a bit field, or named constants.
	 * https://secure.php.net/manual/en/errorfunc.configuration.php#ini.error-reporting
	 * All errors - E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED / Critical errors - E_ERROR | E_WARNING | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR
	 */
	public static $EXCEPTION_ERROR_LEVEL = E_ALL & ~E_NOTICE;

	/** API - Sabre dav - This is a flag that allow or not showing file, line and code of the exception in the returned XML */
	public static $DAV_DEBUG_EXCEPTIONS = false;

	/** Activate the plugin recording log in DAV */
	public static $DAV_DEBUG_PLUGIN = false;

	/** Show errors messages in web service */
	public static $WEBSERVICE_SHOW_ERROR = false;

	/** Web service logs */
	public static $WEBSERVICE_DEBUG = false;

	/** Mailer debug */
	public static $MAILER_DEBUG = false;

	/** System error reporting, sum of: 1 = log; 4 = show, 8 = trace */
	public static $ROUNDCUBE_DEBUG_LEVEL = 1;

	/** Devel_mode this will print real PHP memory usage into logs/console and do not compress JS libraries */
	public static $ROUNDCUBE_DEVEL_MODE = false;

	/**
	 * Activate this option if logs should be written to per-user directories.
	 * Data will only be logged if a directry cache/logs/<username>/ exists and is writable.
	 */
	public static $ROUNDCUBE_PER_USER_LOGGING = false;

	/** Log sent messages to cache/logs/sendmail or to syslog */
	public static $ROUNDCUBE_SMTP_LOG = false;

	/** Log successful/failed logins to cache/logs/userlogins or to syslog */
	public static $ROUNDCUBE_LOG_LOGINS = false;

	/** Log session authentication errors to cache/logs/session or to syslog */
	public static $ROUNDCUBE_LOG_SESSION = false;

	/** Log SQL queries to cache/logs/sql or to syslog */
	public static $ROUNDCUBE_SQL_DEBUG = false;

	/** Log IMAP conversation to cache/logs/imap or to syslog */
	public static $ROUNDCUBE_IMAP_DEBUG = false;

	/** Log LDAP conversation to cache/logs/ldap or to syslog */
	public static $ROUNDCUBE_LDAP_DEBUG = false;

	/** Log SMTP conversation to cache/logs/smtp or to syslog */
	public static $ROUNDCUBE_SMTP_DEBUG = false;
}
