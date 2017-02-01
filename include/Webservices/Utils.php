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

require_once('include/database/PearDatabase.php');
require_once("modules/Users/Users.php");
require_once 'include/Webservices/WebserviceField.php';
require_once 'include/Webservices/EntityMeta.php';
require_once 'include/Webservices/VtigerWebserviceObject.php';
require_once("include/Webservices/VtigerCRMObject.php");
require_once("include/Webservices/VtigerCRMObjectMeta.php");
require_once("include/Webservices/DataTransform.php");
require_once("include/Webservices/WebServiceError.php");
require_once 'include/utils/utils.php';
require_once 'include/utils/UserInfoUtil.php';
require_once 'include/Webservices/ModuleTypes.php';
require_once 'include/utils/VtlibUtils.php';
require_once 'include/Webservices/WebserviceEntityOperation.php';
require_once 'include/Webservices/PreserveGlobal.php';

/* Function to return all the users in the groups that this user is part of.
 * @param $id - id of the user
 * returns Array:UserIds userid of all the users in the groups that this user is part of.
 */

function vtws_getUsersInTheSameGroup($id)
{
	require_once('include/utils/GetGroupUsers.php');
	require_once('include/utils/GetUserGroups.php');

	$groupUsers = new GetGroupUsers();
	$userGroups = new GetUserGroups();
	$allUsers = [];
	$userGroups->getAllUserGroups($id);
	$groups = $userGroups->user_groups;

	foreach ($groups as $group) {
		$groupUsers->getAllUsersInGroup($group);
		$usersInGroup = $groupUsers->group_users;
		foreach ($usersInGroup as $user) {
			if ($user != $id) {
				$allUsers[$user] = \App\Fields\Owner::getUserLabel($user);
			}
		}
	}
	return $allUsers;
}

function vtws_generateRandomAccessKey($length = 10)
{
	$source = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$accesskey = "";
	$maxIndex = strlen($source);
	for ($i = 0; $i < $length; ++$i) {
		$accesskey = $accesskey . substr($source, rand(null, $maxIndex), 1);
	}
	return $accesskey;
}

/**
 * get current vtiger version from the database.
 */
function vtws_getVtigerVersion()
{
	$adb = PearDatabase::getInstance();
	$query = 'select * from vtiger_version';
	$result = $adb->pquery($query, []);
	$version = '';
	while ($row = $adb->fetch_array($result)) {
		$version = $row['current_version'];
	}
	return $version;
}

function vtws_getUserAccessibleGroups($moduleId, $user)
{
	$adb = PearDatabase::getInstance();
	require('user_privileges/user_privileges_' . $user->id . '.php');
	require('user_privileges/sharing_privileges_' . $user->id . '.php');
	$tabName = \App\Module::getModuleName($moduleId);
	if ($is_admin === false && $profileGlobalPermission[2] == 1 &&
		($defaultOrgSharingPermission[$moduleId] == 3 || $defaultOrgSharingPermission[$moduleId] == 0)) {
		$result = get_current_user_access_groups($tabName);
	} else {
		$result = \vtlib\Functions::get_group_options();
	}

	$groups = [];
	if ($result != null && $result != '' && is_object($result)) {
		$rowCount = $adb->num_rows($result);
		for ($i = 0; $i < $rowCount; $i++) {
			$nameArray = $adb->query_result_rowdata($result, $i);
			$groupId = $nameArray["groupid"];
			$groupName = $nameArray["groupname"];
			$groups[] = array('id' => $groupId, 'name' => $groupName);
		}
	}
	return $groups;
}

function vtws_getWebserviceGroupFromGroups($groups)
{
	$adb = PearDatabase::getInstance();
	$webserviceObject = VtigerWebserviceObject::fromName($adb, 'Groups');
	foreach ($groups as $index => $group) {
		$groups[$index]['id'] = vtws_getId($webserviceObject->getEntityId(), $group['id']);
	}
	return $groups;
}

function vtws_getUserWebservicesGroups($tabId, $user)
{
	$groups = vtws_getUserAccessibleGroups($tabId, $user);
	return vtws_getWebserviceGroupFromGroups($groups);
}

function vtws_getIdComponents($elementid)
{
	return explode("x", $elementid);
}

function vtws_getId($objId, $elemId)
{
	return $objId . "x" . $elemId;
}

