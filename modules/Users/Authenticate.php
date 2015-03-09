<?php
/*********************************************************************************
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
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Users/Authenticate.php,v 1.10 2005/02/28 05:25:22 jack Exp $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('modules/Users/Users.php');
require_once('modules/Users/CreateUserPrivilegeFile.php');
require_once('include/logging.php');
require_once('user_privileges/audit_trail.php');

global $mod_strings, $default_charset;

$focus = new Users();

// Add in defensive code here.
$focus->column_fields["user_name"] = to_html($_REQUEST['user_name']);
$user_password = $_REQUEST['user_password'];

$focus->load_user($user_password);

$successURL = 'index.php';

if($focus->is_authenticated()) {
	session_regenerate_id();
	//Inserting entries for audit trail during login
	$audit_trail = 'false';
	if($audit_trail == 'true') {
		if($record == '')
			$auditrecord = '';
		else
			$auditrecord = $record;

		$date_var = $adb->formatDate(date('Y-m-d H:i:s'), true);
		$query = "insert into vtiger_audit_trial values(?,?,?,?,?,?)";
		$params = array($adb->getUniqueID('vtiger_audit_trial'), $focus->id, 'Users','Authenticate','',$date_var);
		$adb->pquery($query, $params);
	}

	require_once('modules/Users/LoginHistory.php');
	// Recording the login info
	$usip=$_SERVER['REMOTE_ADDR'];
	$intime=date("Y/m/d H:i:s");
	$loghistory=new LoginHistory();
	$Signin = $loghistory->user_login($focus->column_fields["user_name"],$usip,$intime);

	//Security related entries start
	require_once('include/utils/UserInfoUtil.php');

	createUserPrivilegesfile($focus->id);

	if ( version_compare(PHP_VERSION, '5.4.0', '<') ) {
		//Security related entries end
		session_unregister('login_password');
		session_unregister('login_error');
		session_unregister('login_user_name');
	}

	$_SESSION['authenticated_user_id'] = $focus->id;
	$_SESSION['AUTHUSERID'] = $focus->id;
	$_SESSION['app_unique_key'] = $application_unique_key;

	global $upload_badext;
	//Enabled session variable for KCFINDER
	$_SESSION['KCFINDER'] = array();
	$_SESSION['KCFINDER']['disabled'] = false;
	$_SESSION['KCFINDER']['uploadURL'] = "cache/upload";
	$_SESSION['KCFINDER']['uploadDir'] = "/cache/upload";
	$deniedExts = implode(" ", $upload_badext);
	$_SESSION['KCFINDER']['deniedExts'] = $deniedExts;

	// store the user's theme in the session
	if(!empty($focus->column_fields["theme"])) {
		$authenticated_user_theme = $focus->column_fields["theme"];
	} else {
		$authenticated_user_theme = $default_theme;
	}

	// store the user's language in the session
	if(!empty($focus->column_fields["language"])) {
		$authenticated_user_language = $focus->column_fields["language"];
	} else {
		$authenticated_user_language = $default_language;
	}

	// If this is the default user and the default user theme is set to reset, reset it to the default theme value on each login
	if($reset_theme_on_default_user && $focus->user_name == $default_user_name) {
		$authenticated_user_theme = $default_theme;
	}
	if(isset($reset_language_on_default_user) && $reset_language_on_default_user && $focus->user_name == $default_user_name) {
		$authenticated_user_language = $default_language;
	}

	$_SESSION['vtiger_authenticated_user_theme'] = $authenticated_user_theme;
	$_SESSION['authenticated_user_language'] = $authenticated_user_language;

	$log->debug("authenticated_user_theme is $authenticated_user_theme");
	$log->debug("authenticated_user_language is $authenticated_user_language");
	$log->debug("authenticated_user_id is ". $focus->id);
	$log->debug("app_unique_key is $application_unique_key");

	// Clear all uploaded import files for this user if it exists
	global $import_dir;

	$tmp_file_name = $import_dir. "IMPORT_".$focus->id;

	if (file_exists($tmp_file_name)) {
		unlink($tmp_file_name);
	}

	$arr = $_SESSION['lastpage'];
	if(isset($_SESSION['lastpage'])) {
		header("Location: $successURL".$arr);
	} else {
		header("Location: $successURL");
	}
} else {
	$sql = 'select user_name, id, crypt_type from vtiger_users where user_name=?';
	$result = $adb->pquery($sql, array($focus->column_fields["user_name"]));
	$rowList = $result->GetRows();
	foreach ($rowList as $row) {
		$cryptType = $row['crypt_type'];
		/* PHP 5.3 WIN implementation of crypt API not compatible with earlier version */
		if(strtolower($cryptType) == 'md5' && version_compare(PHP_VERSION, '5.3.0') >= 0 && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ) {
			header("Location: modules/Migration/PHP5.3_PasswordHelp.php");
			die;
		}
	}
	$_SESSION['login_user_name'] = $focus->column_fields["user_name"];
	$_SESSION['login_password'] = $user_password;
	$_SESSION['login_error'] = $mod_strings['ERR_INVALID_PASSWORD'];

	// go back to the login screen.
	// create an error message for the user.
	header("Location: index.php");
}

?>