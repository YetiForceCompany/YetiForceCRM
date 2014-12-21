<?php
/* * *******************************************************************************
 * The content of this file is subject to the MYC Vtiger Customer Portal license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is caboose s.l. - MakeYourCloud
 * Portions created by caboose s.l. - MakeYourCloud are Copyright(C) caboose s.l. - MakeYourCloud
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */

global $vtiger_path;
global $portal_theme;
global $upload_dir;
global $languages;
global $hiddenmodules;
global $show_summary_tab;
global $summary_widgets;

//This is the vtiger server path ie., the url to access the vtiger server in browser
//Ex. i access my vtiger as http://yourdomain.com:90/yourcrm/index.php so i will give as http://yourdomain.com:90/yourcrm
$vtiger_path = "http://yeti";

//Give a temporary directory path which is used when we upload attachment
$upload_dir = 'tmp';

//This is the default charset that will be used to encode data exchanged with vTiger;
$default_charset = 'UTF-8';

$default_language = 'en_us';

//This is an array of available languages declared as follow: "filename"=>"Language Label", 
//the language file should be created in the language folder with the format: namespecified.lang.php 
//Ex.: "en_us" will correspond to a file named "en_us.lang.php"
$languages = Array(
	'en_us'=>'English',
	'pl_pl'=>'Polski',
	'de_de'=>'Deutsch',
	'pt_br'=>'Brazilian Portuguese'
);

//Define the default theme to apply to the portal, the theme folder should be present in the themes directory with the same name specified here
$portal_theme = 'default';

//Define wich module you want to force to be hidden
$hiddenmodules = array("ProjectTask","ProjectMilestone");

//Default timezone settings for the server, uncomment this if you have a bad configured server that shows you warning of if you want to use a different timezone than the server.
date_default_timezone_set('Europe/Sarajevo');

//Summary configuration 
$show_summary_tab = true; //Show summary tab
$summary_widgets = array('all'); //all,OpenTickets,TicketsByStatus