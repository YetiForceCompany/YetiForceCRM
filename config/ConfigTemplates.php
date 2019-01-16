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
			'description' => 'Webservice config.',
			'validation' => function () {
				return true;
			}
		],
		'PUBLIC_KEY' => [
			'default' => 'config/public.key',
			'description' => 'Webservice config.',
			'validation' => function () {
				return true;
			}
		]
	],
	'main' => [
		'CALENDAR_DISPLAY' => [
			'default' => 'true',
			'description' => 'Show or hide calendar, world clock, calculator, chat and CKEditor
 Do NOT remove the quotes if you set these to false!',
			'validation' => ''
		],
		'WORLD_CLOCK_DISPLAY' => [
			'default' => 'true',
			'description' => '',
			'validation' => ''
		],
		'CALCULATOR_DISPLAY' => [
			'default' => 'true',
			'description' => '',
			'validation' => ''
		],
		'CHAT_DISPLAY' => [
			'default' => 'true',
			'description' => '',
			'validation' => ''
		],
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
			'default' => 'db_server' . ':' . 'db_port',
			'description' => '',
			'validation' => ''
		],
		'host_name' => [
			'default' => 'db_hostname',
			'description' => '',
			'validation' => ''
		],
		'site_URL' => [
			'default' => '_SITE_URL_',
			'description' => 'Backslash is required at the end of URL',
			'validation' => ''
		],
		'cache_dir' => [
			'default' => '_VT_CACHEDIR_',
			'description' => 'Cache direcory path',
			'validation' => ''
		],
		'tmp_dir' => [
			'default' => '_VT_TMPDIR_',
			'description' => 'Default value prepended by cache_dir = images/',
			'validation' => ''
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
		'$upload_maxsize' => [
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
			'default' => '20',
			'description' => 'List max entries per page: default value = 20',
			'validation' => ''
		],
		'limitpage_navigation' => [
			'default' => '5',
			'description' => 'Limit page navigation: default value = 5',
			'validation' => ''
		],
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
			'description' => '',
			'validation' => ''
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
	]
];
