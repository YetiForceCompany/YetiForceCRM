<?php
/**
 * Main config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'api' => [
		'enabledServices' => [
			'default' => [],
			'description' => 'List of active services. Available: dav, webservices, webservice',
			'validation' => function () {
				$arg = func_get_arg(0);
				return is_array($arg) && empty(array_diff($arg, ['dav', 'webservices', 'webservice']));
			}
		],
		'enableBrowser' => [
			'default' => false,
			'description' => 'Dav configuration. Available: false, true',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'enableCardDAV' => [
			'default' => false,
			'description' => 'Dav configuration. Available: false, true',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'enableCalDAV' => [
			'default' => false,
			'description' => 'Dav configuration. Available: false, true',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'enableWebDAV' => [
			'default' => false,
			'description' => 'Dav configuration. Available: false, true',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'ENCRYPT_DATA_TRANSFER' => [
			'default' => false,
			'description' => 'Webservice config. Available: false, true',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'AUTH_METHOD' => [
			'default' => 'Basic',
			'description' => 'Webservice config.',
			'validation' => function () {
				return func_get_arg(0) === 'Basic';
			}
		],
		'PRIVATE_KEY' => [
			'default' => 'config/private.key',
			'description' => 'Webservice config.'
		],
		'PUBLIC_KEY' => [
			'default' => 'config/public.key',
			'description' => 'Webservice config.'
		]
	],
	'main' => [
//		'CALENDAR_DISPLAY' => [
//			'default' => 'true',
//			'description' => 'Show or hide calendar, world clock, calculator, chat and CKEditor
// Do NOT remove the quotes if you set these to false!'
//		],
//		'WORLD_CLOCK_DISPLAY' => [
//			'default' => 'true',
//			'description' => ''
//		],
//		'CALCULATOR_DISPLAY' => [
//			'default' => 'true',
//			'description' => ''
//		],
//		'CHAT_DISPLAY' => [
//			'default' => 'true',
//			'description' => ''
//		],
		'USE_RTE' => [
			'default' => 'true',
			'description' => '',
			'validation' => ''
		],
		'PORTAL_URL' => [
			'default' => 'https://portal.yetiforce.com',
			'description' => 'Url for customer portal (Example: https://portal.yetiforce.com/)',
			'validation' => ''
		],
		'HELPDESK_SUPPORT_NAME' => [
			'default' => 'your-support name',
			'description' => 'Helpdesk support email id and support name (Example: "support@yetiforce.com" and "yetiforce support")',
			'validation' => ''
		],
		'HELPDESK_SUPPORT_EMAIL_REPLY' => [
			'default' => '',
			'description' => '',
			'validation' => ''
		],
		'db_server' => [
			'default' => '_DBC_SERVER_',
			'description' => '',
			'validation' => ''
		], 'db_port' => [
			'default' => '_DBC_PORT_',
			'description' => '',
			'validation' => ''
		],
		'db_username' => [
			'default' => '_DBC_USER_',
			'description' => '',
			'validation' => ''
		],
		'db_password' => [
			'default' => '_DBC_PASS_',
			'description' => '',
			'validation' => ''
		],
		'db_name' => [
			'default' => '_DBC_NAME_',
			'description' => '',
			'validation' => ''
		],
		'db_type' => [
			'default' => '_DBC_TYPE_',
			'description' => '',
			'validation' => ''
		],
		'db_status' => [
			'default' => '_DB_STAT_',
			'description' => '',
			'validation' => ''
		],
		'db_hostname' => [
			'type' => 'function',
			'default' => 'return self::$db_server . ":" . self::$db_port;',
			'description' => 'Gets host name'
		],
		'site_URL' => [
			'default' => '_SITE_URL_',
			'description' => 'Backslash is required at the end of URL',
			'validation' => ''
		],
		'cache_dir' => [
			'default' => 'cache/',
			'description' => 'Cache directory path'
		],
		'tmp_dir' => [
			'type' => 'function',
			'default' => 'return self::$cache_dir . "images/";',
			'description' => 'Default value prepended by cache_dir = images/',
		],
		'import_dir' => [
			'default' => 'cache/import/',
			'description' => 'Import_dir default value prepended by cache_dir = import/',
			'validation' => ''
		],
		'upload_dir' => [
			'default' => 'cache/upload/',
			'description' => '',
			'validation' => ''
		],
		'upload_maxsize' => [
			'default' => 52428800,
			'description' => 'Maximum file size for uploaded files in bytes also used when uploading import files: upload_maxsize default value = 3000000',
			'validation' => '\App\Purifier::naturalNumber'
		],
		'allow_exports' => [
			'default' => 'all',
			'description' => 'Flag to allow export functionality: "all" - to allow anyone to use exports, "admin" - to only allow admins to export, "none" -  to block exports completely',
			'validation' => ''
		],
		'upload_badext' => [
			'default' => ['php', 'php3', 'php4', 'php5', 'pl', 'cgi', 'py', 'asp', 'cfm', 'js', 'vbs', 'html', 'htm', 'exe', 'bin', 'bat', 'sh', 'dll', 'phps', 'phtml', 'xhtml', 'rb', 'msi', 'jsp', 'shtml', 'sth', 'shtm'],
			'description' => 'Files with one of these extensions will have ".txt" appended to their filename on upload: efault value = php, php3, php4, php5, pl, cgi, py, asp, cfm, js, vbs, html, htm',
			'validation' => ''
		],
		'list_max_entries_per_page' => [
			'default' => 20,
			'description' => 'List max entries per page: default value = 20',
			'validation' => '\App\Purifier::naturalNumber'
		],
//		'limitpage_navigation' => [
//			'default' => '5',
//			'description' => 'Limit page navigation: default value = 5'
//		],
		'history_max_viewed' => [
			'default' => '5',
			'description' => 'History max viewed: default value = 5',
			'validation' => ''
		],
		'default_module' => [
			'default' => 'Home',
			'description' => 'Default module: default value = Home',
			'validation' => ''
		],
		'default_action' => [
			'default' => 'index',
			'description' => 'Default action: default value = index',
			'validation' => ''
		],
		'default_theme' => [
			'default' => 'twilight',
			'description' => 'Set default theme: default value = blue',
			'validation' => ''
		],
		'default_user_name' => [
			'default' => '',
			'description' => 'Default text that is placed initially in the login form for user name',
			'validation' => ''
		],
		'default_charset' => [
			'default' => '_VT_CHARSET_',
			'description' => 'Default charset:  default value = "UTF-8" or "ISO-8859-1"',
			'validation' => ''
		],
		'default_language' => [
			'default' => '_LANG_',
			'description' => 'Default language: default value = en-US',
			'validation' => ''
		],
		'translation_string_prefix' => [
			'default' => false,
			'description' => 'Add the language pack name to every translation string in the display: default value = false',
			'validation' => '\App\Validator::bool'
		],
		'cache_tab_perms' => [
			'default' => true,
			'description' => '',
			'validation' => '\App\Validator::bool'
		],
		'display_empty_home_blocks' => [
			'default' => false,
			'description' => 'Option to hide empty home blocks if no entries.',
			'validation' => '\App\Validator::bool'
		],
		'disable_stats_tracking' => [
			'default' => false,
			'description' => 'Disable Stat Tracking of vtiger CRM instance',
			'validation' => '\App\Validator::bool'
		],
		'application_unique_key' => [
			'default' => '_VT_APP_UNIQKEY_',
			'description' => 'Generating Unique Application Key',
			'validation' => ''
		],
		'listview_max_textlength' => [
			'default' => 40,
			'description' => 'Trim descriptions, titles in listviews to this value',
			'validation' => '\App\Purifier::naturalNumber'
		],
		'php_max_execution_time' => [
			'default' => 0,
			'description' => 'Maximum time limit for PHP script execution (in seconds)',
			'validation' => '\App\Purifier::naturalNumber'
		],
		'default_timezone' => [
			'default' => '_TIMEZONE_',
			'description' => 'Set the default timezone as per your preference',
			'validation' => ''
		],
		'title_max_length' => [
			'default' => 60,
			'description' => 'Maximum length of characters for title',
			'validation' => '\App\Purifier::naturalNumber'
		],
		'href_max_length' => [
			'default' => 35,
			'description' => 'Maximum length for href tag',
			'validation' => '\App\Purifier::naturalNumber'
		],
		'breadcrumbs' => [
			'default' => true,
			'description' => 'Should menu breadcrumbs be visible? true = show, false = hide',
			'validation' => '\App\Validator::bool'
		],
		'breadcrumbs_separator' => [
			'default' => '>',
			'description' => 'Separator for menu breadcrumbs default value = ">"',
			'validation' => ''
		],
		'MINIMUM_CRON_FREQUENCY' => [
			'default' => 1,
			'description' => 'Minimum cron frequency [min]',
			'validation' => '\App\Purifier::naturalNumber'
		],
		'session_regenerate_id' => [
			'default' => true,
			'description' => 'Update the current session id with a newly generated one after login',
			'validation' => '\App\Validator::bool'
		],
		'davStorageDir' => [
			'default' => 'storage/Files',
			'description' => 'Update the current session id with a newly generated one after login',
			'validation' => ''
		],
		'davHistoryDir' => [
			'default' => 'storage/FilesHistory',
			'description' => '',
			'validation' => ''
		],
		'systemMode' => [
			'default' => 'prod',
			'description' => 'System mode. Available: prod, demo, test',
			'validation' => function () {
				$arg = func_get_arg(0);
				return in_array($arg, ['prod', 'demo', 'test']);
			}
		],
		'forceSSL' => [
			'default' => false,
			'description' => 'Force site access to always occur under SSL (https) for selected areas. You will not be able to access selected areas under non-ssl. Note, you must have SSL enabled on your server to utilise this option.',
			'validation' => '\App\Validator::bool'
		],
		'listMaxEntriesMassEdit' => [
			'default' => 500,
			'description' => 'Maximum number of records in a mass edition',
			'validation' => '\App\Purifier::naturalNumber'
		],
		'backgroundClosingModal' => [
			'default' => true,
			'description' => 'Enable closing of mondal window by clicking on the background',
			'validation' => '\App\Validator::bool'
		],
		'csrfProtection' => [
			'default' => true,
			'description' => 'Enable CSRF protection',
			'validation' => '\App\Validator::bool'
		],
		'isActiveSendingMails' => [
			'default' => true,
			'description' => 'Is sending emails active.',
			'validation' => '\App\Validator::bool'
		],
		'unblockedTimeoutCronTasks' => [
			'default' => true,
			'description' => 'Should the task in cron be unblocked if the script execution time was exceeded',
			'validation' => '\App\Validator::bool'
		],
		'maxExecutionCronTime' => [
			'default' => 3600,
			'description' => 'The maximum time of executing a cron. Recommended same as the max_exacution_time parameter value.',
			'validation' => '\App\Purifier::naturalNumber'
		],
		'langInLoginView' => [
			'default' => false,
			'description' => "System's language selection in the login window (true/false).",
			'validation' => '\App\Validator::bool',
		],
		'layoutInLoginView' => [
			'default' => false,
			'description' => "System's lyout selection in the login window (true/false)",
			'validation' => '\App\Validator::bool'
		],
		'defaultLayout' => [
			'default' => 'basic',
			'description' => 'Set the default layout',
			'validation' => ''
		],
		'forceRedirect' => [
			'default' => true,
			'description' => 'Redirect to proper url when wrong url is entered.',
			'validation' => '\App\Validator::bool'
		],
		'phoneFieldAdvancedVerification' => [
			'default' => true,
			'description' => 'Enable advanced phone number validation. Enabling  it will block saving invalid phone number.',
			'validation' => '\App\Validator::bool'
		],
	],
	'debug' => [
		'LOG_TO_FILE' => [
			'default' => false,
			'description' => 'Enable saving logs to file. Values: false/true',
			'validation' => '\App\Validator::bool'
		],
		'LOG_TO_CONSOLE' => [
			'default' => false,
			'description' => 'Enable displaying logs in debug console. Values: false/true',
			'validation' => '\App\Validator::bool'
		],
		'LOG_TO_PROFILE' => [
			'default' => false,
			'description' => 'Enable saving logs profiling.  Values: false/true',
			'validation' => '\App\Validator::bool'
		],
		'LOG_LEVELS' => [
			'default' => false,
			'description' => 'Level of saved/displayed logs. Values: false = All / 3 = error and warning / ["error", "warning", "info", "trace", "profile"]',
			'validation' => ''
		],
		'LOG_TRACE_LEVEL' => [
			'default' => 0,
			'description' => 'Level of saved/displayed tracerts. // Values: int',
			'validation' => '\App\Validator::naturalNumber'
		],
		'DISPLAY_DEBUG_CONSOLE' => [
			'default' => false,
			'description' => 'Display main debug console',
			'validation' => '\App\Validator::bool'
		],
		'DEBUG_CONSOLE_ALLOWED_IPS' => [
			'default' => false,
			'description' => 'List of IP addresses allowed to display debug console. Values: false = All IPS / "192.168.1.10" / ["192.168.1.10","192.168.1.11"]',
			'validation' => ''
		],
		'SQL_DIE_ON_ERROR' => [
			'default' => false,
			'description' => 'Stop the running process of the system if there is and error in sql query',
			'validation' => '\App\Validator::bool'
		],
		'DEBUG_CRON' => [
			'default' => false,
			'description' => 'Debug cron => cache/logs/cron/',
			'validation' => '\App\Validator::bool'
		],
		'DEBUG_VIEWER' => [
			'default' => true,
			'description' => 'Debug Viewer => cache/logs/viewer-debug.log',
			'validation' => '\App\Validator::bool'
		],
		'DISPLAY_DEBUG_VIEWER' => [
			'default' => false,
			'description' => 'Display Smarty Debug Console',
			'validation' => '\App\Validator::bool'
		],
		'SMARTY_ERROR_REPORTING' => [
			'default' => E_ALL & ~E_NOTICE,
			'description' => 'Do not show Smarty Notice in phpError.log',
			'validation' => ''
		],
		'JS_DEBUG' => [
			'default' => true,
			'description' => 'Turn on/off debug errors javascript',
			'validation' => '\App\Validator::bool'
		],
		'DISPLAY_EXCEPTION_BACKTRACE' => [
			'default' => false,
			'description' => 'Displays information about the tracking code when an error occurs. Available only with the active SQL_DIE_ON_ERROR = true',
			'validation' => '\App\Validator::bool'
		],
		'DISPLAY_EXCEPTION_LOGS' => [
			'default' => false,
			'description' => 'Display logs when error exception occurs',
			'validation' => '\App\Validator::bool'
		],
		'EXCEPTION_ERROR_HANDLER' => [
			'default' => false,
			'description' => 'Turn on/off the error handler',
			'validation' => '\App\Validator::bool'
		],
		'EXCEPTION_ERROR_TO_FILE' => [
			'default' => false,
			'description' => 'Save logs to file (cache/logs/errors.log)',
			'validation' => '\App\Validator::bool'
		],
		'EXCEPTION_ERROR_TO_SHOW' => [
			'default' => false,
			'description' => 'Display errors',
			'validation' => '\App\Validator::bool'
		],
		'EXCEPTION_ERROR_LEVEL' => [
			'default' => E_ALL & ~E_NOTICE,
			'description' => 'Set the error reporting level. The parameter is either an integer representing a bit field, or named constants. https://secure.php.net/manual/en/errorfunc.configuration.php#ini.error-reporting / All errors - E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED / Critical errors - E_ERROR | E_WARNING | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR',
			'validation' => ''
		],
		'DAV_DEBUG_EXCEPTIONS' => [
			'default' => false,
			'description' => 'API - Sabre dav - This is a flag that allow or not showing file, line and code of the exception in the returned XML',
			'validation' => '\App\Validator::bool'
		], 'DAV_DEBUG_PLUGIN' => [
			'default' => false,
			'description' => 'Activate the plugin recording log in DAV',
			'validation' => '\App\Validator::bool'
		],
		'WEBSERVICE_SHOW_ERROR' => [
			'default' => false,
			'description' => 'Show errors messages in web service',
			'validation' => '\App\Validator::bool'
		],
		'WEBSERVICE_DEBUG' => [
			'default' => false,
			'description' => 'Web service logs',
			'validation' => '\App\Validator::bool'
		],
		'MAILER_DEBUG' => [
			'default' => false,
			'description' => 'Mailer debug',
			'validation' => '\App\Validator::bool'
		],
		'ROUNDCUBE_DEBUG_LEVEL' => [
			'default' => 1,
			'description' => 'System error reporting, sum of: 1 = log; 4 = show, 8 = trace',
			'validation' => '\App\Validator::naturalNumber'
		],

		'ROUNDCUBE_DEVEL_MODE' => [
			'default' => false,
			'description' => 'Devel_mode this will print real PHP memory usage into logs/console and do not compress JS libraries',
			'validation' => ''
		],
		'ROUNDCUBE_PER_USER_LOGGING' => [
			'default' => false,
			'description' => 'Activate this option if logs should be written to per-user directories. Data will only be logged if a directry cache/logs/<username>/ exists and is writable.',
			'validation' => '\App\Validator::bool'
		],
		'ROUNDCUBE_SMTP_LOG' => [
			'default' => false,
			'description' => 'Log sent messages to cache/logs/sendmail or to syslog',
			'validation' => '\App\Validator::bool'
		],
		'ROUNDCUBE_LOG_LOGINS' => [
			'default' => false,
			'description' => 'Log successful/failed logins to cache/logs/userlogins or to syslog',
			'validation' => '\App\Validator::bool'
		],
		'ROUNDCUBE_LOG_SESSION' => [
			'default' => false,
			'description' => 'Log session authentication errors to cache/logs/session or to syslog',
			'validation' => '\App\Validator::bool'
		],
		'ROUNDCUBE_SQL_DEBUG' => [
			'default' => false,
			'description' => 'Log SQL queries to cache/logs/sql or to syslog',
			'validation' => '\App\Validator::bool'
		],
		'ROUNDCUBE_IMAP_DEBUG' => [
			'default' => false,
			'description' => 'Log IMAP conversation to cache/logs/imap or to syslog',
			'validation' => '\App\Validator::bool'
		],
		'ROUNDCUBE_LDAP_DEBUG' => [
			'default' => false,
			'description' => 'Log LDAP conversation to cache/logs/ldap or to syslog',
			'validation' => '\App\Validator::bool'
		],
		'ROUNDCUBE_SMTP_DEBUG' => [
			'default' => false,
			'description' => 'Log SMTP conversation to cache/logs/smtp or to syslog',
			'validation' => '\App\Validator::bool'
		],
	],
	'developer' => [
		'CHANGE_GENERATEDTYPE' => [
			'default' => false,
			'description' => 'Turn the possibility to change generatedtype',
			'validation' => '\App\Validator::bool'
		],
		'MINIMIZE_JS' => [
			'default' => true,
			'description' => 'Enable minimize JS files',
			'validation' => '\App\Validator::bool'
		],
		'MINIMIZE_CSS' => [
			'default' => true,
			'description' => ' Enable minimize CSS files',
			'validation' => '\App\Validator::bool'
		],
		'CHANGE_VISIBILITY' => [
			'default' => false,
			'description' => 'Change of fields visibility',
			'validation' => '\App\Validator::bool'
		],
		'CHANGE_RELATIONS' => [
			'default' => false,
			'description' => 'Adding/Deleting relations between modules.',
			'validation' => '\App\Validator::bool'
		],
		'MISSING_LIBRARY_DEV_MODE' => [
			'default' => false,
			'description' => 'Developer libraries update mode',
			'validation' => '\App\Validator::bool'
		],
	],
	'mainetypes' => [
		'txt' => [
			'default' => 'text/plain',
			'description' => '',
			'validation' => ''
		],
		'html' => [
			'default' => 'text/html',
			'description' => '',
			'validation' => ''
		],
		'php' => [
			'default' => 'text/php',
			'description' => '',
			'validation' => ''
		],
		'css' => [
			'default' => 'text/css',
			'description' => '',
			'validation' => ''
		],
		'js' => [
			'default' => 'application/javascript',
			'description' => '',
			'validation' => ''
		],
		'json' => [
			'default' => 'application/json',
			'description' => '',
			'validation' => ''
		],
		'xml' => [
			'default' => 'application/xml',
			'description' => '',
			'validation' => ''
		],
		'swf' => [
			'default' => 'application/x-shockwave-flash',
			'description' => '',
			'validation' => ''
		],
		'flv' => [
			'default' => 'video/x-flv',
			'description' => '',
			'validation' => ''
		],
		'png' => [
			'default' => 'image/png',
			'description' => '',
			'validation' => ''
		],
		'jpg' => [
			'default' => 'image/jpeg',
			'description' => '',
			'validation' => ''
		],
		'jpe' => [
			'default' => 'image/jpeg',
			'description' => '',
			'validation' => ''
		],
		'jpeg' => [
			'default' => 'image/jpeg',
			'description' => '',
			'validation' => ''
		],
		'gif' => [
			'default' => 'image/gif',
			'description' => '',
			'validation' => ''
		],
		'bmp' => [
			'default' => 'image/bmp',
			'description' => '',
			'validation' => ''
		],
		'ico' => [
			'default' => 'image/vnd.microsoft.icon',
			'description' => '',
			'validation' => ''
		],
		'tiff' => [
			'default' => 'image/tiff',
			'description' => '',
			'validation' => ''
		],
		'tif' => [
			'default' => 'image/tiff',
			'description' => '',
			'validation' => ''
		],
		'svg' => [
			'default' => 'image/svg+xml',
			'description' => '',
			'validation' => ''
		],
		'svgz' => [
			'default' => 'image/svg+xml',
			'description' => '',
			'validation' => ''
		],
		'zip' => [
			'default' => 'application/zip',
			'description' => '',
			'validation' => ''
		],
		'rar' => [
			'default' => 'application/x-rar-compressed',
			'description' => '',
			'validation' => ''
		],
		'exe' => [
			'default' => 'application/x-msdownload',
			'description' => '',
			'validation' => ''
		],
		'msi' => [
			'default' => 'application/x-msdownload',
			'description' => '',
			'validation' => ''
		],
		'cab' => [
			'default' => 'application/vnd.ms-cab-compressed',
			'description' => '',
			'validation' => ''
		],
		'mov' => [
			'default' => 'video/quicktime',
			'description' => '',
			'validation' => ''
		],
		'qt' => [
			'default' => 'video/quicktime',
			'description' => '',
			'validation' => ''
		],
		'pdf' => [
			'default' => 'application/pdf',
			'description' => '',
			'validation' => ''
		],
		'psd' => [
			'default' => 'image/vnd.adobe.photoshop',
			'description' => '',
			'validation' => ''
		],
		'ai' => [
			'default' => 'application/postscript',
			'description' => '',
			'validation' => ''
		],
		'eps' => [
			'default' => 'application/postscript',
			'description' => '',
			'validation' => ''
		],
		'ps' => [
			'default' => 'application/postscript',
			'description' => '',
			'validation' => ''
		],
		'doc' => [
			'default' => 'application/msword',
			'description' => '',
			'validation' => ''
		],
		'rtf' => [
			'default' => 'application/rtf',
			'description' => '',
			'validation' => ''
		],
		'xls' => [
			'default' => 'application/vnd.ms-excel',
			'description' => '',
			'validation' => ''
		],
		'ppt' => [
			'default' => 'application/vnd.ms-powerpoint',
			'description' => '',
			'validation' => ''
		],
		'odt' => [
			'default' => 'application/vnd.oasis.opendocument.text',
			'description' => '',
			'validation' => ''
		],
		'ods' => [
			'default' => 'application/vnd.oasis.opendocument.spreadsheet',
			'description' => '',
			'validation' => ''
		],
		'xls' => [
			'default' => 'application/vnd.ms-excel',
			'description' => '',
			'validation' => ''
		],
		'xlm' => [
			'default' => 'application/vnd.ms-excel',
			'description' => '',
			'validation' => ''
		],
		'xla' => [
			'default' => 'application/vnd.ms-excel',
			'description' => '',
			'validation' => ''
		],
		'xlc' => [
			'default' => 'application/vnd.ms-excel',
			'description' => '',
			'validation' => ''
		],
		'xlt' => [
			'default' => 'application/vnd.ms-excel',
			'description' => '',
			'validation' => ''
		],
		'xlw' => [
			'default' => 'application/vnd.ms-excel',
			'description' => '',
			'validation' => ''
		],
		'ppt' => [
			'default' => 'application/vnd.ms-powerpoint',
			'description' => '',
			'validation' => ''
		],
		'pps' => [
			'default' => 'application/vnd.ms-powerpoint',
			'description' => '',
			'validation' => ''
		],
		'pot' => [
			'default' => 'application/vnd.ms-powerpoint',
			'description' => '',
			'validation' => ''
		],
		'dot' => [
			'default' => 'application/msword',
			'description' => '',
			'validation' => ''
		],
		'odc' => [
			'default' => 'application/vnd.oasis.opendocument.chart',
			'description' => '',
			'validation' => ''
		],
		'otc' => [
			'default' => 'application/vnd.oasis.opendocument.chart-template',
			'description' => '',
			'validation' => ''
		],
		'odf' => [
			'default' => 'application/vnd.oasis.opendocument.formula',
			'description' => '',
			'validation' => ''
		],
		'otf' => [
			'default' => 'application/vnd.oasis.opendocument.formula-template',
			'description' => '',
			'validation' => ''
		],
		'odg' => [
			'default' => 'application/vnd.oasis.opendocument.graphics',
			'description' => '',
			'validation' => ''
		],
		'otg' => [
			'default' => 'application/vnd.oasis.opendocument.graphics-template',
			'description' => '',
			'validation' => ''
		],
		'odi' => [
			'default' => 'application/vnd.oasis.opendocument.image',
			'description' => '',
			'validation' => ''
		],
		'oti' => [
			'default' => 'application/vnd.oasis.opendocument.image-template',
			'description' => '',
			'validation' => ''
		],
		'odp' => [
			'default' => 'application/vnd.oasis.opendocument.presentation',
			'description' => '',
			'validation' => ''
		],
		'otp' => [
			'default' => 'application/vnd.oasis.opendocument.presentation-template',
			'description' => '',
			'validation' => ''
		],
		'ots' => [
			'default' => 'application/vnd.oasis.opendocument.spreadsheet-template',
			'description' => '',
			'validation' => ''
		],
		'otm' => [
			'default' => 'application/vnd.oasis.opendocument.text-master',
			'description' => '',
			'validation' => ''
		],
		'ott' => [
			'default' => 'application/vnd.oasis.opendocument.text-template',
			'description' => '',
			'validation' => ''
		],
		'oth' => [
			'default' => 'application/vnd.oasis.opendocument.text-web',
			'description' => '',
			'validation' => ''
		],
		'docm' => [
			'default' => 'application/vnd.ms-word.document.macroEnabled.12',
			'description' => '',
			'validation' => ''
		],
		'docx' => [
			'default' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'description' => '',
			'validation' => ''
		],
		'dotm' => [
			'default' => 'application/vnd.ms-word.template.macroEnabled.12',
			'description' => '',
			'validation' => ''
		],
		'dotx' => [
			'default' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
			'description' => '',
			'validation' => ''
		],
		'ppsm' => [
			'default' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
			'description' => '',
			'validation' => ''
		],
		'ppsx' => [
			'default' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'description' => '',
			'validation' => ''
		],
		'pptm' => [
			'default' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
			'description' => '',
			'validation' => ''
		],
		'pptx' => [
			'default' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'description' => '',
			'validation' => ''
		],
		'xlsb' => [
			'default' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
			'description' => '',
			'validation' => ''
		],
		'xlsm' => [
			'default' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
			'description' => '',
			'validation' => ''
		],
		'xlsx' => [
			'default' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'description' => '',
			'validation' => ''
		],
		'xps' => [
			'default' => 'application/vnd.ms-xpsdocument',
			'description' => '',
			'validation' => ''
		],
		'7z' => [
			'default' => 'application/x-7z-compressed',
			'description' => '',
			'validation' => ''
		],
		's7z' => [
			'default' => 'application/x-7z-compressed',
			'description' => '',
			'validation' => ''
		],
		'vcf' => [
			'default' => 'text/vcard',
			'description' => '',
			'validation' => ''
		],
		'ics' => [
			'default' => 'text/calendar',
			'description' => '',
			'validation' => ''
		],
		'dwg' => [
			'default' => 'application/acad',
			'description' => '',
			'validation' => ''
		],
	],
	'performance' => [
		'CACHING_DRIVER' => [
			'default' => 'Base',
			'description' => 'Data caching is about storing some PHP variables in cache and retrieving it later from cache. Drivers: Base, Apcu',
			'validation' => function () {
				$arg = func_get_arg(0);
				return $arg === 'Basic' || $arg === 'Apcu';
			}
		],
		'ENABLE_CACHING_USERS' => [
			'default' => false,
			'description' => 'Enable caching of user data',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'ENABLE_CACHING_DB_CONNECTION' => [
			'default' => false,
			'description' => ' Enable caching database instance, accelerate time database connection',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'SQL_LOG_INCLUDE_CALLER' => [
			'default' => false,
			'description' => 'Should the caller information be captured in SQL Logging? It adds little overhead for performance but will be useful to debug. All data can be found in the table "l_yf_sqltime"',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'DB_DEFAULT_CHARSET_UTF8' => [
			'default' => true,
			'description' => 'If database default charset is UTF-8, set this to true This avoids executing the SET NAMES SQL for each query!',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'LISTVIEW_COMPUTE_PAGE_COUNT' => [
			'default' => false,
			'description' => ' Compute list view record count while loading listview everytime. Recommended value false',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE' => [
			'default' => true,
			'description' => 'Enable automatic records list refreshing while changing the value of the selection list',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'SEARCH_SHOW_OWNER_ONLY_IN_LIST' => [
			'default' => false,
			'description' => 'Show in search engine/filters only users and groups available in records list. It might result in a longer search time.',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'INTERVAL_FOR_NOTIFICATION_NUMBER_CHECK' => [
			'default' => 100,
			'description' => 'Time to update number of notifications in seconds',
			'validation' => '\App\Validator::naturalNumber'
		],
		'SEARCH_OWNERS_BY_AJAX' => [
			'default' => false,
			'description' => 'Search owners by AJAX. We recommend selecting the "true" value if there are numerous users in the system.',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'SEARCH_ROLES_BY_AJAX' => [
			'default' => false,
			'description' => 'Search roles by AJAX',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'SEARCH_REFERENCE_BY_AJAX' => [
			'default' => false,
			'description' => 'Search reference by AJAX. We recommend selecting the "true" value if there are numerous users in the system.',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'MAX_NUMBER_EXPORT_RECORDS' => [
			'default' => 500,
			'description' => 'Max number of exported records',
			'validation' => '\App\Validator::naturalNumber'
		],
		'OWNER_MINIMUM_INPUT_LENGTH' => [
			'default' => 2,
			'description' => 'Minimum number of characters to search for record owner',
			'validation' => '\App\Validator::naturalNumber'
		],
		'ROLE_MINIMUM_INPUT_LENGTH' => [
			'default' => 2,
			'description' => 'Minimum number of characters to search for role',
			'validation' => '\App\Validator::naturalNumber'
		],
		'NUMBERS_EMAILS_DOWNLOADED_DURING_ONE_SCANNING' => [
			'default' => 100,
			'description' => 'The numbers of emails downloaded during one scanning',
			'validation' => '\App\Validator::naturalNumber'
		],
		'CRON_MAX_NUMBERS_RECORD_PRIVILEGES_UPDATER' => [
			'default' => 1000000,
			'description' => 'In how many records should the global search permissions be updated in cron',
			'validation' => '\App\Validator::naturalNumber'
		],
		'CRON_MAX_NUMBERS_RECORD_ADDRESS_BOOK_UPDATER' => [
			'default' => 10000,
			'description' => 'In how many records should the address book be updated in cron',
			'validation' => '\App\Validator::naturalNumber'
		],
		'CRON_MAX_NUMBERS_RECORD_LABELS_UPDATER' => [
			'default' => 1000,
			'description' => 'In how many records should the label be updated in cron',
			'validation' => '\App\Validator::naturalNumber'
		],
		'CRON_MAX_NUMBERS_SENDING_MAILS' => [
			'default' => 1000,
			'description' => 'In how many mails should the send in cron (Mailer).',
			'validation' => '\App\Validator::naturalNumber'
		],
		'CRON_MAX_NUMBERS_SENDING_SMS' => [
			'default' => 10,
			'description' => 'In how many sms should the send in cron.',
			'validation' => '\App\Validator::naturalNumber'
		],
		'CRON_MAX_ATACHMENTS_DELETE' => [
			'default' => 1000,
			'description' => 'In how many atachments should the delete in cron.',
			'validation' => '\App\Validator::naturalNumber'
		],
		'CRON_BATCH_METHODS_LIMIT' => [
			'default' => 15,
			'description' => 'Time to execute batch methods [min].',
			'validation' => '\App\Validator::naturalNumber'
		],
		'LOAD_CUSTOM_FILES' => [
			'default' => false,
			'description' => ' Parameter that allows to disable file overwriting. After enabling it the system will additionally check whether the file exists in the custom directory. Ex. custom/modules/Assets/Assets.php',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'SHOW_ADMIN_PANEL' => [
			'default' => false,
			'description' => 'Parameter that determines whether admin panel should be available to admin by default',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'SHOW_ADMINISTRATORS_IN_USERS_LIST' => [
			'default' => true,
			'description' => 'Display administrators in the list of users (Assigned To)',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'GLOBAL_SEARCH' => [
			'default' => true,
			'description' => 'Global search: true/false',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'MAX_MULTIIMAGE_VIEW' => [
			'default' => 5,
			'description' => 'Maximum MultiImage icon view in lists',
			'validation' => '\App\Validator::naturalNumber'
		],
		'BROWSING_HISTORY_WORKING' => [
			'default' => true,
			'description' => 'Browsing history working if true',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'BROWSING_HISTORY_VIEW_LIMIT' => [
			'default' => 20,
			'description' => 'Number of browsing history steps',
			'validation' => '\App\Validator::naturalNumber'
		],
		'BROWSING_HISTORY_DELETE_AFTER' => [
			'default' => 7,
			'description' => 'Days after browsing history has deleted',
			'validation' => '\App\Validator::naturalNumber'
		],
		'SESSION_DRIVER' => [
			'default' => 'File',
			'description' => 'Session handler name, handler dir: app/Session/',
			'validation' => '\App\Validator::naturalNumber'
		],
		'CHART_MULTI_FILTER_LIMIT' => [
			'default' => 5,
			'description' => 'Charts multi filter limit',
			'validation' => '\App\Validator::naturalNumber'
		],
		'CHART_MULTI_FILTER_STR_LEN' => [
			'default' => 50,
			'description' => 'Charts multi filter maximum db value length',
			'validation' => '\App\Validator::naturalNumber'
		],
		'CHART_ADDITIONAL_FILTERS_LIMIT' => [
			'default' => 6,
			'description' => "Additional filters limit for ChartFilter's",
			'validation' => '\App\Validator::naturalNumber'
		],
		'MAX_MERGE_RECORDS' => [
			'default' => 4,
			'description' => 'Maximum number of merged records',
			'validation' => '\App\Validator::naturalNumber'
		],
		'ACCESS_TO_INTERNET' => [
			'default' => true,
			'description' => 'Can CRM have access to the Internet?',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'CHANGE_LOCALE' => [
			'default' => true,
			'description' => 'Change the locale for sort the data',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'INVENTORY_EDIT_VIEW_LAYOUT' => [
			'default' => true,
			'description' => 'Is divided layout style on edit view in modules with products',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'LIMITED_INFO_IN_FOOTER' => [
			'default' => false,
			'description' => "Any modifications of this parameter require the vendor's consent.  Any unauthorised modification breaches the terms and conditions of YetiForce Public License.",
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'RECORD_POPOVER_DELAY' => [
			'default' => 500,
			'description' => "Popover record's trigger delay in ms",
			'validation' => '\App\Validator::naturalNumber'
		],
		'PICKLIST_DEPEDENCY_DEFAULT_EMPTY' => [
			'default' => true,
			'description' => 'Empty value when is not selected item in picklist depedency',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
	],
	'relation' => [
		'COMMENT_MAX_LENGTH' => [
			'default' => 20,
			'description' => 'Maximum length of a comment visible in the related module',
			'validation' => '\App\Validator::naturalNumber',
		],
		'SELECTABLE_CATEGORY' => [
			'default' => true,
			'description' => 'Enabling this option makes it possible to select a folder/category in the Tree Category Modal window, together with the category tree and records; for example: Product and Services in Account.',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'SHOW_RELATED_MODULE_NAME' => [
			'default' => true,
			'description' => 'Show names related modules',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'SHOW_RELATED_ICON' => [
			'default' => true,
			'description' => 'Show icon related modules',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'SHOW_RECORDS_COUNT' => [
			'default' => false,
			'description' => 'Show record count in tabs related modules',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
	]
];
