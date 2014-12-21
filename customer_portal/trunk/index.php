<?php
/* * *******************************************************************************
 * The content of this file is subject to the MYC Vtiger Customer Portal license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is Proseguo s.l. - MakeYourCloud
 * Portions created by Proseguo s.l. - MakeYourCloud are Copyright(C) Proseguo s.l. - MakeYourCloud
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */

//Require all files necessary for the application to start, including user settings, soap library for enstablish the connection, and the portal classes
require_once("config/config.php");
require_once("config/version.php");
require_once('lib/nusoap/lib/nusoap.php');
require_once("portal.php");

// Set session path
ini_set('session.save_path','tmp');

//Establish the connection with the crm and store it in a global variable
global $sclient;
$sclient = new soapclient2($vtiger_path."/api.php?service=yetiportal");
$sclient->soap_defencoding = $default_charset;

session_start();

//Check if there are stored variables then if the user is previously logged or if has sent some login/forgot request, else provide him a logging screen
User::check_login();

//If the login is passed analyze the REQUEST and call the requested action.
Router::start();