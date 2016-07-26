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
		Vtiger_Session::init();

		$request = AppRequest::init();
		$dbLog = PearDatabase::getInstance('log');
		$userName = Vtiger_Session::get('full_user_name');
		$dbLog->insert('o_yf_access_for_user', [
			'username' => empty($userName) ? '-' : $userName,
			'date' => date('Y-m-d H:i:s'),
			'ip' => vtlib\Functions::getRemoteIP(),
			'module' => $request->getModule(),
			'url' => vtlib\Functions::getBrowserInfo()->url,
			'agent' => $_SERVER['HTTP_USER_AGENT'],
			'request' => json_encode($_REQUEST),
			'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''
		]);
	}
}

class NoPermittedToRecordException extends Exception
{

	public function __construct($message = null, $code = 0)
	{
		parent::__construct($message, $code);
		Vtiger_Session::init();

		$request = AppRequest::init();
		$dbLog = PearDatabase::getInstance('log');
		$userName = Vtiger_Session::get('full_user_name');
		$dbLog->insert('o_yf_access_to_record', [
			'username' => empty($userName) ? '-' : $userName,
			'date' => date('Y-m-d H:i:s'),
			'ip' => vtlib\Functions::getRemoteIP(),
			'record' => $request->get('record'),
			'module' => $request->getModule(),
			'url' => vtlib\Functions::getBrowserInfo()->url,
			'agent' => $_SERVER['HTTP_USER_AGENT'],
			'request' => json_encode($_REQUEST),
			'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''
		]);
	}
}

class NoPermittedForAdminException extends Exception
{

	public function __construct($message = null, $code = 0)
	{
		parent::__construct($message, $code);
		Vtiger_Session::init();

		$request = AppRequest::init();
		$dbLog = PearDatabase::getInstance('log');
		$userName = Vtiger_Session::get('full_user_name');
		$dbLog->insert('o_yf_access_for_admin', [
			'username' => empty($userName) ? '-' : $userName,
			'date' => date('Y-m-d H:i:s'),
			'ip' => vtlib\Functions::getRemoteIP(),
			'module' => $request->getModule(),
			'url' => vtlib\Functions::getBrowserInfo()->url,
			'agent' => $_SERVER['HTTP_USER_AGENT'],
			'request' => json_encode($_REQUEST),
			'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''
		]);
	}
}

class CsrfException extends Exception
{

	public function __construct($message = null, $code = 0)
	{
		parent::__construct($message, $code);
		Vtiger_Session::init();

		$dbLog = PearDatabase::getInstance('log');
		$userName = Vtiger_Session::get('full_user_name');
		$dbLog->insert('o_yf_csrf', [
			'username' => empty($userName) ? '-' : $userName,
			'date' => date('Y-m-d H:i:s'),
			'ip' => vtlib\Functions::getRemoteIP(),
			'referer' => $_SERVER['HTTP_REFERER'],
			'url' => vtlib\Functions::getBrowserInfo()->url,
			'agent' => $_SERVER['HTTP_USER_AGENT'],
		]);
	}
}

class APINoPermittedException extends Exception
{

	public function __construct($message = null, $code = 0)
	{
		parent::__construct($message, $code);
		Vtiger_Session::init();

		$request = AppRequest::init();
		$dbLog = PearDatabase::getInstance('log');
		$userName = Vtiger_Session::get('full_user_name');
		$dbLog->insert('o_yf_access_for_api', [
			'username' => empty($userName) ? '-' : $userName,
			'date' => date('Y-m-d H:i:s'),
			'ip' => vtlib\Functions::getRemoteIP(),
			'url' => vtlib\Functions::getBrowserInfo()->url,
			'agent' => $_SERVER['HTTP_USER_AGENT'],
			'request' => json_encode($_REQUEST),
		]);
	}

	public function stop($message)
	{
		die(json_encode($message));
	}
}
