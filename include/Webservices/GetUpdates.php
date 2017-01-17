<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */
require_once 'include/Webservices/Utils.php';
require_once 'include/Webservices/ModuleTypes.php';
require_once 'include/utils/CommonUtils.php';
require_once 'include/Webservices/DescribeObject.php';

function vtws_sync($mtime, $elementType, $syncType, $user)
{
	return 'Currently not supported';

	global $recordString, $modifiedTimeString;
	$adb = PearDatabase::getInstance();
	$numRecordsLimit = 100;
	$ignoreModules = array("Users");
	$typed = true;
	$dformat = "Y-m-d H:i:s";
	$datetime = date($dformat, $mtime);
	$setypeArray = [];
	$setypeData = [];
	$setypeHandler = [];
	$setypeNoAccessArray = [];

	$output = [];
	$output["updated"] = [];
	$output["deleted"] = [];

	$applicationSync = false;
	if (is_object($syncType) && ($syncType instanceof Users)) {
		$user = $syncType;
	} else if ($syncType == 'application') {
		$applicationSync = true;
	} else if ($syncType == 'userandgroup') {
		$userAndGroupSync = true;
	}

	if ($applicationSync && !\vtlib\Functions::userIsAdministrator($user)) {
		throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED, "Only admin users can perform application sync");
	}

	$ownerIds = array($user->id);
	// To get groupids in which this user exist
	if ($userAndGroupSync) {
		$groupresult = $adb->pquery("select groupid from vtiger_users2group where userid=?", array($user->id));
		$numOfRows = $adb->num_rows($groupresult);
		if ($numOfRows > 0) {
			for ($i = 0; $i < $numOfRows; $i++) {
				$ownerIds[count($ownerIds)] = $adb->query_result($groupresult, $i, "groupid");
			}
		}
	}
	// End


	if (!isset($elementType) || $elementType == '' || $elementType === null) {
		$typed = false;
	}



	$adb->startTransaction();

	$accessableModules = [];
	$entityModules = [];
	$modulesDetails = vtws_listtypes(null, $user);
	$moduleTypes = $modulesDetails['types'];
	$modulesInformation = $modulesDetails["information"];

	foreach ($modulesInformation as $moduleName => $entityInformation) {
		if ($entityInformation["isEntity"])
			$entityModules[] = $moduleName;
	}
	if (!$typed) {
		$accessableModules = $entityModules;
	} else {
		if (!in_array($elementType, $entityModules))
			throw new WebServiceException(WebServiceErrorCode::$ACCESSDENIED, "Permission to perform the operation is denied");
		$accessableModules[] = $elementType;
	}

	$accessableModules = array_diff($accessableModules, $ignoreModules);

	if (count($accessableModules) <= 0) {
		$output['lastModifiedTime'] = $mtime;
		$output['more'] = false;
		return $output;
	}

	if ($typed) {
		$handler = vtws_getModuleHandlerFromName($elementType, $user);
		$moduleMeta = $handler->getMeta();
		$entityDefaultBaseTables = $moduleMeta->getEntityDefaultTableList();
		//since there will be only one base table for all entities
		$baseCRMTable = $entityDefaultBaseTables[0];
		if ($elementType == "Calendar" || $elementType == "Events") {
			$baseCRMTable = getSyncQueryBaseTable($elementType);
		}
	} else
		$baseCRMTable = " vtiger_crmentity ";

	//modifiedtime - next token
	$q = "SELECT modifiedtime FROM $baseCRMTable WHERE  modifiedtime>? and setype IN(" . generateQuestionMarks($accessableModules) . ") ";
	$params = array($datetime);
	foreach ($accessableModules as $entityModule) {
		if ($entityModule == "Events")
			$entityModule = "Calendar";
		$params[] = $entityModule;
	}
	if (!$applicationSync) {
		$q .= ' and smownerid IN(' . generateQuestionMarks($ownerIds) . ')';
		$params = array_merge($params, $ownerIds);
	}

	$q .= " order by modifiedtime limit $numRecordsLimit";
	$result = $adb->pquery($q, $params);

	$modTime = [];
	$countResult = $adb->num_rows($result);
	for ($i = 0; $i < $countResult; $i++) {
		$modTime[] = $adb->query_result($result, $i, 'modifiedtime');
	}
	if (!empty($modTime)) {
		$maxModifiedTime = max($modTime);
	}
	if (!$maxModifiedTime) {
		$maxModifiedTime = $datetime;
	}
	foreach ($accessableModules as $elementType) {
		$handler = vtws_getModuleHandlerFromName($elementType, $user);
		$moduleMeta = $handler->getMeta();
		$deletedQueryCondition = $moduleMeta->getEntityDeletedQuery();
		preg_match_all("/(?:\s+\w+[ \t\n\r]+)?([^=]+)\s*=([^\s]+|'[^']+')/", $deletedQueryCondition, $deletedFieldDetails);
		$fieldNameDetails = $deletedFieldDetails[1];
		$deleteFieldValues = $deletedFieldDetails[2];
		$deleteColumnNames = [];
		foreach ($fieldNameDetails as $tableName_fieldName) {
			$fieldComp = explode(".", $tableName_fieldName);
			$deleteColumnNames[$tableName_fieldName] = $fieldComp[1];
		}
		$params = array($moduleMeta->getTabName(), $datetime, $maxModifiedTime);


		$queryGenerator = new QueryGenerator($elementType, $user);
		$fields = [];
		$moduleFields = $moduleMeta->getModuleFields();
		$moduleFieldNames = getSelectClauseFields($elementType, $moduleMeta, $user);
		$moduleFieldNames[] = 'id';
		$queryGenerator->setFields($moduleFieldNames);
		$selectClause = sprintf("SELECT %s", $queryGenerator->getSelectClauseColumnSQL());
		// adding the fieldnames that are present in the delete condition to the select clause
		// since not all fields present in delete condition will be present in the fieldnames of the module
		foreach ($deleteColumnNames as $table_fieldName => $columnName) {
			if (!in_array($columnName, $moduleFieldNames)) {
				$selectClause .= ", " . $table_fieldName;
			}
		}
		$fromClause = $queryGenerator->getFromClause();
		$fromClause .= " INNER JOIN (select modifiedtime, crmid,deleted,setype FROM $baseCRMTable WHERE setype=? and modifiedtime >? and modifiedtime<=?";
		if (!$applicationSync) {
			$fromClause .= 'and smownerid IN(' . generateQuestionMarks($ownerIds) . ')';
			$params = array_merge($params, $ownerIds);
		}
		$fromClause .= ' ) vtiger_ws_sync ON (vtiger_crmentity.crmid = vtiger_ws_sync.crmid)';
		$q = $selectClause . " " . $fromClause;
		$result = $adb->pquery($q, $params);
		$recordDetails = [];
		$deleteRecordDetails = [];
		while ($arre = $adb->fetchByAssoc($result)) {
			$key = $arre[$moduleMeta->getIdColumn()];
			if (vtws_isRecordDeleted($arre, $deleteColumnNames, $deleteFieldValues)) {
				if (!$moduleMeta->hasAccess()) {
					continue;
				}
				$output["deleted"][] = vtws_getId($moduleMeta->getEntityId(), $key);
			} else {
				if (!$moduleMeta->hasAccess() || !$moduleMeta->hasPermission(EntityMeta::$RETRIEVE, $key)) {
					continue;
				}
				try {
					$output["updated"][] = DataTransform::sanitizeDataWithColumn($arre, $moduleMeta);
				} catch (WebServiceException $e) {
					//ignore records the user doesn't have access to.
					continue;
				} catch (Exception $e) {
					throw new WebServiceException(WebServiceErrorCode::$INTERNALERROR, "Unknown Error while processing request");
				}
			}
		}
	}

	$q = "SELECT crmid FROM $baseCRMTable WHERE modifiedtime>?  and setype IN(" . generateQuestionMarks($accessableModules) . ")";
	$params = array($maxModifiedTime);

	foreach ($accessableModules as $entityModule) {
		if ($entityModule == "Events")
			$entityModule = "Calendar";
		$params[] = $entityModule;
	}
	if (!$applicationSync) {
		$q .= 'and smownerid IN(' . generateQuestionMarks($ownerIds) . ')';
		$params = array_merge($params, $ownerIds);
	}

	$result = $adb->pquery($q, $params);
	if ($adb->num_rows($result) > 0) {
		$output['more'] = true;
	} else {
		$output['more'] = false;
	}
	if (!$maxModifiedTime) {
		$modifiedtime = $mtime;
	} else {
		$modifiedtime = vtws_getSeconds($maxModifiedTime);
	}
	if (is_string($modifiedtime)) {
		$modifiedtime = intval($modifiedtime);
	}
	$output['lastModifiedTime'] = $modifiedtime;

	$error = $adb->hasFailedTransaction();
	$adb->completeTransaction();

	if ($error) {
		throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, vtws_getWebserviceTranslatedString('LBL_' .
			WebServiceErrorCode::$DATABASEQUERYERROR));
	}

	VTWS_PreserveGlobal::flush();
	return $output;
}

