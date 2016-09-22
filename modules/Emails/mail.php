<?php
/* * *******************************************************************************
 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */


require_once("modules/Emails/class.phpmailer.php");
require_once 'include/utils/CommonUtils.php';
require_once 'include/utils/VTCacheUtils.php';

/**   Function used to send email
 *   $module 		-- current module
 *   $to_email 	-- to email address
 *   $fromName	-- currently loggedin user name
 *   $fromEmail	-- currently loggedin vtiger_users's email id. you can give as '' if you are not in HelpDesk module
 *   $subject		-- subject of the email you want to send
 *   $contents		-- body of the email you want to send
 *   $cc		-- add email ids with comma seperated. - optional
 *   $bcc		-- add email ids with comma seperated. - optional.
 *   $attachment	-- whether we want to attach the currently selected file or all vtiger_files.[values = current,all] - optional
 *   $emailid		-- id of the email object which will be used to get the vtiger_attachments
 */
function send_mail($module, $to_email, $fromName, $fromEmail, $subject, $contents, $cc = '', $bcc = '', $attachment = '', $emailid = '', $logo = '', $useGivenFromEmailAddress = false, $attachmentSrc = [])
{

	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();

	$log->debug('To id => ' . $to_email . ' Subject => ' . $subject . 'Contents => ' . $contents);

	//Get the email id of assigned_to user -- pass the value and name, name must be "user_name" or "id"(field names of vtiger_users vtiger_table)
	//if module is HelpDesk then from_email will come based on support email id
	if (empty($fromEmail)) {
		//if from email is not defined, then use the useremailid as the from address
		$fromEmail = getUserEmailId('user_name', $fromName);
	}

	//if the newly defined from email field is set, then use this email address as the from address
	//and use the username as the reply-to address
	$systems = Vtiger_Cache::get('SYSTEMS', 'email');
	if (!$cachedFromEmail) {
		$query = 'select from_email_field from vtiger_systems where server_type=?';
		$result = $adb->pquery($query, ['email']);
		$systems = $adb->getRow($result);
		Vtiger_Cache::set('SYSTEMS', 'email', $systems);
	}
	$fromEmailField = $systems['from_email_field'];

	if ((!empty($fromEmailField) && !$useGivenFromEmailAddress) || empty($fromEmail)) {
		//setting from _email to the defined email address in the outgoing server configuration
		$fromEmail = $fromEmailField;
	}
	$supportName = AppConfig::main('HELPDESK_SUPPORT_NAME');
	if (!empty($supportName)) {
		$fromName = $supportName;
	}

	$mail = new PHPMailer();

	setMailerProperties($mail, $subject, $contents, $fromEmail, $fromName, trim($to_email, ','), $attachment, $emailid, $module, $logo);
	setCCAddress($mail, 'cc', $cc);
	setCCAddress($mail, 'bcc', $bcc);

	$emailReply = AppConfig::main('HELPDESK_SUPPORT_EMAIL_REPLY');
	if (!empty($emailReply) && $emailReply != $fromEmail) {
		$mail->AddReplyTo($emailReply);
	}

	// Fix: Return immediately if Outgoing server not configured
	if (empty($mail->Host)) {
		return 0;
	}
	// END

	if (count($attachmentSrc)) {
		foreach ($attachmentSrc as $name => $src) {
			if (is_array($src)) {
				$mail->AddStringAttachment(
					$src['string'], $src['filename'], $src['encoding'], $src['type']
				);
			} else {
				$mail->AddAttachment($src, $name);
			}
		}
	}

	$sendStatus = MailSend($mail);
	if ($sendStatus != 1) {
		$mailError = getMailError($mail, $sendStatus, $mailto);
	} else {
		$mailError = $sendStatus;
	}

	return $mailError;
}

/** 	Function to get the user Email id based on column name and column value
 * 	$name -- column name of the vtiger_users vtiger_table
 * 	$val  -- column value
 */
function getUserEmailId($name, $val)
{
	$log = LoggerManager::getInstance();
	$log->debug('Inside the function getUserEmailId. --- ' . $name . ' = ' . $val);
	if ($val != '') {
		$adb = PearDatabase::getInstance();
		//done to resolve the PHP5 specific behaviour
		$sql = sprintf("SELECT email1 from vtiger_users WHERE status='Active' && %s = ?", $adb->sql_escape_string($name));
		$res = $adb->pquery($sql, array($val));
		$email = $adb->query_result($res, 0, 'email1');
		$log->debug('Email id is selected  => ' . $email);
		return $email;
	} else {
		$log->debug('User id is empty. so return value is ""');
		return '';
	}
}

