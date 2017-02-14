<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ******************************************************************************* */
require_once 'config/config.php';
require_once 'config/debug.php';
require_once 'config/performance.php';
require_once 'include/Webservices/Delete.php';
require_once 'include/Webservices/DescribeObject.php';
require_once 'include/Loader.php';
require_once('include/ConfigUtils.php');
vimport('include.runtime.Globals');
vimport('include.runtime.BaseModel');

function vtws_convertlead($entityvalues, $user)
{
	$adb = PearDatabase::getInstance();

	\App\Log::trace('Start ' . __METHOD__);
	if (empty($entityvalues['assignedTo'])) {
		$entityvalues['assignedTo'] = $user->id;
	}
	if (empty($entityvalues['transferRelatedRecordsTo'])) {
		$entityvalues['transferRelatedRecordsTo'] = 'Accounts';
	}


	$leadObject = VtigerWebserviceObject::fromName($adb, 'Leads');
	$handlerPath = $leadObject->getHandlerPath();
	$handlerClass = $leadObject->getHandlerClass();

	require_once $handlerPath;

	$leadHandler = new $handlerClass($leadObject, $user, $adb, $log);


	$leadInfo = Vtiger_Record_Model::getInstanceById($entityvalues['leadId'])->getData();
	$sql = "select converted from vtiger_leaddetails where converted = 1 and leadid=?";
	$leadIdComponents = $entityvalues['leadId'];
	$result = $adb->pquery($sql, [$leadIdComponents]);
	if ($result === false) {
		\App\Log::error('Error converting a lead: ' . vtws_getWebserviceTranslatedString('LBL_' . WebServiceErrorCode::$DATABASEQUERYERROR));
		throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, vtws_getWebserviceTranslatedString('LBL_' .
			WebServiceErrorCode::$DATABASEQUERYERROR));
	}
	$rowCount = $adb->num_rows($result);
	if ($rowCount > 0) {
		\App\Log::error('Error converting a lead: ' . vtws_getWebserviceTranslatedString('LBL_' . WebServiceErrorCode::$LEAD_ALREADY_CONVERTED));
		throw new WebServiceException(WebServiceErrorCode::$LEAD_ALREADY_CONVERTED, vtws_getWebserviceTranslatedString('LBL_' . WebServiceErrorCode::$LEAD_ALREADY_CONVERTED));
	}

	$eventHandler = new App\EventHandler();
	$eventHandler->setParams(['entityValues' => $entityvalues, 'user' => $user, 'leadInfo' => $leadInfo]);
	$eventHandler->trigger('EntityBeforeConvertLead');

	$entityIds = [];
	$availableModules = ['Accounts', 'Contacts'];

	if (!(($entityvalues['entities']['Accounts']['create']) || ($entityvalues['entities']['Contacts']['create']))) {
		return null;
	}

	foreach ($availableModules as $entityName) {
		if ($entityvalues['entities'][$entityName]['create']) {
			$entityvalue = $entityvalues['entities'][$entityName];
			$entityObject = VtigerWebserviceObject::fromName($adb, $entityvalue['name']);
			$handlerPath = $entityObject->getHandlerPath();
			$handlerClass = $entityObject->getHandlerClass();

			require_once $handlerPath;

			$entityHandler = new $handlerClass($entityObject, $user, $adb, $log);

			$entityObjectValues = [];
			$entityObjectValues['assigned_user_id'] = $entityvalues['assignedTo'];
			$entityObjectValues = vtws_populateConvertLeadEntities($entityvalue, $entityObjectValues, $entityHandler, $leadHandler, $leadInfo);

			//update the contacts relation
			if ($entityvalue['name'] == 'Contacts') {
				if (!empty($entityIds['Accounts'])) {
					$entityObjectValues['parent_id'] = $entityIds['Accounts'];
				}
			}

			try {
				$create = true;
				if ($entityvalue['name'] == 'Accounts' && $entityvalue['convert_to_id'] && is_int($entityvalue['convert_to_id'])) {
					$entityIds[$entityName] = $entityvalue['convert_to_id'];
					$create = false;
				}
				if ($create) {
					$recordModel = Vtiger_Record_Model::getCleanInstance($entityvalue['name']);
					$fieldModelList = $recordModel->getModule()->getFields();
					foreach ($fieldModelList as $fieldName => &$fieldModel) {
						if (isset($entityObjectValues[$fieldName])) {
							$recordModel->set($fieldName, $entityObjectValues[$fieldName]);
						} else {
							$defaultValue = $fieldModel->getDefaultFieldValue();
							if ($defaultValue !== '') {
								$recordModel->set($fieldName, $defaultValue);
							}
						}
					}
					$recordModel->save();
					$entityIds[$entityName] = $recordModel->getId();
				}
			} catch (Exception $e) {
				\App\Log::error('Error converting a lead: ' . $e->getMessage());
				throw new WebServiceException(WebServiceErrorCode::$UNKNOWNOPERATION, $e->getMessage() . ' : ' . $entityvalue['name']);
			}
		}
	}

	try {
		$accountId = $entityIds['Accounts'];
		$contactId = $entityIds['Contacts'];

		$transfered = vtws_convertLeadTransferHandler($leadIdComponents, $entityIds, $entityvalues);

		$relatedId = $entityIds[$entityvalues['transferRelatedRecordsTo']];
		vtws_getRelatedActivities($leadIdComponents, $accountId, $contactId, $relatedId);
		vtws_updateConvertLeadStatus($entityIds, $entityvalues['leadId'], $user);

		$eventHandler->addParams('entityIds', $entityIds);
		$eventHandler->trigger('EntityAfterConvertLead');
	} catch (Exception $e) {
		\App\Log::error('Error converting a lead: ' . $e->getMessage());
		foreach ($entityIds as $entity => $id) {
			vtws_delete($id, $user);
		}
		return null;
	}
	\App\Log::trace('End ' . __METHOD__);
	return $entityIds;
}
/*
 * populate the entity fields with the lead info.
 * if mandatory field is not provided populate with '????'
 * returns the entity array.
 */