function getEmailFieldId($meta, $entityId)
{
	$adb = PearDatabase::getInstance();
	//no email field accessible in the module. since its only association pick up the field any way.
	$query = "SELECT fieldid,fieldlabel,columnname FROM vtiger_field WHERE tabid=?
		and uitype=13 and presence in (0,2)";
	$result = $adb->pquery($query, array($meta->getTabId()));

	//pick up the first field.
	$fieldId = $adb->query_result($result, 0, 'fieldid');
	return $fieldId;
}

function vtws_getParameter($parameterArray, $paramName, $default = null)
{

	if (!get_magic_quotes_gpc()) {
		if (is_array($parameterArray[$paramName])) {
			$param = array_map('addslashes', $parameterArray[$paramName]);
		} else {
			$param = addslashes($parameterArray[$paramName]);
		}
	} else {
		$param = $parameterArray[$paramName];
	}
	if (!$param) {
		$param = $default;
	}
	return $param;
}

function vtws_getEntityNameFields($moduleName)
{

	$adb = PearDatabase::getInstance();
	$query = "select fieldname,tablename,entityidfield from vtiger_entityname where modulename = ?";
	$result = $adb->pquery($query, array($moduleName));
	$rowCount = $adb->num_rows($result);
	$nameFields = [];
	if ($rowCount > 0) {
		$fieldsname = $adb->query_result($result, 0, 'fieldname');
		if (!(strpos($fieldsname, ',') === false)) {
			$nameFields = explode(',', $fieldsname);
		} else {
			array_push($nameFields, $fieldsname);
		}
	}
	return $nameFields;
}

/** function to get the module List to which are crm entities.
 *  @return Array modules list as array
 */
function vtws_getModuleNameList()
{
	$adb = PearDatabase::getInstance();

	$sql = "select name from vtiger_tab where isentitytype=1 and name not in ('Rss'," .
		"'Recyclebin','Events') order by tabsequence";
	$res = $adb->pquery($sql, []);
	$mod_array = [];
	while ($row = $adb->fetchByAssoc($res)) {
		array_push($mod_array, $row['name']);
	}
	return $mod_array;
}

function vtws_getWebserviceEntities()
{
	$adb = PearDatabase::getInstance();

	$sql = "select name,id,ismodule from vtiger_ws_entity";
	$res = $adb->pquery($sql, []);
	$moduleArray = [];
	$entityArray = [];
	while ($row = $adb->fetchByAssoc($res)) {
		if ($row['ismodule'] == '1') {
			array_push($moduleArray, $row['name']);
		} else {
			array_push($entityArray, $row['name']);
		}
	}
	return array('module' => $moduleArray, 'entity' => $entityArray);
}

/**
 *
 * @param VtigerWebserviceObject $webserviceObject
 * @return CRMEntity
 */
function vtws_getModuleInstance($webserviceObject)
{
	$moduleName = $webserviceObject->getEntityName();
	return CRMEntity::getInstance($moduleName);
}

function vtws_getOwnerType($ownerId)
{
	return \App\Fields\Owner::getType($ownerId);
}

function vtws_runQueryAsTransaction($query, $params, &$result)
{
	$adb = PearDatabase::getInstance();

	$adb->startTransaction();
	$result = $adb->pquery($query, $params);
	$error = $adb->hasFailedTransaction();
	$adb->completeTransaction();
	return !$error;
}

function vtws_getCalendarEntityType($id)
{
	$seType = Vtiger_Cache::get('vtws_getCalendarEntityType', $id);
	if ($seType !== false) {
		return $seType;
	}
	$adb = PearDatabase::getInstance();

	$sql = 'select activitytype from vtiger_activity where activityid=?';
	$result = $adb->pquery($sql, array($id));
	$seType = 'Calendar';
	if ($result != null && isset($result)) {
		if ($adb->num_rows($result) > 0) {
			$activityType = $adb->query_result($result, 0, 'activitytype');
			if ($activityType !== 'Task') {
				$seType = 'Events';
			}
		}
	}
	Vtiger_Cache::set('vtws_getCalendarEntityType', $id, $seType);
	return $seType;
}
/* * *
 * Get the webservice reference Id given the entity's id and it's type name
 */

function vtws_getWebserviceEntityId($entityName, $id)
{
	$adb = PearDatabase::getInstance();
	$webserviceObject = VtigerWebserviceObject::fromName($adb, $entityName);
	return $webserviceObject->getEntityId() . 'x' . $id;
}

