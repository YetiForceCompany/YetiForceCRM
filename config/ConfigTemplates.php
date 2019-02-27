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
		'USE_RTE' => [
			'default' => true,
			'description' => 'Use rte',
		],
		'PORTAL_URL' => [
			'default' => '',
			'description' => 'Url for customer portal (Example: https://portal.yetiforce.com/)',
		],
//		'HELPDESK_SUPPORT_NAME' => [
//			'default' => 'your-support name',
//			'description' => 'Helpdesk support email id and support name (Example: "support@yetiforce.com" and "yetiforce support")',
//			'validation' => ''
//		],
		'HELPDESK_SUPPORT_EMAIL_REPLY' => [
			'default' => '',
			'description' => 'Help desk support email reply',
		],
		'site_URL' => [
			'default' => '',
			'description' => 'Backslash is required at the end of URL',
			'validation' => '\App\Validator::url'
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
		'upload_maxsize' => [
			'default' => 52428800,
			'description' => 'Maximum file size for uploaded files in bytes also used when uploading import files: upload_maxsize default value = 52428800 (50MB)',
			'validation' => function () {
				$arg = func_get_arg(0);
				return $arg && \App\Validator::naturalNumber($arg) && ($arg * 1048576) <= \vtlib\Functions::getMaxUploadSize();
			},
			'sanitization' => function () {
				return (int) func_get_arg(0) * 1048576;
			}
		],
		'allow_exports' => [
			'default' => 'all',
			'description' => 'Flag to allow export functionality: "all" - to allow anyone to use exports, "admin" - to only allow admins to export, "none" -  to block exports completely',
			'validation' => function () {
				$arg = func_get_arg(0);
				return in_array($arg, ['all', 'admin', 'none']);
			}
		],
		'upload_badext' => [
			'default' => ['php', 'php3', 'php4', 'php5', 'pl', 'cgi', 'py', 'asp', 'cfm', 'js', 'vbs', 'html', 'htm', 'exe', 'bin', 'bat', 'sh', 'dll', 'phps', 'phtml', 'xhtml', 'rb', 'msi', 'jsp', 'shtml', 'sth', 'shtm'],
			'description' => 'Files with one of these extensions will have ".txt" appended to their filename on upload.'
		],
		'list_max_entries_per_page' => [
			'default' => 20,
			'description' => 'List max entries per page: default value = 20',
			'validation' => function () {
				$arg = func_get_arg(0);
				return $arg && \App\Validator::naturalNumber($arg) && (100 >= $arg) && (0 < $arg);
			},
			'sanitization' => function () {
				return (int) func_get_arg(0);
			}
		],
		'default_module' => [
			'default' => 'Home',
			'description' => 'Default module: default value = Home',
			'validation' => function () {
				$arg = func_get_arg(0);
				return \App\Module::isModuleActive($arg) === true;
			}
		],
		'default_charset' => [
			'default' => 'UTF-8',
			'description' => 'Default charset:  default value = "UTF-8"',
			'validation' => function () {
				return func_get_arg(0) === 'UTF-8';
			}
		],
		'default_language' => [
			'default' => 'en-US',
			'description' => 'Default language: default value = en-US',
			'validation' => '\App\Validator::languageTag',
		],
		'application_unique_key' => [
			'default' => '',
			'description' => 'Unique Application Key',
			'validation' => function () {
				return !class_exists("\Config\Main");
			},
			'sanitization' => function () {
				return sha1(time() + random_int(1, 9999999));
			}
		],
		'listview_max_textlength' => [
			'default' => 40,
			'description' => 'Trim descriptions, titles in listviews to this value',
			'validation' => function () {
				$arg = func_get_arg(0);
				return $arg && \App\Validator::naturalNumber($arg) && (100 >= $arg) && (0 < $arg);
			},
			'sanitization' => function () {
				return (int) func_get_arg(0);
			}
		],
		'php_max_execution_time' => [
			'default' => 0,
			'description' => 'Maximum time limit for PHP script execution (in seconds)'
		],
		'default_timezone' => [
			'default' => '_TIMEZONE_',
			'description' => 'Set the default timezone as per your preference',
			'validation' => function () {
				if (!class_exists('UserTimeZones')) {
					Vtiger_Loader::includeOnce('~modules/Users/UserTimeZonesArray.php');
				}
				$arg = func_get_arg(0);
				return in_array($arg, UserTimeZones::getTimeZones());
			}
		],
		'title_max_length' => [
			'default' => 60,
			'description' => 'Maximum length of characters for title',
			'validation' => function () {
				$arg = func_get_arg(0);
				return $arg && \App\Validator::naturalNumber($arg) && (100 >= $arg) && (0 < $arg);
			},
			'sanitization' => function () {
				return (int) func_get_arg(0);
			}
		],
		'href_max_length' => [
			'default' => 35,
			'description' => 'Maximum length for href tag',
			'validation' => function () {
				$arg = func_get_arg(0);
				return $arg && \App\Validator::naturalNumber($arg) && (100 >= $arg) && (0 < $arg);
			},
			'sanitization' => function () {
				return (int) func_get_arg(0);
			}
		],
		'breadcrumbs' => [
			'default' => true,
			'description' => 'Should menu breadcrumbs be visible? true = show, false = hide',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'MINIMUM_CRON_FREQUENCY' => [
			'default' => 1,
			'description' => 'Minimum cron frequency [min]',
			'validation' => function () {
				$arg = func_get_arg(0);
				return $arg && \App\Validator::naturalNumber($arg) && (100 >= $arg) && (0 < $arg);
			},
			'sanitization' => function () {
				return (int) func_get_arg(0);
			}
		],
		'session_regenerate_id' => [
			'default' => true,
			'description' => 'Update the current session id with a newly generated one after login'
		],
		'davStorageDir' => [
			'default' => 'storage/Files',
			'description' => 'Update the current session id with a newly generated one after login',
		],
		'systemMode' => [
			'default' => 'prod',
			'description' => 'System mode. Available: prod, demo, test'
		],
		'forceSSL' => [
			'default' => false,
			'description' => 'Force site access to always occur under SSL (https) for selected areas. You will not be able to access selected areas under non-ssl. Note, you must have SSL enabled on your server to utilise this option.'
		],
		'listMaxEntriesMassEdit' => [
			'default' => 500,
			'description' => 'Maximum number of records in a mass edition',
			'validation' => function () {
				$arg = func_get_arg(0);
				return $arg && \App\Validator::naturalNumber($arg) && (5000 >= $arg);
			},
			'sanitization' => function () {
				return (int) func_get_arg(0);
			}
		],
		'backgroundClosingModal' => [
			'default' => true,
			'description' => 'Enable closing of mondal window by clicking on the background',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'csrfProtection' => [
			'default' => true,
			'description' => 'Enable CSRF protection'
		],
		'isActiveSendingMails' => [
			'default' => true,
			'description' => 'Is sending emails active.'
		],
		'unblockedTimeoutCronTasks' => [
			'default' => true,
			'description' => 'Should the task in cron be unblocked if the script execution time was exceeded'
		],
		'maxExecutionCronTime' => [
			'default' => 3600,
			'description' => 'The maximum time of executing a cron. Recommended same as the max_exacution_time parameter value.'
		],
		'langInLoginView' => [
			'default' => false,
			'description' => "System's language selection in the login window (true/false).",
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'layoutInLoginView' => [
			'default' => false,
			'description' => "System's lyout selection in the login window (true/false)",
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'defaultLayout' => [
			'default' => 'basic',
			'description' => 'Set the default layout',
			'validation' => function () {
				return isset(\App\Layout::getAllLayouts()[func_get_arg(0)]);
			}
		],
		'forceRedirect' => [
			'default' => true,
			'description' => 'Redirect to proper url when wrong url is entered.'
		],
		'phoneFieldAdvancedVerification' => [
			'default' => true,
			'description' => 'Enable advanced phone number validation. Enabling  it will block saving invalid phone number.'
		],
	],
	'debug' => [
		'LOG_TO_FILE' => [
			'default' => false,
			'description' => 'Enable saving logs to file. Values: false/true'
		],
		'LOG_TO_CONSOLE' => [
			'default' => false,
			'description' => 'Enable displaying logs in debug console. Values: false/true'
		],
		'LOG_TO_PROFILE' => [
			'default' => false,
			'description' => 'Enable saving logs profiling.  Values: false/true'
		],
		'LOG_LEVELS' => [
			'default' => false,
			'description' => 'Level of saved/displayed logs. Values: false = All / 3 = error and warning / ["error", "warning", "info", "trace", "profile"]',
		],
		'LOG_TRACE_LEVEL' => [
			'default' => 0,
			'description' => 'Level of saved/displayed tracerts. // Values: int'
		],
		'DISPLAY_DEBUG_CONSOLE' => [
			'default' => false,
			'description' => 'Display main debug console'
		],
		'DEBUG_CONSOLE_ALLOWED_IPS' => [
			'default' => false,
			'description' => 'List of IP addresses allowed to display debug console. Values: false = All IPS / "192.168.1.10" / ["192.168.1.10","192.168.1.11"]',
		],
		'SQL_DIE_ON_ERROR' => [
			'default' => false,
			'description' => 'Stop the running process of the system if there is and error in sql query'
		],
		'DEBUG_CRON' => [
			'default' => false,
			'description' => 'Debug cron => cache/logs/cron/'
		],
		'DEBUG_VIEWER' => [
			'default' => false,
			'description' => 'Debug Viewer => cache/logs/viewer-debug.log'
		],
		'DISPLAY_DEBUG_VIEWER' => [
			'default' => false,
			'description' => 'Display Smarty Debug Console'
		],
		'SMARTY_ERROR_REPORTING' => [
			'default' => new \Nette\PhpGenerator\PhpLiteral('E_ALL & ~E_NOTICE'),
			'description' => 'Do not show Smarty Notice in phpError.log',
		],
		'JS_DEBUG' => [
			'default' => true,
			'description' => 'Turn on/off debug errors javascript'
		],
		'DISPLAY_EXCEPTION_BACKTRACE' => [
			'default' => false,
			'description' => 'Displays information about the tracking code when an error occurs. Available only with the active SQL_DIE_ON_ERROR = true'
		],
		'DISPLAY_EXCEPTION_LOGS' => [
			'default' => false,
			'description' => 'Display logs when error exception occurs'
		],
		'EXCEPTION_ERROR_HANDLER' => [
			'default' => false,
			'description' => 'Turn on/off the error handler'
		],
		'EXCEPTION_ERROR_TO_FILE' => [
			'default' => false,
			'description' => 'Save logs to file (cache/logs/errors.log)'
		],
		'EXCEPTION_ERROR_TO_SHOW' => [
			'default' => false,
			'description' => 'Display errors'
		],
		'EXCEPTION_ERROR_LEVEL' => [
			'default' => new \Nette\PhpGenerator\PhpLiteral('E_ALL & ~E_NOTICE'),
			'description' => "Set the error reporting level. The parameter is either an integer representing a bit field, or named constants.\nhttps://secure.php.net/manual/en/errorfunc.configuration.php#ini.error-reporting\nAll errors - E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED / Critical errors - E_ERROR | E_WARNING | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR",
		],
		'DAV_DEBUG_EXCEPTIONS' => [
			'default' => false,
			'description' => 'API - Sabre dav - This is a flag that allow or not showing file, line and code of the exception in the returned XML'
		],
		'DAV_DEBUG_PLUGIN' => [
			'default' => false,
			'description' => 'Activate the plugin recording log in DAV'
		],
		'WEBSERVICE_SHOW_ERROR' => [
			'default' => false,
			'description' => 'Show errors messages in web service'
		],
		'WEBSERVICE_DEBUG' => [
			'default' => false,
			'description' => 'Web service logs'
		],
		'MAILER_DEBUG' => [
			'default' => false,
			'description' => 'Mailer debug'
		],
		'ROUNDCUBE_DEBUG_LEVEL' => [
			'default' => 1,
			'description' => 'System error reporting, sum of: 1 = log; 4 = show, 8 = trace'
		],

		'ROUNDCUBE_DEVEL_MODE' => [
			'default' => false,
			'description' => 'Devel_mode this will print real PHP memory usage into logs/console and do not compress JS libraries'
		],
		'ROUNDCUBE_PER_USER_LOGGING' => [
			'default' => false,
			'description' => "Activate this option if logs should be written to per-user directories.\nData will only be logged if a directry cache/logs/<username>/ exists and is writable."
		],
		'ROUNDCUBE_SMTP_LOG' => [
			'default' => false,
			'description' => 'Log sent messages to cache/logs/sendmail or to syslog'
		],
		'ROUNDCUBE_LOG_LOGINS' => [
			'default' => false,
			'description' => 'Log successful/failed logins to cache/logs/userlogins or to syslog'
		],
		'ROUNDCUBE_LOG_SESSION' => [
			'default' => false,
			'description' => 'Log session authentication errors to cache/logs/session or to syslog'
		],
		'ROUNDCUBE_SQL_DEBUG' => [
			'default' => false,
			'description' => 'Log SQL queries to cache/logs/sql or to syslog'
		],
		'ROUNDCUBE_IMAP_DEBUG' => [
			'default' => false,
			'description' => 'Log IMAP conversation to cache/logs/imap or to syslog'
		],
		'ROUNDCUBE_LDAP_DEBUG' => [
			'default' => false,
			'description' => 'Log LDAP conversation to cache/logs/ldap or to syslog'
		],
		'ROUNDCUBE_SMTP_DEBUG' => [
			'default' => false,
			'description' => 'Log SMTP conversation to cache/logs/smtp or to syslog'
		],
	],
	'developer' => [
		'CHANGE_GENERATEDTYPE' => [
			'default' => false,
			'description' => 'Turn the possibility to change generatedtype'
		],
		'MINIMIZE_JS' => [
			'default' => true,
			'description' => 'Enable minimize JS files'
		],
		'MINIMIZE_CSS' => [
			'default' => true,
			'description' => ' Enable minimize CSS files'
		],
		'CHANGE_VISIBILITY' => [
			'default' => false,
			'description' => 'Change of fields visibility'
		],
		'CHANGE_RELATIONS' => [
			'default' => false,
			'description' => 'Adding/Deleting relations between modules.'
		],
		'MISSING_LIBRARY_DEV_MODE' => [
			'default' => false,
			'description' => 'Developer libraries update mode'
		],
		'LANGUAGES_UPDATE_DEV_MODE' => [
			'default' => false,
			'description' => 'Developer libraries update mode'
		]
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
			'description' => "Should the caller information be captured in SQL Logging?\nIt adds little overhead for performance but will be useful to debug.\nAll data can be found in the table 'l_yf_sqltime'",
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
		'maxMassDeleteRecords' => [
			'default' => 1000,
			'description' => 'Max number of mass deleted records',
		],
		'maxMassTransferOwnershipRecords' => [
			'default' => 1000,
			'description' => 'Max number of transfer ownership records',
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
			'description' => "Parameter that allows to disable file overwriting.\nAfter enabling it the system will additionally check whether the file exists in the custom directory. Ex. custom/modules/Assets/Assets.php",
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
			'description' => "Any modifications of this parameter require the vendor's consent.\nAny unauthorised modification breaches the terms and conditions of YetiForce Public License.",
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'LIMITED_INFO_SUPPORT' => [
			'default' => false,
			'description' => "Any modifications of this parameter require the vendor's consent.\nAny unauthorised modification breaches the terms and conditions of YetiForce Public License.",
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'RECORD_POPOVER_DELAY' => [
			'default' => 500,
			'description' => "Popover record's trigger delay in ms",
			'validation' => '\App\Validator::naturalNumber'
		],
		'PICKLIST_DEPENDENCY_DEFAULT_EMPTY' => [
			'default' => true,
			'description' => 'Empty value when is not selected item in picklist dependency',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'recursiveTranslate' => [
			'default' => false,
			'description' => 'If there is no translation in the chosen language, then get from the default language.'
		]
	],
	'relation' => [
		'COMMENT_MAX_LENGTH' => [
			'default' => 20,
			'description' => 'Maximum length of a comment visible in the related module'
		],
		'SHOW_RELATED_MODULE_NAME' => [
			'default' => true,
			'description' => 'Show names related modules'
		],
		'SHOW_RELATED_ICON' => [
			'default' => true,
			'description' => 'Show icon related modules'
		],
		'SHOW_RECORDS_COUNT' => [
			'default' => false,
			'description' => 'Show record count in tabs related modules'
		]
	],
	'search' => [
		'GLOBAL_SEARCH_SELECT_MODULE' => [
			'default' => true,
			'description' => 'Auto select current module in global search (true/false)',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'GLOBAL_SEARCH_MODAL_MAX_NUMBER_RESULT' => [
			'default' => 100,
			'description' => 'Auto select current module in global search (int)',
			'validation' => '\App\Validator::naturalNumber',
		],
		'GLOBAL_SEARCH_SORTING_RESULTS' => [
			'default' => 0,
			'description' => 'Global search - Should the results be sorted in MySQL or PHP while displaying (None = 0, PHP = 1, Mysql = 2). The parameter impacts system efficiency.',
			'validation' => function () {
				$arg = func_get_arg(0);
				return is_int($arg) && in_array($arg, [0, 1, 2]);
			}
		],
		'GLOBAL_SEARCH_CURRENT_MODULE_TO_TOP' => [
			'default' => true,
			'description' => 'Global search - Show current module as first in search results (true/false).',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'GLOBAL_SEARCH_AUTOCOMPLETE' => [
			'default' => 1,
			'description' => 'Global search - Search for records while entering text  (1/0).',
			'validation' => function () {
				$arg = func_get_arg(0);
				return is_int($arg) && in_array($arg, [0, 1]);
			}
		],
		'GLOBAL_SEARCH_AUTOCOMPLETE_LIMIT' => [
			'default' => 15,
			'description' => 'Global search - Max number of displayed results. The parameter impacts system efficiency.',
			'validation' => '\App\Validator::naturalNumber'
		],
		'GLOBAL_SEARCH_AUTOCOMPLETE_MIN_LENGTH' => [
			'default' => 3,
			'description' => 'Global search - The minimum number of characters a user must type before a search is performed. The parameter impacts system efficiency',
			'validation' => '\App\Validator::naturalNumber'
		],
		'GLOBAL_SEARCH_OPERATOR_SELECT' => [
			'default' => true,
			'description' => 'Global search - Show operator list.',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'GLOBAL_SEARCH_DEFAULT_OPERATOR' => [
			'default' => 'FulltextBegin',
			'description' => 'Global search - Default search operator. (FulltextBegin,FulltextWord,Contain,Begin,End)',
			'validation' => function () {
				$arg = func_get_arg(0);
				return in_array($arg, ['FulltextBegin', 'FulltextWord', 'Contain', 'Begin', 'End']);
			}
		],
		'LIST_ENTITY_STATE_COLOR' => [
			'default' => [
				'Archived' => '#0032a2',
				'Trash' => '#ab0505',
				'Active' => '#009405',
			],
			'description' => 'Colors for record state will be displayed in list view, history, and preview.',
		],
	],
	'securityKeys' => [
		'encryptionPass' => [
			'default' => 'yeti',
			'description' => 'Key to encrypt passwords, changing the key results in the loss of all encrypted data.',
			'validation' => function () {
				$arg = func_get_arg(0);
				return is_array($arg) && !empty($arg['pass']) && !empty($arg['method']) &&
					in_array($arg['method'], \App\Encryption::getMethods()) && strlen($arg['pass']) === App\Encryption::getLengthVector($arg['method']);
			}
		],
		'encryptionMethod' => [
			'default' => 'AES-256-CBC',
			'description' => 'Encryption method.',
			'validation' => function () {
				$arg = func_get_arg(0);
				return empty($arg) || ($arg && in_array($arg, \App\Encryption::getMethods()));
			}
		],
	],
	'security' => [
		'USER_ENCRYPT_PASSWORD_COST' => [
			'default' => 10,
			'description' => "Password encrypt algorithmic cost. Numeric values - we recommend values greater than 10.\nThe greater the value, the longer it takes to encrypt the password."
		],
		'RESET_LOGIN_PASSWORD' => [
			'default' => false,
			'description' => 'Possible to reset the password while logging in (true/false)',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'SHOW_MY_PREFERENCES' => [
			'default' => true,
			'description' => 'Show my preferences',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'CHANGE_LOGIN_PASSWORD' => [
			'default' => true,
			'description' => 'Changing the settings by the user is possible true/false',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'PERMITTED_BY_ROLES' => [
			'default' => true,
			'description' => 'Permitted by roles.',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'PERMITTED_BY_SHARING' => [
			'default' => true,
			'description' => 'Permitted by sharing.',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'PERMITTED_BY_SHARED_OWNERS' => [
			'default' => true,
			'description' => 'Permitted by shared owners.',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'PERMITTED_BY_RECORD_HIERARCHY' => [
			'default' => true,
			'description' => 'Permitted by record hierarchy.',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'PERMITTED_BY_ADVANCED_PERMISSION' => [
			'default' => true,
			'description' => 'Permitted by advanced permission.',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'PERMITTED_BY_PRIVATE_FIELD' => [
			'default' => false,
			'description' => 'Permitted by private field.',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'CACHING_PERMISSION_TO_RECORD' => [
			'default' => false,
			'description' => "Configuration of the permission mechanism on records list.\ntrue - Permissions based on the users column in vtiger_crmentity.\n		Permissions are not verified in real time. They are updated via cron.\n		We do not recommend using this option in production environments.\nfalse - Permissions based on adding tables with permissions to query (old mechanism).",
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'EMAIL_FIELD_RESTRICTED_DOMAINS_ACTIVE' => [
			'default' => false,
			'description' => "Restricted domains allow you to block saving an email address from a given domain in the system.\nRestricted domains work only for email address type fields.",
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'EMAIL_FIELD_RESTRICTED_DOMAINS_VALUES' => [
			'default' => [],
			'description' => 'Restricted domains',
		],
		'EMAIL_FIELD_RESTRICTED_DOMAINS_ALLOWED' => [
			'default' => [],
			'description' => 'List of modules where restricted domains are enabled, if empty it will be enabled everywhere.',
		],
		'EMAIL_FIELD_RESTRICTED_DOMAINS_EXCLUDED' => [
			'default' => ['OSSEmployees', 'Users'],
			'description' => 'List of modules excluded from restricted domains validation.',
		],
		'LOGIN_PAGE_REMEMBER_CREDENTIALS' => [
			'default' => false,
			'description' => 'Remember user credentials',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'FIELDS_REFERENCES_DEPENDENT' => [
			'default' => false,
			'description' => 'Interdependent reference fields',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'HPKP_KEYS' => [
			'default' => [],
			'description' => "HTTP Public-Key-Pins (HPKP) pin-sha256 For HPKP to work properly at least 2 keys are needed.\nhttps://scotthelme.co.uk/hpkp-http-public-key-pinning/, https://sekurak.pl/mechanizm-http-public-key-pinning/.",
		],
		'CSP_ACTIVE' => [
			'default' => true,
			'description' => 'Content Security Policy',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'PURIFIER_ALLOWED_DOMAINS' => [
			'default' => [],
			'description' => 'List of allowed domains for fields with HTML support',
		],
		'MAX_LIFETIME_SESSION' => [
			'default' => 21600,
			'description' => 'Lifetime session (in seconds)',
		],
		'USER_AUTHY_MODE' => [
			'default' => 'TOTP_OPTIONAL',
			'description' => "User authentication mode.\n@see \Users_Totp_Authmethod::ALLOWED_USER_AUTHY_MODE Available values.",
			'validation' => function () {
				$arg = func_get_arg(0);
				return in_array($arg, \Users_Totp_Authmethod::ALLOWED_USER_AUTHY_MODE);
			}
		],
		'CACHE_LIFETIME_SENSIOLABS_SECURITY_CHECKER' => [
			'default' => 3600,
			'description' => 'Cache lifetime for SensioLabs security checker.',
			'validation' => '\App\Validator::naturalNumber',
		],
	],
	'sounds' => [
		'IS_ENABLED' => [
			'default' => true,
			'description' => 'Enable system sounds',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'REMINDERS' => [
			'default' => 'sound_1.mp3',
			'description' => 'Sets the type of sound of reminders',
		],
		'CHAT' => [
			'default' => 'sound_1.mp3',
			'description' => 'Sets the type of sound of chat',
		],
		'MAILS' => [
			'default' => 'sound_1.mp3',
			'description' => 'Sets the type of sound of mails',
		],
	],
	'db' => [
		'db_server' => [
			'default' => '>URL<',
			'description' => 'Gets the database server',
			'validation' => '\App\Validator::domain',
			'sanitization' => '\App\Purifier::purify'
		],
		'db_port' => [
			'default' => '',
			'description' => 'Gets the database port',
			'validation' => '\App\Validator::port'
		],
		'db_username' => [
			'default' => '_DBC_USER_',
			'description' => 'Gets the database user name',
			'validation' => '\App\Validator::sql',
			'sanitization' => '\App\Purifier::purify'
		],
		'db_password' => [
			'default' => '_DBC_PASS_',
			'description' => 'Gets the database password',
			'validation' => function () {
				return true;
			}
		],
		'db_name' => [
			'default' => '_DBC_NAME_',
			'description' => 'Gets the database name',
			'validation' => '\App\Validator::dbName',
			'sanitization' => '\App\Purifier::purify'
		],
		'db_type' => [
			'default' => 'mysql',
			'description' => 'Gets the database type',
			'validation' => '\App\Validator::dbType'
		],
		'db_hostname' => [
			'type' => 'function',
			'default' => 'return self::$db_server . ":" . self::$db_port;',
			'description' => 'Gets host name'
		],
		'base' => [
			'type' => 'function',
			'default' => "return [
	'dsn' => self::\$db_type . ':host=' . self::\$db_server . ';dbname=' . self::\$db_name . ';port=' . self::\$db_port,
	'host' => self::\$db_server,
	'port' => self::\$db_port,
	'username' => self::\$db_username,
	'password' => self::\$db_password,
	'dbName' => self::\$db_name,
	'tablePrefix' => 'yf_',
	'charset' => 'utf8',
];",
			'description' => 'Basic database configuration'
		],
	]
];
