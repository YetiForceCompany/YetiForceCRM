<?php
if(!$no_include_config){
	include_once '../../../config.php';
}

$config['db_dsnw'] = 'mysql://'.$dbconfig['db_username'].':'.$dbconfig['db_password'].'@'.$dbconfig['db_server'].'/'.$dbconfig['db_name'];
$config['db_prefix'] = 'roundcube_';
$config['debug_level'] = 13;
$config['default_host'] = 'ssl://imap.gmail.com';
$config['default_port'] = 993;
$config['smtp_server'] = 'ssl://smtp.gmail.com';
$config['smtp_port'] = 465;
$config['smtp_user'] = '%u';
$config['smtp_pass'] = '%p';
$config['support_url'] = 'http://localhost/test/roundcubemail-1.0-beta/';
$config['des_key'] = 'rGOQ26hR%gxlZk=QA!$HMOvb';
$config['username_domain'] = 'gmail.com';
$config['product_name'] = 'Roundcube Webmail OpenSaaS Sp. z o.o.';
$config['plugins'] = array();
$config['language'] = 'pl_PL';
$config['mime_param_folding'] = 0;
$config['skin_logo'] = array("*" => "/images/null.png");
$config['ip_check'] = false;///
$config['enable_spellcheck'] = true;//
$config['identities_level'] = 0;
$config['auto_create_user'] = true;
$config['smtp_log'] = true;
$config['mail_pagesize'] = 50;
$config['addressbook_pagesize'] = 50;
$config['prefer_html'] = true;
$config['preview_pane'] = false;
$config['htmleditor'] = 0;
$config['draft_autosave'] = 300;
$config['mdn_requests'] = 0;
$config['session_lifetime'] = 10;
$config['sendmail_delay'] = 0;
$config['date_long'] = 'Y-m-d H:i';
$config['date_format'] = 'Y-m-d';
$config['time_format'] = 'H:i';
$config['show_images'] = 0;