function vtws_getSeconds($mtimeString)
{
	return strtotime($mtimeString);
}

function vtws_isRecordDeleted($recordDetails, $deleteColumnDetails, $deletedValues)
{
	$deletedRecord = false;
	$i = 0;
	foreach ($deleteColumnDetails as $tableName_fieldName => $columnName) {
		if ($recordDetails[$columnName] != $deletedValues[$i++]) {
			$deletedRecord = true;
			break;
		}
	}
	return $deletedRecord;
}

function getSyncQueryBaseTable($elementType)
{
	if ($elementType != "Calendar" && $elementType != "Events") {
		return "vtiger_crmentity";
	} else {
		$activityCondition = getCalendarTypeCondition($elementType);
		$query = "vtiger_crmentity INNER JOIN vtiger_activity ON (vtiger_crmentity.crmid = vtiger_activity.activityid and $activityCondition)";
		return $query;
	}
}

function getCalendarTypeCondition($elementType)
{
	if ($elementType == "Events")
		$activityCondition = "vtiger_activity.activitytype !='Task'";
	else
		$activityCondition = "vtiger_activity.activitytype ='Task'";
	return $activityCondition;
}

function getSelectClauseFields($module, $moduleMeta, $user)
{
	$moduleFieldNames = $moduleMeta->getModuleFields();
	return array_keys($moduleFieldNames);
}
