<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
include ROOT_DIRECTORY.DIRECTORY_SEPARATOR.'config/config.inc.php';
$config['db_dsnw'] = 'mysql://' . $dbconfig['db_username'] . ':' . $dbconfig['db_password'] . '@' . $dbconfig['db_server'] . ':' . $dbconfig['db_port'] . '/' . $dbconfig['db_name'];
$config['db_prefix'] = 'roundcube_';
$config['default_host'] = ['192.168.3.2' => '192.168.3.2',];
$config['validate_cert'] = false;
$config['default_port'] = 143;
$config['smtp_server'] = '172.17.10.82';
$config['smtp_port'] = 25;
$config['smtp_user'] = '';
$config['smtp_pass'] = '';
$config['support_url'] = 'http://yetiforce.com';
$config['des_key'] = 'rGOQ26hR%gxlZk=QA!$HMOvb';
$config['username_domain'] = 'pro-crm.local';
$config['product_name'] = 'YetiForce';
$config['plugins'] = array('identity_smtp', 'ical_attachments', 'yetiforce', 'thunderbird_labels', 'zipdownload', 'archive', 'authres_status');
$config['language'] = 'de_DE';
$config['mime_param_folding'] = 0;
$config['skin_logo'] = array('*' => '/images/null.png');
$config['ip_check'] = false;
$config['enable_spellcheck'] = true;
$config['identities_level'] = '0';
$config['auto_create_user'] = true;
$config['mail_pagesize'] = 30;
$config['addressbook_pagesize'] = 50;
$config['prefer_html'] = true;
$config['preview_pane'] = false;
$config['htmleditor'] = '1';
$config['draft_autosave'] = 300;
$config['mdn_requests'] = '0';
$config['session_lifetime'] = 100000;
$config['sendmail_delay'] = 0;
$config['date_long'] = 'Y-m-d H:i';
$config['date_format'] = 'Y-m-d';
$config['time_format'] = 'H:i';
$config['show_images'] = '0';
$config['imap_cache'] = 'db';
$config['messages_cache'] = 'db';
$config['reply_mode'] = 1;
// Debug
$config['debug_level'] = AppConfig::debug('ROUNDCUBE_DEBUG_LEVEL');
$config['per_user_logging'] = AppConfig::debug('ROUNDCUBE_PER_USER_LOGGING');
$config['smtp_log'] = AppConfig::debug('ROUNDCUBE_SMTP_LOG');
$config['log_logins'] = AppConfig::debug('ROUNDCUBE_LOG_LOGINS');
$config['log_session'] = AppConfig::debug('ROUNDCUBE_LOG_SESSION');
$config['sql_debug'] = AppConfig::debug('ROUNDCUBE_SQL_DEBUG');
$config['imap_debug'] = AppConfig::debug('ROUNDCUBE_IMAP_DEBUG');
$config['ldap_debug'] = AppConfig::debug('ROUNDCUBE_LDAP_DEBUG');
$config['smtp_debug'] = AppConfig::debug('ROUNDCUBE_SMTP_DEBUG');
$config['devel_mode'] = AppConfig::debug('ROUNDCUBE_DEVEL_MODE');
$config['log_dir'] = ROOT_DIRECTORY.DIRECTORY_SEPARATOR.'cache/logs/';
$config['temp_dir'] = ROOT_DIRECTORY.DIRECTORY_SEPARATOR.'cache/mail/';
//Socket context options
$config['imap_conn_options'] = [
	'ssl' => [
		'verify_peer' => false,
		'verfify_peer_name' => false,
	],
];
$config['imap_auth_type'] = 'PLAIN';
$config['smtp_timeout'] = 5;
$config['smtp_conn_options'] = [
	'ssl' => [
		'verify_peer' => false,
		'verfify_peer_name' => false,
	],
];
$config['smtp_timeout'] = 5;
$config['smtp_helo_host'] = 'YetiForceCRM';
$config['root_directory'] = ROOT_DIRECTORY . DIRECTORY_SEPARATOR;
$config['site_URL'] = $site_URL;
$config['imap_open_add_connection_type'] = true;
$config['enable_variables_in_signature'] = false;
$config['skin'] = 'yetiforce';
$config['list_cols'] = array('flag', 'status', 'subject', 'fromto', 'date', 'size', 'attachment', 'authres_status', 'threads');
// plugin authres_status
$config['enable_authres_status_column'] = true;
$config['show_statuses'] = 127;
//CRM Additional configuration parameters
$config['root_directory'] = ROOT_DIRECTORY . DIRECTORY_SEPARATOR;
$config['site_URL'] = $site_URL;
$config['imap_open_add_connection_type'] = true;
$config['enable_variables_in_signature'] = false;

// migoi Exchange fix
$config['imap_fix_msexchange_kerberos'] = true;