function vtws_addDefaultModuleTypeEntity($moduleName)
{
	$moduleHandler = array('file' => 'include/Webservices/VtigerModuleOperation.php', 'class' => 'VtigerModuleOperation');
	return vtws_addModuleTypeWebserviceEntity($moduleName, $moduleHandler['file'], $moduleHandler['class'], 1);
}

function vtws_addModuleTypeWebserviceEntity($moduleName, $filePath, $className, $isModule = 1)
{
	$isExists = (new \App\Db\Query())->from('vtiger_ws_entity')->where(['name' => $moduleName, 'handler_class' => $className, 'handler_path' => $filePath])->exists();
	if (!$isExists) {
		\App\Db::getInstance()->createCommand()
			->insert('vtiger_ws_entity', [
				'name' => $moduleName,
				'handler_path' => $filePath,
				'handler_class' => $className,
				'ismodule' => $isModule,
			])->execute();
	}
}

function vtws_deleteWebserviceEntity($moduleName)
{
	\App\Db::getInstance()->createCommand()
		->delete('vtiger_ws_entity', ['name' => $moduleName])->execute();
}

function vtws_addDefaultActorTypeEntity($actorName, $actorNameDetails, $withName = true)
{
	$actorHandler = array('file' => 'include/Webservices/VtigerActorOperation.php',
		'class' => 'VtigerActorOperation');
	if ($withName === true) {
		vtws_addActorTypeWebserviceEntityWithName($actorName, $actorHandler['file'], $actorHandler['class'], $actorNameDetails);
	} else {
		vtws_addActorTypeWebserviceEntityWithoutName($actorName, $actorHandler['file'], $actorHandler['class'], $actorNameDetails);
	}
}

function vtws_addActorTypeWebserviceEntityWithName($moduleName, $filePath, $className, $actorNameDetails)
{
	$db = \App\Db::getInstance();
	$db->createCommand()
		->insert('vtiger_ws_entity', [
			'name' => $moduleName,
			'handler_path' => $filePath,
			'handler_class' => $className,
			'ismodule' => 0,
		])->execute();
	vtws_addActorTypeName($db->getLastInsertID('vtiger_ws_entity_seq'), $actorNameDetails['fieldNames'], $actorNameDetails['indexField'], $actorNameDetails['tableName']);
}

function vtws_addActorTypeWebserviceEntityWithoutName($moduleName, $filePath, $className, $actorNameDetails)
{
	\App\Db::getInstance()->createCommand()
		->insert('vtiger_ws_entity', [
			'name' => $moduleName,
			'handler_path' => $filePath,
			'handler_class' => $className,
			'ismodule' => 0,
		])->execute();
}

function vtws_addActorTypeName($entityId, $fieldNames, $indexColumn, $tableName)
{
	\App\Db::getInstance()->createCommand()
		->insert('vtiger_ws_entity_name', [
			'entity_id' => $entityId,
			'name_fields' => $fieldNames,
			'index_field' => $indexColumn,
			'table_name' => $tableName,
		])->execute();
}

function vtws_getName($id, $user)
{
	$adb = PearDatabase::getInstance();


	$webserviceObject = VtigerWebserviceObject::fromId($adb, $id);
	$handlerPath = $webserviceObject->getHandlerPath();
	$handlerClass = $webserviceObject->getHandlerClass();

	require_once $handlerPath;

	$handler = new $handlerClass($webserviceObject, $user, $adb, $log);
	$meta = $handler->getMeta();
	return $meta->getName($id);
}

function vtws_preserveGlobal($name, $value)
{
	return VTWS_PreserveGlobal::preserveGlobal($name, $value);
}

/**
 * Takes the details of a webservices and exposes it over http.
 * @param $name name of the webservice to be added with namespace.
 * @param $handlerFilePath file to be include which provides the handler method for the given webservice.
 * @param $handlerMethodName name of the function to the called when this webservice is invoked.
 * @param $requestType type of request that this operation should be, if in doubt give it as GET,
 * 	general rule of thumb is that, if the operation is adding/updating data on server then it must be POST
 * 	otherwise it should be GET.
 * @param $preLogin 0 if the operation need the user to authorised to access the webservice and
 * 	1 if the operation is called before login operation hence the there will be no user authorisation happening
 * 	for the operation.
 * @return Integer operationId of successful or null upon failure.
 */