/** 	Funtion to add the user's signature with the content passed
 * 	$contents -- where we want to add the signature
 * 	$fromname -- which user's signature will be added to the contents
 */
function addSignature($contents, $fromname)
{
	$log = LoggerManager::getInstance();
	$log->debug('Inside the function addSignature');

	$sign = VTCacheUtils::getUserSignature($fromname);
	if ($sign === null) {
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery('select signature, first_name, last_name from vtiger_users where user_name=?', array($fromname));
		$sign = $adb->query_result($result, 0, 'signature');
		VTCacheUtils::setUserSignature($fromname, $sign);
		VTCacheUtils::setUserFullName($fromname, $adb->query_result($result, 0, 'first_name') . ' ' . $adb->query_result($result, 0, 'last_name'));
	}

	$sign = nl2br($sign);

	if ($sign != '') {
		$contents .= '<br><br>' . $sign;
		$log->debug('Signature is added with the body => ' . $sign);
	} else {
		$log->debug('Signature is empty for the user => ' . $fromname);
	}
	return $contents;
}

/** 	Function to set all the Mailer properties
 * 	$mail 		-- reference of the mail object
 * 	$subject	-- subject of the email you want to send
 * 	$contents	-- body of the email you want to send
 * 	$fromEmail	-- from email id which will be displayed in the mail
 * 	$fromName	-- from name which will be displayed in the mail
 * 	$to_email 	-- to email address  -- This can be an email in a single string, a comma separated
 * 			   list of emails or an array of email addresses
 * 	$attachment	-- whether we want to attach the currently selected file or all vtiger_files.
  [values = current,all] - optional
 * 	$emailid	-- id of the email object which will be used to get the vtiger_attachments - optional
 */
function setMailerProperties($mail, $subject, $contents, $fromEmail, $fromName, $to_email, $attachment = '', $emailid = '', $module = '', $logo = '')
{
	$log = LoggerManager::getInstance();
	$log->debug('Inside the function setMailerProperties');
	$companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
	$logourl = 'storage/Logo/' . $companyDetails->get('logoname');
	if ($logo == 1) {
		$image = getimagesize($logourl);
		$mail->AddEmbeddedImage($logourl, 'logo', $companyDetails->get('logoname'), 'base64', $image['mime']);
	}

	$mail->Subject = $subject;
	//Added back as we have changed php mailer library, older library was using html_entity_decode before sending mail
	$mail->Body = decode_html($contents);
	$mail->AltBody = strip_tags(preg_replace(array("/<p>/i", "/<br>/i", "/<br \/>/i"), array("\n", "\n", "\n"), $contents));

	$mail->IsSMTP();  //set mailer to use SMTP

	setMailServerProperties($mail);

	//Handle the from name and email for HelpDesk
	$mail->From = $fromEmail;
	$userFullName = trim(VTCacheUtils::getUserFullName($fromName));
	if (empty($userFullName)) {
		$adb = PearDatabase::getInstance();
		$rs = $adb->pquery("select first_name,last_name from vtiger_users where user_name=?", array($fromName));
		$num_rows = $adb->num_rows($rs);
		if ($num_rows > 0) {
			$fullName = \vtlib\Deprecated::getFullNameFromQResult($rs, 0, 'Users');
			VTCacheUtils::setUserFullName($fromName, $fullName);
		}
	} else {
		$fromName = $userFullName;
	}
	$mail->FromName = decode_html($fromName);

	if ($to_email != '') {
		if (is_array($to_email)) {
			for ($j = 0, $num = count($to_email); $j < $num; $j++) {
				$mail->addAddress($to_email[$j]);
			}
		} else {
			$_tmp = explode(',', $to_email);
			for ($j = 0, $num = count($_tmp); $j < $num; $j++) {
				$mail->addAddress($_tmp[$j]);
			}
		}
	}

	//commented so that it does not add from_email in reply to
	$mail->WordWrap = 50;

	//If we want to add the currently selected file only then we will use the following function
	if ($attachment == 'current' && $emailid != '') {
		if (AppRequest::has('filename_hidden')) {
			$file_name = AppRequest::get('filename_hidden');
		} else {
			$file_name = $_FILES['filename']['name'];
		}
		addAttachment($mail, $file_name, $emailid);
	}

	//This will add all the vtiger_files which are related to this record or email
	if ($attachment == 'all' && $emailid != '') {
		addAllAttachments($mail, $emailid);
	}

	$mail->IsHTML(true);  // set email format to HTML

	return;
}

