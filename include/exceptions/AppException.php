<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class AppException extends Exception
{
	
}

class NoPermittedException extends Exception
{

	public function __construct($message = null, $code = 0)
	{
		parent::__construct($message, $code);
		$request = new Vtiger_Request($_REQUEST);
		$dbLog = PearDatabase::getInstance('log');
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$dbLog->insert('l_yf_access_for_user', [
			'username' => $currentUser->getDisplayName(),
			'date' => date('Y-m-d H:i:s'),
			'ip' => Vtiger_Functions::getRemoteIP(),
			'module' => $request->getModule(),
			'url' => Vtiger_Functions::getBrowserInfo()->url,
			'agent' => $_SERVER['HTTP_USER_AGENT'],
			'request' => json_encode($_REQUEST),
		]);
	}
}

class NoPermittedToRecordException extends Exception
{

	public function __construct($message = null, $code = 0)
	{
		parent::__construct($message, $code);
		$request = new Vtiger_Request($_REQUEST);
		$dbLog = PearDatabase::getInstance('log');
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$dbLog->insert('l_yf_access_to_record', [
			'username' => $currentUser->getDisplayName(),
			'date' => date('Y-m-d H:i:s'),
			'ip' => Vtiger_Functions::getRemoteIP(),
			'record' => $request->get('record'),
			'module' => $request->getModule(),
			'url' => Vtiger_Functions::getBrowserInfo()->url,
			'agent' => $_SERVER['HTTP_USER_AGENT'],
			'request' => json_encode($_REQUEST),
		]);
	}
}

class NoPermittedForAdminException extends Exception
{

	public function __construct($message = null, $code = 0)
	{
		parent::__construct($message, $code);
		$request = new Vtiger_Request($_REQUEST);
		$dbLog = PearDatabase::getInstance('log');
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$dbLog->insert('l_yf_access_for_admin', [
			'username' => $currentUser->getDisplayName(),
			'date' => date('Y-m-d H:i:s'),
			'ip' => Vtiger_Functions::getRemoteIP(),
			'module' => $request->getModule(),
			'url' => Vtiger_Functions::getBrowserInfo()->url,
			'agent' => $_SERVER['HTTP_USER_AGENT'],
			'request' => json_encode($_REQUEST),
		]);
	}
}

class CsrfException extends Exception
{

	public function __construct($message = null, $code = 0)
	{
		parent::__construct($message, $code);
		$dbLog = PearDatabase::getInstance('log');
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$dbLog->insert('l_yf_csrf', [
			'username' => $currentUser->getDisplayName(),
			'date' => date('Y-m-d H:i:s'),
			'ip' => Vtiger_Functions::getRemoteIP(),
			'referer' => $_SERVER['HTTP_REFERER'],
			'url' => Vtiger_Functions::getBrowserInfo()->url,
			'agent' => $_SERVER['HTTP_USER_AGENT'],
		]);
	}
}