function vtws_addWebserviceOperation($name, $handlerFilePath, $handlerMethodName, $requestType, $preLogin = 0)
{
	$adb = PearDatabase::getInstance();
	$createOperationQuery = "insert into vtiger_ws_operation(operationid,name,handler_path,handler_method,type,prelogin)
		values (?,?,?,?,?,?);";
	if (strtolower($requestType) != 'get' && strtolower($requestType) != 'post') {
		return null;
	}
	$requestType = strtoupper($requestType);
	if (empty($preLogin)) {
		$preLogin = 0;
	} else {
		$preLogin = 1;
	}
	$operationId = $adb->getUniqueID("vtiger_ws_operation");
	$result = $adb->pquery($createOperationQuery, array($operationId, $name, $handlerFilePath, $handlerMethodName,
		$requestType, $preLogin));
	if ($result !== false) {
		return $operationId;
	}
	return null;
}

/**
 * Add a parameter to a webservice.
 * @param $operationId Id of the operation for which a webservice needs to be added.
 * @param $paramName name of the parameter used to pickup value from request(POST/GET) object.
 * @param $paramType type of the parameter, it can either 'string','datetime' or 'encoded'
 * 	encoded type is used for input which will be encoded in JSON or XML(NOT SUPPORTED).
 * @param $sequence sequence of the parameter in the definition in the handler method.
 * @return Boolean true if the parameter was added successfully, false otherwise
 */
function vtws_addWebserviceOperationParam($operationId, $paramName, $paramType, $sequence)
{
	$adb = PearDatabase::getInstance();
	$supportedTypes = array('string', 'encoded', 'datetime', 'double', 'boolean');
	if (!is_numeric($sequence)) {
		$sequence = 1;
	}if ($sequence <= 1) {
		$sequence = 1;
	}
	if (!in_array(strtolower($paramType), $supportedTypes)) {
		return false;
	}
	$createOperationParamsQuery = "insert into vtiger_ws_operation_parameters(operationid,name,type,sequence)
		values (?,?,?,?);";
	$result = $adb->pquery($createOperationParamsQuery, array($operationId, $paramName, $paramType, $sequence));
	return ($result !== false);
}

/**
 *
 * @global PearDatabase $adb
 * @global <type> $log
 * @param <type> $name
 * @param <type> $user
 * @return WebserviceEntityOperation
 */
function vtws_getModuleHandlerFromName($name, $user)
{
	$adb = PearDatabase::getInstance();

	$webserviceObject = VtigerWebserviceObject::fromName($adb, $name);
	$handlerPath = $webserviceObject->getHandlerPath();
	$handlerClass = $webserviceObject->getHandlerClass();

	require_once $handlerPath;

	$handler = new $handlerClass($webserviceObject, $user, $adb, $log);
	return $handler;
}

function vtws_getModuleHandlerFromId($id, $user)
{
	$adb = PearDatabase::getInstance();

	$webserviceObject = VtigerWebserviceObject::fromId($adb, $id);
	$handlerPath = $webserviceObject->getHandlerPath();
	$handlerClass = $webserviceObject->getHandlerClass();

	require_once $handlerPath;

	$handler = new $handlerClass($webserviceObject, $user, $adb, $log);
	return $handler;
}

function vtws_getActorEntityName($name, $idList)
{
	$db = PearDatabase::getInstance();
	if (!is_array($idList) && count($idList) == 0) {
		return [];
	}
	$entity = VtigerWebserviceObject::fromName($db, $name);
	return vtws_getActorEntityNameById($entity->getEntityId(), $idList);
}

function vtws_getActorEntityNameById($entityId, $idList)
{
	$db = PearDatabase::getInstance();
	if (!is_array($idList) && count($idList) == 0) {
		return [];
	}
	$nameList = [];
	$webserviceObject = VtigerWebserviceObject::fromId($db, $entityId);
	$query = "select * from vtiger_ws_entity_name where entity_id = ?";
	$result = $db->pquery($query, array($entityId));
	if (is_object($result)) {
		$rowCount = $db->num_rows($result);
		if ($rowCount > 0) {
			$nameFields = $db->query_result($result, 0, 'name_fields');
			$tableName = $db->query_result($result, 0, 'table_name');
			$indexField = $db->query_result($result, 0, 'index_field');
			if (!(strpos($nameFields, ',') === false)) {
				$fieldList = explode(',', $nameFields);
				$nameFields = "concat(";
				$nameFields = $nameFields . implode(",' ',", $fieldList);
				$nameFields = $nameFields . ")";
			}

			$query1 = "select $nameFields as entityname, $indexField from $tableName where " .
				"$indexField in (" . generateQuestionMarks($idList) . ")";
			$params1 = array($idList);
			$result = $db->pquery($query1, $params1);
			if (is_object($result)) {
				$rowCount = $db->num_rows($result);
				for ($i = 0; $i < $rowCount; $i++) {
					$id = $db->query_result($result, $i, $indexField);
					$nameList[$id] = $db->query_result($result, $i, 'entityname');
				}
				return $nameList;
			}
		}
	}
	return [];
}

