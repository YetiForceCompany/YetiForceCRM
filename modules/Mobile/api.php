<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
header('Content-Type: text/json');
chdir(dirname(__FILE__) . '/../../');

/**
 * URL Verfication - Required to overcome Apache mis-configuration and leading to shared setup mode.
 */
require_once 'config/config.php';

// Define GetRelatedList API before including the core files
// NOTE: Make sure GetRelatedList function_exists check is made in include/utils/RelatedListView.php
include_once dirname(__FILE__) . '/api/Relation.php';

include_once dirname(__FILE__) . '/api/Request.php';
include_once dirname(__FILE__) . '/api/Response.php';
include_once dirname(__FILE__) . '/api/Session.php';

include_once dirname(__FILE__) . '/api/ws/Controller.php';
require_once 'include/main/WebUI.php';

class Mobile_API_Controller
{

	static $opControllers = array(
		'login' => array('file' => '/api/ws/Login.php', 'class' => 'Mobile_WS_Login'),
		'loginAndFetchModules' => array('file' => '/api/ws/LoginAndFetchModules.php', 'class' => 'Mobile_WS_LoginAndFetchModules'),
		'fetchModuleFilters' => array('file' => '/api/ws/FetchModuleFilters.php', 'class' => 'Mobile_WS_FetchModuleFilters'),
		'filterDetailsWithCount' => array('file' => '/api/ws/FilterDetailsWithCount.php', 'class' => 'Mobile_WS_FilterDetailsWithCount'),
		'fetchAllAlerts' => array('file' => '/api/ws/FetchAllAlerts.php', 'class' => 'Mobile_WS_FetchAllAlerts'),
		'alertDetailsWithMessage' => array('file' => '/api/ws/AlertDetailsWithMessage.php', 'class' => 'Mobile_WS_AlertDetailsWithMessage'),
		'listModuleRecords' => array('file' => '/api/ws/ListModuleRecords.php', 'class' => 'Mobile_WS_ListModuleRecords'),
		'fetchRecord' => array('file' => '/api/ws/FetchRecord.php', 'class' => 'Mobile_WS_FetchRecord'),
		'fetchRecordWithGrouping' => array('file' => '/api/ws/FetchRecordWithGrouping.php', 'class' => 'Mobile_WS_FetchRecordWithGrouping'),
		'fetchRecordsWithGrouping' => array('file' => '/api/ws/FetchRecordsWithGrouping.php', 'class' => 'Mobile_WS_FetchRecordsWithGrouping'),
		'describe' => array('file' => '/api/ws/Describe.php', 'class' => 'Mobile_WS_Describe'),
		'saveRecord' => array('file' => '/api/ws/SaveRecord.php', 'class' => 'Mobile_WS_SaveRecord'),
		'syncModuleRecords' => array('file' => '/api/ws/SyncModuleRecords.php', 'class' => 'Mobile_WS_SyncModuleRecords'),
		'query' => array('file' => '/api/ws/Query.php', 'class' => 'Mobile_WS_Query'),
		'queryWithGrouping' => array('file' => '/api/ws/QueryWithGrouping.php', 'class' => 'Mobile_WS_QueryWithGrouping'),
		'relatedRecordsWithGrouping' => array('file' => '/api/ws/RelatedRecordsWithGrouping.php', 'class' => 'Mobile_WS_RelatedRecordsWithGrouping'),
		'deleteRecords' => array('file' => '/api/ws/DeleteRecords.php', 'class' => 'Mobile_WS_DeleteRecords'),
		'addRecordComment' => array('file' => '/api/ws/AddRecordComment.php', 'class' => 'Mobile_WS_AddRecordComment'),
		'history' => array('file' => '/api/ws/History.php', 'class' => 'Mobile_WS_History'),
		'taxByType' => array('file' => '/api/ws/TaxByType.php', 'class' => 'Mobile_WS_TaxByType'),
		'fetchModuleOwners' => array('file' => '/api/ws/FetchModuleOwners.php', 'class' => 'Mobile_WS_FetchModuleOwners')
	);

	static function process(Mobile_API_Request $request)
	{
		$operation = $request->getOperation();
		$sessionid = $request->getSession();

		$response = false;
		if (isset(self::$opControllers[$operation])) {

			$operationFile = self::$opControllers[$operation]['file'];
			$operationClass = self::$opControllers[$operation]['class'];

			include_once dirname(__FILE__) . $operationFile;
			$operationController = new $operationClass;

			$operationSession = false;
			if ($operationController->requireLogin()) {
				$operationSession = Mobile_API_Session::init($sessionid);
				if ($operationController->hasActiveUser() === false) {
					$operationSession = false;
				}
				//Mobile_WS_Utils::initAppGlobals();
			} else {
				// By-pass login
				$operationSession = true;
			}

			if ($operationSession === false) {
				$response = new Mobile_API_Response();
				$response->setError(1501, 'Login required');
			} else {

				try {
					$response = $operationController->process($request);
				} catch (Exception $e) {
					$response = new Mobile_API_Response();
					$response->setError($e->getCode(), $e->getMessage());
				}
			}
		} else {
			$response = new Mobile_API_Response();
			$response->setError(1404, 'Operation not found: ' . $operation);
		}

		if ($response !== false) {
			echo $response->emitJSON();
		}
	}
}

/** Take care of stripping the slashes */
function stripslashes_recursive($value)
{
	$value = is_array($value) ? array_map('stripslashes_recursive', $value) : stripslashes($value);
	return $value;
}
/** END * */
if (!defined('MOBILE_API_CONTROLLER_AVOID_TRIGGER')) {
	$clientRequestValues = $_POST; // $_REQUEST or $_GET

	$clientRequestValuesRaw = array();

	// Set of request key few controllers are interested in raw values (example, SaveRecord)
	/* $rawValueHeaders = array('values');
	  foreach($rawValueHeaders as $rawValueHeader) {
	  if(isset($clientRequestValues[$rawValueHeader])) {
	  $clientRequestValuesRaw[$rawValueHeader] = $clientRequestValues[$rawValueHeader];
	  }
	  } */
	// END

	if (get_magic_quotes_gpc()) {
		$clientRequestValues = stripslashes_recursive($clientRequestValues);
	}
	Mobile_API_Controller::process(new Mobile_API_Request($clientRequestValues, $clientRequestValuesRaw));
}