function vtws_populateConvertLeadEntities($entityvalue, $entity, $entityHandler, $leadHandler, $leadinfo)
{
	$adb = PearDatabase::getInstance();

	$column;
	$entityName = $entityvalue['name'];
	$sql = "SELECT * FROM vtiger_convertleadmapping";
	$result = $adb->pquery($sql, []);
	if ($adb->num_rows($result)) {
		switch ($entityName) {
			case 'Accounts':$column = 'accountfid';
				break;
			case 'Contacts':$column = 'contactfid';
				break;
			default:$column = 'leadfid';
				break;
		}

		$leadFields = $leadHandler->getMeta()->getModuleFields();
		$entityFields = $entityHandler->getMeta()->getModuleFields();
		$row = $adb->fetch_array($result);
		$count = 1;
		foreach ($entityFields as $fieldname => $field) {
			$defaultvalue = $field->getDefault();
			if ($defaultvalue && $entity[$fieldname] == '') {
				$entity[$fieldname] = $defaultvalue;
			}
		}
		do {
			$entityField = vtws_getFieldfromFieldId($row[$column], $entityFields);
			if ($entityField === null) {
				continue;
			}
			$leadField = vtws_getFieldfromFieldId($row['leadfid'], $leadFields);
			if ($leadField === null) {
				continue;
			}
			$leadFieldName = $leadField->getFieldName();
			$entityFieldName = $entityField->getFieldName();
			$entity[$entityFieldName] = $leadinfo[$leadFieldName];
			$count++;
		} while ($row = $adb->fetch_array($result));

		foreach ($entityvalue as $fieldname => $fieldvalue) {
			if (!empty($fieldvalue)) {
				$entity[$fieldname] = $fieldvalue;
			}
		}

		$entity = vtws_validateConvertLeadEntityMandatoryValues($entity, $entityHandler, $leadinfo, $entityName);
	}
	return $entity;
}

function vtws_validateConvertLeadEntityMandatoryValues($entity, $entityHandler, $leadinfo, $module)
{

	$mandatoryFields = $entityHandler->getMeta()->getMandatoryFields();
	foreach ($mandatoryFields as $field) {
		if (empty($entity[$field])) {
			$fieldInfo = vtws_getConvertLeadFieldInfo($module, $field);
			if (($fieldInfo['type']['name'] == 'picklist' || $fieldInfo['type']['name'] == 'multipicklist' || $fieldInfo['type']['name'] == 'date' || $fieldInfo['type']['name'] == 'datetime') && ($fieldInfo['editable'] === true)) {
				$entity[$field] = $fieldInfo['default'];
			} else {
				$entity[$field] = '????';
			}
		}
	}
	return $entity;
}

function vtws_getConvertLeadFieldInfo($module, $fieldname)
{
	$adb = PearDatabase::getInstance();

	$describe = vtws_describe($module, vglobal('current_user'));
	foreach ($describe['fields'] as $index => $fieldInfo) {
		if ($fieldInfo['name'] == $fieldname) {
			return $fieldInfo;
		}
	}
	return false;
}

//function to handle the transferring of related records for lead
function vtws_convertLeadTransferHandler($leadIdComponents, $entityIds, $entityvalues)
{
	try {
		$entityidComponents = $entityIds[$entityvalues['transferRelatedRecordsTo']];
		vtws_transferLeadRelatedRecords($leadIdComponents, $entityidComponents, $entityvalues['transferRelatedRecordsTo']);
	} catch (Exception $e) {
		return false;
	}
	return true;
}

function vtws_updateConvertLeadStatus($entityIds, $leadId, $user)
{
	$adb = PearDatabase::getInstance();

	if ($entityIds['Accounts'] != '' || $entityIds['Contacts'] != '') {
		$sql = "UPDATE vtiger_leaddetails SET converted = 1 where leadid=?";
		$result = $adb->pquery($sql, [$leadId]);
		if ($result === false) {
			throw new WebServiceException(WebServiceErrorCode::$FAILED_TO_MARK_CONVERTED, "Failed mark lead converted");
		}

		$sql = "DELETE FROM vtiger_tracker WHERE item_id=?";
		$adb->pquery($sql, [$leadId]);

		//update the modifiedtime and modified by information for the record
		$leadModifiedTime = $adb->formatDate(date('Y-m-d H:i:s'), true);
		$crmentityUpdateSql = "UPDATE vtiger_crmentity SET modifiedtime=?, modifiedby=? WHERE crmid=?";
		$adb->pquery($crmentityUpdateSql, array($leadModifiedTime, $user->id, $leadId));
	}
	$moduleArray = array('Accounts', 'Contacts');

	foreach ($moduleArray as $module) {
		if (!empty($entityIds[$module])) {
			$id = $entityIds[$module];
			$webserviceModule = vtws_getModuleHandlerFromName($module, $user);
			$meta = $webserviceModule->getMeta();
			$fields = $meta->getModuleFields();
			$field = $fields['isconvertedfromlead'];
			$tablename = $field->getTableName();
			$tableList = $meta->getEntityTableIndexList();
			$tableIndex = $tableList[$tablename];
			$adb->pquery("UPDATE $tablename SET isconvertedfromlead = ? WHERE $tableIndex = ?", array(1, $id));
		}
	}
}