function vtws_isRoleBasedPicklist($name)
{
	$db = PearDatabase::getInstance();
	$sql = "select picklistid from vtiger_picklist where name = ?";
	$result = $db->pquery($sql, array($name));
	return ($db->num_rows($result) > 0);
}

function vtws_getConvertLeadFieldMapping()
{
	$adb = PearDatabase::getInstance();
	$sql = "select * from vtiger_convertleadmapping";
	$result = $adb->pquery($sql, []);
	if ($result === false) {
		return null;
	}
	$mapping = [];
	$rowCount = $adb->num_rows($result);
	for ($i = 0; $i < $rowCount; ++$i) {
		$row = $adb->query_result_rowdata($result, $i);
		$mapping[$row['leadfid']] = array('Accounts' => $row['accountfid'], 'Contacts' => $row['contactfid']);
	}
	return $mapping;
}

/** 	Function used to get the lead related Notes and Attachments with other entities Account, Contact and Potential
 * 	@param integer $id - leadid
 * 	@param integer $relatedId -  related entity id (accountid / contactid)
 */
function vtws_getRelatedNotesAttachments($id, $relatedId)
{
	$adb = PearDatabase::getInstance();
	$db = \App\Db::getInstance();

	$sql = 'SELECT notesid FROM vtiger_senotesrel WHERE crmid=?';
	$result = $adb->pquery($sql, [$id]);
	if (!$result->rowCount()) {
		return false;
	}
	while ($noteId = $adb->getSingleValue($result)) {
		$db->createCommand()->insert('vtiger_senotesrel', ['crmid' => $relatedId, 'notesid' => $noteId])->execute();
	}

	$sql = 'SELECT attachmentsid FROM vtiger_seattachmentsrel WHERE crmid=?';
	$result = $adb->pquery($sql, [$id]);
	if (!$result->rowCount()) {
		return false;
	}
	while ($attachmentId = $adb->getSingleValue($result)) {
		$db->createCommand()->insert('vtiger_seattachmentsrel', ['crmid' => $relatedId, 'attachmentsid' => $attachmentId])->execute();
	}
	return true;
}

/** 	Function used to save the lead related products with other entities Account, Contact and Potential
 * 	$leadid - leadid
 * 	$relatedid - related entity id (accountid/contactid/potentialid)
 * 	$setype - related module(Accounts/Contacts)
 */
function vtws_saveLeadRelatedProducts($leadId, $relatedId, $setype)
{
	$db = \App\Db::getInstance();
	$dataReader = (new \App\Db\Query())->select(['productid'])
			->from('vtiger_seproductsrel')
			->where(['crmid' => $leadId])
			->createCommand()->query();
	if ($dataReader->count() === 0) {
		return false;
	}
	while ($productId = $dataReader->readColumn(0)) {
		$resultNew = $db->createCommand()->insert('vtiger_seproductsrel', [
				'crmid' => $relatedId,
				'productid' => $productId,
				'setype' => $setype,
				'rel_created_user' => \App\User::getCurrentUserId(),
				'rel_created_time' => date('Y-m-d H:i:s')
			])->execute();
		if ($resultNew === 0) {
			return false;
		}
	}
	return true;
}

/** 	Function used to save the lead related services with other entities Account, Contact and Potential
 * 	$leadid - leadid
 * 	$relatedid - related entity id (accountid/contactid/potentialid)
 * 	$setype - related module(Accounts/Contacts)
 */
function vtws_saveLeadRelations($leadId, $relatedId, $setype)
{
	$db = \App\Db::getInstance();
	$dataReader = (new App\Db\Query())->from('vtiger_crmentityrel')->where(['crmid' => $leadId])
			->createCommand()->query();
	if ($dataReader->count() === 0) {
		return false;
	}
	while ($row = $dataReader->read()) {
		$resultNew = $db->createCommand()->insert('vtiger_crmentityrel', [
				'crmid' => $relatedId,
				'module' => $setype,
				'relcrmid' => $row['relcrmid'],
				'relmodule' => $row['relmodule']
			])->execute();
		if ($resultNew === 0) {
			return false;
		}
	}
	$dataReader = (new App\Db\Query())->from('vtiger_crmentityrel')->where(['relcrmid' => $leadId])
			->createCommand()->query();
	if ($dataReader->count() === 0) {
		return false;
	}
	while ($row = $dataReader->read()) {
		$resultNew = $db->createCommand()->insert('vtiger_crmentityrel', [
				'crmid' => $relatedId,
				'module' => $setype,
				'relcrmid' => $row['crmid'],
				'relmodule' => $row['module']
			])->execute();
		if ($resultNew === 0) {
			return false;
		}
	}
	return true;
}