/** 	Function to set the Mail Server Properties in the object passed
 * 	$mail -- reference of the mailobject
 */
function setMailServerProperties($mail)
{
	$adb = PearDatabase::getInstance();
	$log = LoggerManager::getInstance();
	$log->debug('Inside the function setMailServerProperties');

	$res = $adb->pquery('select * from vtiger_systems where server_type=?', array('email'));
	if (AppRequest::has('server'))
		$server = AppRequest::get('server');
	else
		$server = $adb->query_result_raw($res, 0, 'server');
	if (AppRequest::has('server_username'))
		$username = AppRequest::get('server_username');
	else
		$username = $adb->query_result_raw($res, 0, 'server_username');

	if (AppRequest::has('server_password'))
		$password = AppRequest::get('server_password');
	else
		$password = $adb->query_result_raw($res, 0, 'server_password');

	// Define default state
	$smtp_auth = false;

	// Prasad: First time read smtp_auth from the request
	if (AppRequest::get('smtp_auth') == 'on')
		$smtp_auth = true;
	else if (AppRequest::get('module') == 'Settings' && (!AppRequest::has('smtp_auth'))) {
		//added to avoid issue while editing the values in the outgoing mail server.
		$smtp_auth = false;
	} else {
		if ($adb->query_result_raw($res, 0, 'smtp_auth') == '1' || $adb->query_result_raw($res, 0, 'smtp_auth') == 'true') {
			$smtp_auth = true;
		}
	}

	$log->debug('Mail server name,username,password => ' . $server . ',' . $username . ',' . $password);
	if ($smtp_auth) {
		$mail->SMTPAuth = true; // turn on SMTP authentication
	}
	$mail->Host = $server;  // specify main and backup server
	$mail->Username = $username; // SMTP username
	$mail->Password = $password; // SMTP password
	// To Support TLS
	$serverinfo = explode("://", $server);
	$smtpsecure = $serverinfo[0];
	if ($smtpsecure == 'tls') {
		$mail->SMTPSecure = $smtpsecure;
		$mail->Host = $serverinfo[1];
	}
	// End

	return;
}

/** 	Function to add the file as attachment with the mail object
 * 	$mail -- reference of the mail object
 * 	$filename -- filename which is going to added with the mail
 * 	$record -- id of the record - optional
 */
function addAttachment($mail, $filename, $record)
{
	$log = LoggerManager::getInstance();
	$log->debug('Inside the function addAttachment');
	$log->debug('The file name is => ' . $filename);

	//This is the file which has been selected in Email EditView
	if (is_file($filename) && $filename != '') {
		$mail->AddAttachment(ROOT_DIRECTORY . '/cache/upload/' . $filename);
	}
}

/**     Function to add all the vtiger_files as attachment with the mail object
 *     $mail -- reference of the mail object
 *     $record -- email id ie., record id which is used to get the all vtiger_attachments from database
 */
function addAllAttachments($mail, $record)
{
	$log = LoggerManager::getInstance();
	$adb = PearDatabase::getInstance();
	$log->debug('Inside the function addAllAttachments');

	//Retrieve the vtiger_files from database where avoid the file which has been currently selected
	$sql = 'SELECT vtiger_attachments.* 
			FROM vtiger_attachments 
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
			INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid  = vtiger_attachments.attachmentsid
			INNER JOIN vtiger_senotesrel ON vtiger_senotesrel.notesid = vtiger_seattachmentsrel.crmid 
			WHERE vtiger_crmentity.deleted=0 && vtiger_senotesrel.crmid=?';
	$res = $adb->pquery($sql, array($record));

	while ($row = $db->getRow($result)) {
		$fileid = $row['attachmentsid'];
		$filename = decode_html($row['attachmentsid']);
		$filepath = $row['path'];
		$filewithpath = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $filepath . $fileid . '_' . $filename;

		//if the file is exist in cache/upload directory then we will add directly
		//else get the contents of the file and write it as a file and then attach (this will occur when we unlink the file)
		if (is_file($filewithpath)) {
			$mail->AddAttachment($filewithpath, $filename);
		}
	}
}

/** 	Function to set the CC or BCC addresses in the mail
 * 	$mail -- reference of the mail object
 * 	$cc_mod -- mode to set the address ie., cc or bcc
 * 	$cc_val -- addresss with comma seperated to set as CC or BCC in the mail
 */
