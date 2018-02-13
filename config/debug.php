<?php
/**
 * Debug config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
$DEBUG_CONFIG = [
    /* +***************************************************************
     * 	Logger
     * ************************************************************** */
    // Enable saving logs to file. Values: false/true
    'LOG_TO_FILE' => false,
    // Enable displaying logs in debug console. Values: false/true
    'LOG_TO_CONSOLE' => false,
    // Enable saving logs profiling.  Values: false/true
    'LOG_TO_PROFILE' => false,
    // Level of saved/displayed logs
    // Values: false = All / 3 = error and warning / ['error', 'warning', 'info', 'trace', 'profile'],
    'LOG_LEVELS' => false,
    // Level of saved/displayed tracerts. // Values: int
    'LOG_TRACE_LEVEL' => 0,
    // Display Main Debug Console
    'DISPLAY_DEBUG_CONSOLE' => false,
    // List of IP addresses allowed to display debug console
    // Values: false = All IPS / '192.168.1.10' / ['192.168.1.10','192.168.1.11']
    'DEBUG_CONSOLE_ALLOWED_IPS' => false,
    // Stop the running process of the system if there is and error in sql query
    'SQL_DIE_ON_ERROR' => false,
    // Debug Viewer => cache/logs/viewer-debug.log
    'DEBUG_VIEWER' => false,
    // Display Smarty Debug Console
    'DISPLAY_DEBUG_VIEWER' => false,
    // migoi
    // Don't show Smarty Notice in phpError.log
    'SMARTY_ERROR_REPORTING' => E_ALL & ~E_NOTICE,
    // / mogoi
    /* +***************************************************************
     * Configure a user-defined error handler function
     * ************************************************************** */
    // Displays information about the tracking code when an error occurs. Available only with the active SQL_DIE_ON_ERROR = true
    'DISPLAY_EXCEPTION_BACKTRACE' => false,
    // Display logs when error exception occurs
    'DISPLAY_EXCEPTION_LOGS' => false,
    // Turn on the error handler
    'EXCEPTION_ERROR_HANDLER' => false,
    // Save logs to file (cache/logs/errors.log)
    'EXCEPTION_ERROR_TO_FILE' => false,
    // Display errors
    'EXCEPTION_ERROR_TO_SHOW' => false,
    // Set the error reporting level. The parameter is either an integer representing a bit field, or named constants.
    // https://secure.php.net/manual/en/errorfunc.configuration.php#ini.error-reporting
    // All errors - E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED
    // Critical errors - E_ERROR | E_WARNING | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR
    'EXCEPTION_ERROR_LEVEL' => E_ALL & ~E_NOTICE,
    /* +***************************************************************
     * 	API
     * ************************************************************** */
    // Sabre dav - This is a flag that allow or not showing file, line and code of the exception in the returned XML
    'DAV_DEBUG_EXCEPTIONS' => false,
    // Activate the plugin recording log in DAV
    'DAV_DEBUG_PLUGIN' => false,
    // Show errors messages in web service
    'WEBSERVICE_SHOW_ERROR' => false,
    // web service logs
    'WEBSERVICE_DEBUG' => false,
    /* +***************************************************************
     * 	Mailer
     * ************************************************************** */
    // Mailer debug
    'MAILER_DEBUG' => false,
    /* +***************************************************************
     * 	ROUNDCUBE MAIL
     * ************************************************************** */
    // System error reporting, sum of: 1 = log; 4 = show, 8 = trace
    'ROUNDCUBE_DEBUG_LEVEL' => 1,
    // Devel_mode this will print real PHP memory usage into logs/console and do not compress JS libraries
    'ROUNDCUBE_DEVEL_MODE' => false,
    // Activate this option if logs should be written to per-user directories.
    // Data will only be logged if a directry cache/logs/<username>/ exists and is writable.
    'ROUNDCUBE_PER_USER_LOGGING' => false,
    // Log sent messages to cache/logs/sendmail or to syslog
    'ROUNDCUBE_SMTP_LOG' => false,
    // Log successful/failed logins to cache/logs/userlogins or to syslog
    'ROUNDCUBE_LOG_LOGINS' => false,
    // Log session authentication errors to cache/logs/session or to syslog
    'ROUNDCUBE_LOG_SESSION' => false,
    // Log SQL queries to cache/logs/sql or to syslog
    'ROUNDCUBE_SQL_DEBUG' => false,
    // Log IMAP conversation to cache/logs/imap or to syslog
    'ROUNDCUBE_IMAP_DEBUG' => false,
    // Log LDAP conversation to cache/logs/ldap or to syslog
    'ROUNDCUBE_LDAP_DEBUG' => false,
    // Log SMTP conversation to cache/logs/smtp or to syslog
    'ROUNDCUBE_SMTP_DEBUG' => false,
];