function vtws_getFieldfromFieldId($fieldId, $fieldObjectList)
{
	foreach ($fieldObjectList as $field) {
		if ($fieldId == $field->getFieldId()) {
			return $field;
		}
	}
	return null;
}

/** 	Function used to get the lead related activities with other entities Account and Contact
 * 	@param integer $leadId - lead entity id
 * 	@param integer $accountId - related account id
 * 	@param integer $contactId -  related contact id
 * 	@param integer $relatedId - related entity id to which the records need to be transferred
 */
function vtws_getRelatedActivities($leadId, $accountId, $contactId, $relatedId)
{

	if (empty($leadId) || empty($relatedId) || (empty($accountId) && empty($contactId))) {
		throw new WebServiceException(WebServiceErrorCode::$LEAD_RELATED_UPDATE_FAILED, "Failed to move related Activities/Emails");
	}
	$db = \App\Db::getInstance();
	if (!empty($accountId)) {
		$db->createCommand()->update('vtiger_activity', ['link' => $accountId], ['link' => $leadId])->execute();
	}
	if (!empty($contactId)) {
		$db->createCommand()->update('vtiger_activity', ['link' => $contactId], ['link' => $leadId])->execute();
	}
	return true;
}

/**
 * Function used to save the lead related Campaigns with Contact
 * @param $leadid - leadid
 * @param $relatedid - related entity id (contactid/accountid)
 * @param $setype - related module(Accounts/Contacts)
 * @return Boolean true on success, false otherwise.
 */
function vtws_saveLeadRelatedCampaigns($leadId, $relatedId, $seType)
{
	$db = \App\Db::getInstance();
	$rowCount = $db->createCommand()->update('vtiger_campaign_records', [
			'crmid' => $relatedId
			], ['crmid' => $leadId]
		)->execute();
	if ($rowCount == 0) {
		return false;
	}
	return true;
}

/**
 * Function used to transfer all the lead related records to given Entity(Contact/Account) record
 * @param $leadid - leadid
 * @param $relatedid - related entity id (contactid/accountid)
 * @param $setype - related module(Accounts/Contacts)
 */
function vtws_transferLeadRelatedRecords($leadId, $relatedId, $seType)
{

	if (empty($leadId) || empty($relatedId) || empty($seType)) {
		throw new WebServiceException(WebServiceErrorCode::$LEAD_RELATED_UPDATE_FAILED, "Failed to move related Records");
	}
	vtws_getRelatedNotesAttachments($leadId, $relatedId);
	vtws_saveLeadRelatedProducts($leadId, $relatedId, $seType);
	vtws_saveLeadRelations($leadId, $relatedId, $seType);
	vtws_saveLeadRelatedCampaigns($leadId, $relatedId, $seType);
	vtws_transferComments($leadId, $relatedId);
	vtws_transferRelatedRecords($leadId, $relatedId);
}

function vtws_transferComments($sourceRecordId, $destinationRecordId)
{
	if (\App\Module::isModuleActive('ModComments')) {
		CRMEntity::getInstance('ModComments');
		ModComments::transferRecords($sourceRecordId, $destinationRecordId);
	}
}