function setCCAddress($mail, $cc_mod, $cc_val)
{
	$log = LoggerManager::getInstance();
	$log->debug('Inside the functin setCCAddress');

	if ($cc_mod == 'cc')
		$method = 'AddCC';
	if ($cc_mod == 'bcc')
		$method = 'AddBCC';
	if ($cc_val != '') {
		$ccmail = explode(",", trim($cc_val, ","));
		for ($i = 0; $i < count($ccmail); $i++) {
			$addr = $ccmail[$i];
			$cc_name = preg_replace('/([^@]+)@(.*)/', '$1', $addr); // First Part Of Email
			if (stripos($addr, '<')) {
				$name_addr_pair = explode("<", $ccmail[$i]);
				$cc_name = $name_addr_pair[0];
				$addr = trim($name_addr_pair[1], ">");
			}
			if ($ccmail[$i] != '')
				$mail->$method($addr, $cc_name);
		}
	}
}

/** 	Function to send the mail which will be called after set all the mail object values
 * 	$mail -- reference of the mail object
 */
function MailSend($mail)
{
	$log = LoggerManager::getInstance();
	$log->info('Inside of Send Mail function.');
	if (!$mail->Send()) {
		$log->error('Error in Mail Sending: ' . $mail->ErrorInfo);
		return $mail->ErrorInfo;
	} else {
		$log->info('Mail has been sent from the YetiForce system: ' . $mail->ErrorInfo);
		return 1;
	}
}

/** 	Function to get the Parent email id from HelpDesk to send the details about the ticket via email
 * 	$returnmodule -- Parent module value. Contact or Account for send email about the ticket details
 * 	$parentid -- id of the parent ie., contact or vtiger_account
 */
function getParentMailId($parentmodule, $parentid)
{
	$log = LoggerManager::getInstance();
	$log->debug('Inside the function getParentMailId. \n parent module and id => ' . $parentmodule . '&' . $parentid);

	if ($parentmodule == 'Contacts') {
		$tablename = 'vtiger_contactdetails';
		$idname = 'contactid';
		$first_email = 'email';
	}
	if ($parentmodule == 'Accounts') {
		$tablename = 'vtiger_account';
		$idname = 'accountid';
		$first_email = 'email1';
	}
	if ($parentid != '') {
		$adb = PearDatabase::getInstance();
		$query = sprintf('select * from %s where %s = ?', $tablename, $idname);
		$res = $adb->pquery($query, [$parentid]);
		$mailid = $adb->query_result($res, 0, $first_email);
	}

	return $mailid;
}

/** 	Function to parse and get the mail error
 * 	$mail -- reference of the mail object
 * 	$mail_status -- status of the mail which is sent or not
 * 	$to -- the email address to whom we sent the mail and failes
 * 	return -- Mail error occured during the mail sending process
 */
function getMailError($mail, $mail_status, $to)
{
	//Error types in class.phpmailer.php
	/*
	  provide_address, mailer_not_supported, execute, instantiate, file_access, file_open, encoding, data_not_accepted, authenticate,
	  connect_host, recipients_failed, from_failed
	 */
	$log = LoggerManager::getInstance();
	$log->info('Inside the function getMailError');

	$msg = array_search($mail_status, $mail->language);
	$log->info("Error message ==> $msg");

	if ($msg == 'connect_host') {
		$error_msg = $msg;
	} elseif (strstr($msg, 'from_failed')) {
		$error_msg = $msg;
	} elseif (strstr($msg, 'recipients_failed')) {
		$error_msg = $msg;
	} else {
		$log->info('Mail error is not as connect_host or from_failed or recipients_failed');
		$error_msg = $msg;
	}

	$log->info("return error => $error_msg");
	return $error_msg;
}

/** 	Function to get the mail status string (string of sent mail status)
 * 	$mail_status_str -- concatenated string with all the error messages with &&& seperation
 * 	return - the error status as a encoded string
 */
