<?php
/*
Disable SSL for IMAP/SMTP
If your IMAP/SMTP servers are on the same host or are connected via a secure network, not using SSL connections improves performance. So don't use "ssl://" or "tls://" urls for 'default_host' and 'smtp_server' config options.


Debug:
$config['debug_level'] = 1;
$config['imap_debug'] = true;
$config['smtp_debug'] = true;
$config['log_logins'] = true;

*/
if(!$no_include_config){
	$include_path = ini_get('include_path');
	$currentPath = getcwd();
	$crmPath =  $currentPath . '/../../../';
	chdir ($crmPath);
	ini_set('include_path',$crmPath);

	include_once('config/config.inc.php');
	if (file_exists('config/config_override.php')) {
		include_once 'config/config_override.php';
	}
	chdir ($currentPath);
	ini_set('include_path',$include_path);
}
$config['db_dsnw'] = 'mysql://'.$dbconfig['db_username'].':'.$dbconfig['db_password'].'@'.$dbconfig['db_server'].$dbconfig['db_port'].'/'.$dbconfig['db_name'];
$config['db_prefix'] = 'roundcube_';
$config['debug_level'] = 1;
$config['default_host'] = 'ssl://imap.gmail.com';
$config['validate_cert'] = false;
$config['default_port'] = 993;
$config['smtp_server'] = 'ssl://smtp.gmail.com';
$config['smtp_port'] = 465;
$config['smtp_user'] = '%u';
$config['smtp_pass'] = '%p';
$config['support_url'] = 'http://yetiforce.com';
$config['des_key'] = 'rGOQ26hR%gxlZk=QA!$HMOvb';
$config['username_domain'] = 'gmail.com';
$config['product_name'] = 'YetiForce';
$config['plugins'] = array('autologon','identity_smtp','ical_attachments');
$config['language'] = 'en_US';
$config['mime_param_folding'] = 0;
$config['skin_logo'] = array("*" => "/images/null.png");
$config['ip_check'] = false;///
$config['enable_spellcheck'] = true;//
$config['identities_level'] = '0';
$config['auto_create_user'] = true;
$config['mail_pagesize'] = 25;
$config['addressbook_pagesize'] = 50;
$config['prefer_html'] = true;
$config['preview_pane'] = false;
$config['htmleditor'] = '1';
$config['draft_autosave'] = 300;
$config['mdn_requests'] = '0';
$config['session_lifetime'] = 10;
$config['sendmail_delay'] = 0;
$config['date_long'] = 'Y-m-d H:i';
$config['date_format'] = 'Y-m-d';
$config['time_format'] = 'H:i';
$config['show_images'] = '0';
$config['imap_cache'] = 'db';
$config['messages_cache'] = 'db';