function vtws_transferRelatedRecords($sourceRecordId, $destinationRecordId)
{
	$db = \App\Db::getInstance();
	//PBXManager
	$db->createCommand()->update('vtiger_pbxmanager', ['customer' => $destinationRecordId], ['customer' => $sourceRecordId])->execute();
	//OSSPasswords
	$db->createCommand()->update('vtiger_osspasswords', ['linkto' => $destinationRecordId], ['linkto' => $sourceRecordId])->execute();
	//Contacts
	$db->createCommand()->update('vtiger_contactdetails', ['parentid' => $destinationRecordId], ['parentid' => $sourceRecordId])->execute();
	//OutsourcedProducts
	$db->createCommand()->update('vtiger_outsourcedproducts', ['parent_id' => $destinationRecordId], ['parent_id' => $sourceRecordId])->execute();
	//OSSOutsourcedServices
	$db->createCommand()->update('vtiger_ossoutsourcedservices', ['parent_id' => $destinationRecordId], ['parent_id' => $sourceRecordId])->execute();
	//OSSTimeControl
	$db->createCommand()->update('vtiger_osstimecontrol', ['link' => $destinationRecordId], ['link' => $sourceRecordId])->execute();
	//OSSMailView
	$db->createCommand()->update('vtiger_ossmailview_relation', ['crmid' => $destinationRecordId], ['crmid' => $sourceRecordId])->execute();
	//CallHistory
	$db->createCommand()->update('vtiger_callhistory', ['destination' => $destinationRecordId], ['destination' => $sourceRecordId])->execute();
	//LettersIn
	$db->createCommand()->update('vtiger_lettersin', ['relatedid' => $destinationRecordId], ['relatedid' => $sourceRecordId])->execute();
	//LettersOut
	$db->createCommand()->update('vtiger_lettersout', ['relatedid' => $destinationRecordId], ['relatedid' => $sourceRecordId])->execute();
}

function vtws_transferOwnership($ownerId, $newOwnerId, $delete = true)
{
	$db = \App\Db::getInstance();
	//Updating the smcreatorid,smownerid, modifiedby, smcreatorid in vtiger_crmentity
	$db->createCommand()->update('vtiger_crmentity', ['smcreatorid' => $newOwnerId], ['smcreatorid' => $ownerId, 'setype' => 'ModComments'])
		->execute();
	$db->createCommand()->update('vtiger_crmentity', ['smownerid' => $newOwnerId], ['smownerid' => $ownerId, 'setype' => 'ModComments'])
		->execute();
	$db->createCommand()->update('vtiger_crmentity', ['modifiedby' => $newOwnerId], ['modifiedby' => $ownerId])
		->execute();
	//deleting from vtiger_tracker
	if ($delete) {
		$db->createCommand()->delete('vtiger_tracker', ['user_id' => $ownerId])
			->execute();
	}
	//updating the vtiger_import_maps
	$db->createCommand()->update('vtiger_import_maps', ['assigned_user_id' => $newOwnerId], ['assigned_user_id' => $ownerId])
		->execute();
	//delete from vtiger_homestuff
	if ($delete) {
		$db->createCommand()->delete('vtiger_homestuff', ['userid' => $ownerId])
			->execute();
		$db->createCommand()->delete('vtiger_users2group', ['userid' => $ownerId])
			->execute();
	}
	$dataReader = (new App\Db\Query())->select(['tabid', 'fieldname', 'tablename', 'columnname'])
			->from('vtiger_field')
			->leftJoin('vtiger_fieldmodulerel', 'vtiger_field.fieldid = vtiger_fieldmodulerel.fieldid')
			->where(['or', ['uitype' => [52, 53, 77, 101]], ['uitype' => 10, 'relmodule' => 'Users']])
			->createCommand()->query();
	$columnList = [];
	while ($row = $dataReader->read()) {
		$column = $row['tablename'] . '.' . $row['columnname'];
		if (!in_array($column, $columnList)) {
			$columnList[] = $column;
			if ($row['columnname'] === 'smcreatorid' || $row['columnname'] === 'smownerid') {
				$db->createCommand()->update($row['tablename'], [$row['columnname'] => $newOwnerId], ['and', [$row['columnname'] => $ownerId], ['<>', 'setype', 'ModComments']])
					->execute();
			} else {
				$db->createCommand()->update($row['tablename'], [$row['columnname'] => $newOwnerId], [$row['columnname'] => $ownerId])
					->execute();
			}
		}
	}
	//update workflow tasks Assigned User from Deleted User to Transfer User
	$newOwnerModel = Users_Record_Model::getInstanceById($newOwnerId, 'Users');
	$ownerModel = Users_Record_Model::getInstanceById($ownerId, 'Users');
	vtws_transferOwnershipForWorkflowTasks($ownerModel, $newOwnerModel);
}