function getMailErrorString($mail_status_str)
{
	$log = LoggerManager::getInstance();
	$log->debug('Inside getMailErrorString function.\nMail status string ==> ' . $mail_status_str);

	$mail_status_str = trim($mail_status_str, '&&&');
	$mail_status_array = explode('&&&', $mail_status_str);
	$log->debug('All Mail status ==>\n' . $mail_status_str . '\n');

	foreach ($mail_status_array as $key => $val) {
		$list = explode('=', $val);
		$log->debug('Mail id & status => ' . $list[0] . ' = ' . $list[1]);
		if ($list[1] == 0) {
			$mail_error_str .= $list[0] . '=' . $list[1] . '&&&';
		}
	}
	$log->debug('Mail error string => ' . $mail_error_str);
	if ($mail_error_str != '') {
		$mail_error_str = 'mail_error=' . base64_encode($mail_error_str);
	}
	return $mail_error_str;
}

/** 	Function to parse the error string
 * 	$mail_error_str -- base64 encoded string which contains the mail sending errors as concatenated with &&&
 * 	return - Error message to display
 */
function parseEmailErrorString($mail_error_str)
{
	$log = LoggerManager::getInstance();
	$log->debug('Inside the parseEmailErrorString function.\n encoded mail error string ==> ' . $mail_error_str);

	$mail_error = base64_decode($mail_error_str);
	$log->debug('Original error string => ' . $mail_error);
	$mail_status = explode("&&&", trim($mail_error, "&&&"));
	foreach ($mail_status as $key => $val) {
		$status_str = explode("=", $val);
		$log->debug('Mail id => "' . $status_str[0] . '".........status => "' . $status_str[1] . '"');
		if ($status_str[1] != 1 && $status_str[1] != '') {
			$log->debug('Error in mail sending');
			if ($status_str[1] == 'connect_host') {
				$log->debug('if part - Mail sever is not configured');
				$errorstr .= '<br><b><font color=red>' . vtranslate('MESSAGE_CHECK_MAIL_SERVER_NAME') . '</font></b>';
				break;
			} elseif ($status_str[1] == '0') {
				$log->debug("first elseif part - status will be 0 which is the case of assigned to vtiger_users's email is empty.");
				$errorstr .= '<br><b><font color=red> ' . vtranslate('MESSAGE_MAIL_COULD_NOT_BE_SEND') . ' ' . vtranslate('MESSAGE_PLEASE_CHECK_FROM_THE_MAILID') . '</font></b>';
				//Added to display the message about the CC && BCC mail sending status
				if ($status_str[0] == 'cc_success') {
					$cc_msg = 'But the mail has been sent to CC & BCC addresses.';
					$errorstr .= '<br><b><font color=purple>' . $cc_msg . '</font></b>';
				}
			} elseif (strstr($status_str[1], 'from_failed')) {
				$log->debug('second elseif part - from email id is failed.');
				$from = explode('from_failed', $status_str[1]);
				$errorstr .= "<br><b><font color=red>" . vtranslate('MESSAGE_PLEASE_CHECK_THE_FROM_MAILID') . " '" . $from[1] . "'</font></b>";
			} else {
				$log->debug('else part - mail send process failed due to the following reason.');
				$errorstr .= "<br><b><font color=red> " . vtranslate('MESSAGE_MAIL_COULD_NOT_BE_SEND_TO_THIS_EMAILID') . " '" . $status_str[0] . "'. " . vtranslate('PLEASE_CHECK_THIS_EMAILID') . "</font></b>";
			}
		}
	}
	$log->debug('Return Error string => ' . $errorstr);
	return $errorstr;
}

function isUserInitiated()
{
	return ( AppRequest::get('module') == 'Emails' &&
		( AppRequest::get('action') == 'mailsend' || AppRequest::get('action') == 'Save'));
}

/**
 * Function to get the group users Email ids
 */
function getDefaultAssigneeEmailIds($groupId)
{
	$adb = PearDatabase::getInstance();
	$emails = [];
	if ($groupId != '') {
		require_once 'include/utils/GetGroupUsers.php';
		$userGroups = new GetGroupUsers();
		$userGroups->getAllUsersInGroup($groupId);

		if (count($userGroups->group_users) == 0)
			return [];

		$query = sprintf('SELECT 
					email1 
				FROM
					vtiger_users 
				WHERE vtiger_users.id IN (%s) 
					AND vtiger_users.status = ?', generateQuestionMarks($userGroups->group_users));
		$result = $adb->pquery($query, [$userGroups->group_users, 'Active']);
		$rows = $adb->num_rows($result);
		for ($i = 0; $i < $rows; $i++) {
			$email = $adb->query_result($result, $i, 'email1');
			array_push($emails, $email);
		}
		$log->debug('Email ids are selected  => ' . $emails);
		return $emails;
	} else {
		$log->debug('User id is empty. so return value is ');
		return [];
	}
}
