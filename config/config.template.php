<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the 
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */

// more than 8MB memory needed for graphics
// memory limit default value = 64M
AppConfig::iniSet('memory_limit', '512M');

// lifetime of session
AppConfig::iniSet('session.gc_maxlifetime', '21600');

// show or hide calendar, world clock, calculator, chat and CKEditor 
// Do NOT remove the quotes if you set these to false! 
$CALENDAR_DISPLAY = 'true';
$WORLD_CLOCK_DISPLAY = 'true';
$CALCULATOR_DISPLAY = 'true';
$CHAT_DISPLAY = 'true';
$USE_RTE = 'true';

// url for customer portal (Example: https://portal.yetiforce.com/)
$PORTAL_URL = 'https://portal.yetiforce.com';

// helpdesk support email id and support name (Example: 'support@yetiforce.com' and 'yetiforce support')
$HELPDESK_SUPPORT_NAME = 'your-support name';
$HELPDESK_SUPPORT_EMAIL_REPLY = '';

/* database configuration
  db_server
  db_port
  db_hostname
  db_username
  db_password
  db_name
 */

$dbconfig['db_server'] = '_DBC_SERVER_';
$dbconfig['db_port'] = '_DBC_PORT_';
$dbconfig['db_username'] = '_DBC_USER_';
$dbconfig['db_password'] = '_DBC_PASS_';
$dbconfig['db_name'] = '_DBC_NAME_';
$dbconfig['db_type'] = '_DBC_TYPE_';
$dbconfig['db_status'] = '_DB_STAT_';

$dbconfig['db_hostname'] = $dbconfig['db_server'] . ':' . $dbconfig['db_port'];

$host_name = $dbconfig['db_hostname'];

// backslash is required at the end of URL
$site_URL = '_SITE_URL_';

// cache direcory path
$cache_dir = '_VT_CACHEDIR_';

// tmp_dir default value prepended by cache_dir = images/
$tmp_dir = '_VT_TMPDIR_';

// import_dir default value prepended by cache_dir = import/
$import_dir = 'cache/import/';

// upload_dir default 
$upload_dir = 'cache/upload/';

// maximum file size for uploaded files in bytes also used when uploading import files
// upload_maxsize default value = 3000000
$upload_maxsize = 52428800;  // 50MB
// flag to allow export functionality
// 'all' to allow anyone to use exports 
// 'admin' to only allow admins to export 
// 'none' to block exports completely 
// allow_exports default value = all
$allow_exports = 'all';

// files with one of these extensions will have '.txt' appended to their filename on upload
// upload_badext default value = php, php3, php4, php5, pl, cgi, py, asp, cfm, js, vbs, html, htm
$upload_badext = array('php', 'php3', 'php4', 'php5', 'pl', 'cgi', 'py', 'asp', 'cfm', 'js', 'vbs', 'html', 'htm', 'exe', 'bin', 'bat', 'sh', 'dll', 'phps', 'phtml', 'xhtml', 'rb', 'msi', 'jsp', 'shtml', 'sth', 'shtm');

// list_max_entries_per_page default value = 20
$list_max_entries_per_page = '20';

// limitpage_navigation default value = 5
$limitpage_navigation = '5';

// history_max_viewed default value = 5
$history_max_viewed = '5';

// default_module default value = Home
$default_module = 'Home';

// default_action default value = index
$default_action = 'index';

// set default theme
// default_theme default value = blue
$default_theme = 'softed';

// default text that is placed initially in the login form for user name
// no default_user_name default value
$default_user_name = '';

//Master currency name
$currency_name = '_MASTER_CURRENCY_';

// default charset
// default charset default value = 'UTF-8' or 'ISO-8859-1'
$default_charset = '_VT_CHARSET_';

// default language
// default_language default value = en_us
$default_language = '_LANG_';

// add the language pack name to every translation string in the display.
// translation_string_prefix default value = false
$translation_string_prefix = false;

//Option to cache tabs permissions for speed.
$cache_tab_perms = true;

//Option to hide empty home blocks if no entries.
$display_empty_home_blocks = false;

//Disable Stat Tracking of vtiger CRM instance
$disable_stats_tracking = false;

// Generating Unique Application Key
$application_unique_key = '_VT_APP_UNIQKEY_';

// trim descriptions, titles in listviews to this value
$listview_max_textlength = 40;

// Maximum time limit for PHP script execution (in seconds)
$php_max_execution_time = 0;

// Set the default timezone as per your preference
$default_timezone = '_TIMEZONE_';

/** If timezone is configured, try to set it */
if (isset($default_timezone) && function_exists('date_default_timezone_set')) {
	@date_default_timezone_set($default_timezone);
}

// Maximum length of characters for title
$title_max_length = 60;

// Maximum length for href tag
$href_max_length = 35;

//Should menu breadcrumbs be visible? true = show, false = hide
$breadcrumbs = true;

//Separator for menu breadcrumbs default value = '>'
$breadcrumbs_separator = '>';

//Minimum cron frequency [min]
$MINIMUM_CRON_FREQUENCY = 1;

//Update the current session id with a newly generated one after login
$session_regenerate_id = false;

$davStorageDir = 'storage/Files';
$davHistoryDir = 'storage/FilesHistory';

// prod, demo, test
$systemMode = 'prod';

// Force site access to always occur under SSL (https) for selected areas. You will not be able to access selected areas under non-ssl. Note, you must have SSL enabled on your server to utilise this option.
$forceSSL = false;

// Maximum number of records in a mass edition
$listMaxEntriesMassEdit = 500;

// Enable closing of mondal window by clicking on the background
$backgroundClosingModal = true;

// Enable CSRF-protection
$csrfProtection = true;

// Is sending emails active. 
$isActiveSendingMails = true;

// Should the task in cron be unblocked if the script execution time was exceeded
$unblockedTimeoutCronTasks = true;

// The maximum time of executing a cron. Recommended same as the max_exacution_time parameter value.
$maxExecutionCronTime = 3600;

// System's language selection in the login window (true/false).
$langInLoginView = false;

// System's lyout selection in the login window (true/false).
$layoutInLoginView = false;

// Set the default layout 
$defaultLayout = 'basic';

$forceRedirect = true;
