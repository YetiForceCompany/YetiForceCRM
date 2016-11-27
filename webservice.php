<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ****************************************************************************** */
include_once('include/main/WebUI.php');
require_once('include/Webservices/Utils.php');
require_once('include/Webservices/State.php');
require_once('include/Webservices/OperationManager.php');
require_once('include/Webservices/SessionManager.php');

require_once('config/api.php');
if (!in_array('webservices', $enabledServices)) {
	echo 'Webservice - Service is not active';
	return;
}

$API_VERSION = "0.22";
$adb = & PearDatabase::getInstance();

function getRequestParamsArrayForOperation($operation)
{
	global $operationInput;
	return $operationInput[$operation];
}

function setResponseHeaders()
{
	header('Content-type: application/json');
}

function writeErrorOutput($operationManager, $error)
{

	setResponseHeaders();
	$state = new State();
	$state->success = false;
	$state->error = $error;
	unset($state->result);
	$output = $operationManager->encode($state);
	echo $output;
}

function writeOutput($operationManager, $data)
{

	setResponseHeaders();
	$state = new State();
	$state->success = true;
	$state->result = $data;
	unset($state->error);
	$output = $operationManager->encode($state);
	echo $output;
}
$operation = AppRequest::get('operation');
$operation = strtolower($operation);
$format = AppRequest::get('format', 'json');
$sessionId = AppRequest::get('sessionName');

try {
	$sessionManager = new SessionManager();
	$operationManager = new OperationManager($adb, $operation, $format, $sessionManager);
} catch (WebServiceException $ex) {
	echo $ex->getMessage();
	return;
}
try {
	if (!$sessionId || strcasecmp($sessionId, "null") === 0) {
		$sessionId = null;
	}

	$input = $operationManager->getOperationInput();
	$adoptSession = false;
	if (strcasecmp($operation, "extendsession") === 0) {
		if (isset($input['operation'])) {
			// Workaround fix for PHP 5.3.x: $_REQUEST doesn't have PHPSESSID
			if (AppRequest::has('PHPSESSID')) {
				$sessionId = AppRequest::get('PHPSESSID');
			} else {
				// NOTE: Need to evaluate for possible security issues
				$sessionId = vtws_getParameter($_COOKIE, 'PHPSESSID');
			}
			// END
			$adoptSession = true;
		} else {
			writeErrorOutput($operationManager, new WebServiceException(WebServiceErrorCode::$AUTHREQUIRED, "Authencation required"));
			return;
		}
	}
	$sid = $sessionManager->startSession($sessionId, $adoptSession);

	if (!$sessionId && !$operationManager->isPreLoginOperation()) {
		writeErrorOutput($operationManager, new WebServiceException(WebServiceErrorCode::$AUTHREQUIRED, "Authencation required"));
		return;
	}

	if (!$sid) {
		writeErrorOutput($operationManager, $sessionManager->getError());
		return;
	}

	$userid = $sessionManager->get("authenticatedUserId");

	if ($userid) {

		$seed_user = new Users();
		$current_user = $seed_user->retrieveCurrentUserInfoFromFile($userid);
	} else {
		$current_user = null;
	}

	$operationInput = $operationManager->sanitizeOperation($input);
	$includes = $operationManager->getOperationIncludes();

	foreach ($includes as $ind => $path) {
		\vtlib\Deprecated::checkFileAccessForInclusion($path);
		require_once($path);
	}
	$rawOutput = $operationManager->runOperation($operationInput, $current_user);
	writeOutput($operationManager, $rawOutput);
} catch (WebServiceException $e) {
	writeErrorOutput($operationManager, $e);
} catch (Exception $e) {
	writeErrorOutput($operationManager, new WebServiceException(WebServiceErrorCode::$INTERNALERROR, "Unknown Error while processing request"));
}