function vtws_transferOwnershipForWorkflowTasks($ownerModel, $newOwnerModel)
{
	$db = \App\Db::getInstance();
	//update workflow tasks Assigned User from Deleted User to Transfer User
	$newOwnerName = $newOwnerModel->get('user_name');
	if (!$newOwnerName) {
		$newOwnerName = $newOwnerModel->getName();
	}
	$newOwnerId = $newOwnerModel->getId();
	$ownerName = $ownerModel->get('user_name');
	if (!$ownerName) {
		$ownerName = $ownerModel->getName();
	}
	$ownerId = $ownerModel->getId();
	$nameSearchValue = '"fieldname":"assigned_user_id","value":"' . $ownerName . '"';
	$idSearchValue = '"fieldname":"assigned_user_id","value":"' . $ownerId . '"';
	$fieldSearchValue = 's:16:"assigned_user_id"';
	$dataReader = (new \App\Db\Query())->select(['task', 'task_id', 'workflow_id'])->from('com_vtiger_workflowtasks')
			->where(['or like', 'task', [$nameSearchValue, $idSearchValue, $fieldSearchValue]])
			->createCommand()->query();
	while ($row = $dataReader->read()) {
		$task = $row['task'];
		$taskComponents = explode(':', $task);
		$classNameWithDoubleQuotes = $taskComponents[2];
		$className = str_replace('"', '', $classNameWithDoubleQuotes);
		require_once("modules/com_vtiger_workflow/VTTaskManager.php");
		require_once 'modules/com_vtiger_workflow/tasks/' . $className . '.php';
		$unserializeTask = unserialize($task);
		if (array_key_exists('field_value_mapping', $unserializeTask)) {
			$fieldMapping = \App\Json::decode($unserializeTask->field_value_mapping);
			if (!empty($fieldMapping)) {
				foreach ($fieldMapping as $key => $condition) {
					if ($condition['fieldname'] == 'assigned_user_id') {
						$value = $condition['value'];
						if (is_numeric($value) && $value == $ownerId) {
							$condition['value'] = $newOwnerId;
						} else if ($value == $ownerName) {
							$condition['value'] = $newOwnerName;
						}
					}
					$fieldMapping[$key] = $condition;
				}
				$updatedTask = \App\Json::encode($fieldMapping);
				$unserializeTask->field_value_mapping = $updatedTask;
				$serializeTask = serialize($unserializeTask);
				$db->createCommand()->update('com_vtiger_workflowtasks', ['task' => $serializeTask], ['workflow_id' => $row['workflow_id'], 'task_id' => $row['task_id']])->execute();
			}
		} else {
			//For VTCreateTodoTask and VTCreateEventTask
			if (array_key_exists('assigned_user_id', $unserializeTask)) {
				$value = $unserializeTask->assigned_user_id;
				if ($value == $ownerId) {
					$unserializeTask->assigned_user_id = $newOwnerId;
				}
				$serializeTask = serialize($unserializeTask);
				$db->createCommand()->update('com_vtiger_workflowtasks', ['task' => $serializeTask], ['workflow_id' => $row['workflow_id'], 'task_id' => $row['task_id']])->execute();
			}
		}
	}
}

function vtws_getWebserviceTranslatedStringForLanguage($label, $currentLanguage)
{
	static $translations = [];
	$currentLanguage = vtws_getWebserviceCurrentLanguage();
	if (empty($translations[$currentLanguage])) {
		include 'languages/' . $currentLanguage . '/Webservices.php';
		$translations[$currentLanguage] = $languageStrings;
	}
	if (isset($translations[$currentLanguage][$label])) {
		return $translations[$currentLanguage][$label];
	}
	return null;
}

function vtws_getWebserviceTranslatedString($label)
{
	$currentLanguage = vtws_getWebserviceCurrentLanguage();
	$translation = vtws_getWebserviceTranslatedStringForLanguage($label, $currentLanguage);
	if (!empty($translation)) {
		return $translation;
	}

	//current language doesn't have translation, return translation in default language
	//if default language is english then LBL_ will not shown to the user.
	$defaultLanguage = vtws_getWebserviceDefaultLanguage();
	$translation = vtws_getWebserviceTranslatedStringForLanguage($label, $defaultLanguage);
	if (!empty($translation)) {
		return $translation;
	}

	//if default language is not en_us then do the translation in en_us to eliminate the LBL_ bit
	//of label.
	if ('en_us' != $defaultLanguage) {
		$translation = vtws_getWebserviceTranslatedStringForLanguage($label, 'en_us');
		if (!empty($translation)) {
			return $translation;
		}
	}
	return $label;
}

function vtws_getWebserviceCurrentLanguage()
{
	$lang = vglobal('current_language');
	if (empty($lang)) {
		$lang = vglobal('default_language');
	}
	return $lang;
}

function vtws_getWebserviceDefaultLanguage()
{
	$lang = vglobal('default_language');
	return $lang;
}
