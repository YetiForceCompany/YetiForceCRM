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
require_once 'include/Loader.php';
require_once 'include/ConfigUtils.php';

class WebservicesConvertLead
{
	/**
	 * The function convert the lead.
	 *
	 * @param string             $entityvalues
	 * @param Users_Record_Model $user
	 *
	 * @return int
	 */
	public static function vtwsConvertlead($entityvalues, Users_Record_Model $user)
	{
		$adb = PearDatabase::getInstance();

		\App\Log::trace('Start ' . __METHOD__);
		if (empty($entityvalues['assignedTo'])) {
			$entityvalues['assignedTo'] = $user->id;
		}
		if (empty($entityvalues['transferRelatedRecordsTo'])) {
			$entityvalues['transferRelatedRecordsTo'] = 'Accounts';
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($entityvalues['leadId']);
		$leadInfo = $recordModel->getData();
		$sql = 'select converted from vtiger_leaddetails where converted = 1 and leadid=?';
		$leadIdComponents = $entityvalues['leadId'];
		$result = $adb->pquery($sql, [$leadIdComponents]);
		if ($result === false) {
			$translateDatabaseError = \App\Language::translate('LBL_' . WebServiceErrorCode::$DATABASEQUERYERROR, 'Webservices');
			\App\Log::error('Error converting a lead: ' . $translateDatabaseError);
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, $translateDatabaseError);
		}
		$rowCount = $adb->numRows($result);
		if ($rowCount > 0) {
			$translateAlreadyConvertedError = \App\Language::translate('LBL_' . WebServiceErrorCode::$LEAD_ALREADY_CONVERTED, 'Leads');
			\App\Log::error('Error converting a lead: ' . $translateAlreadyConvertedError);
			throw new WebServiceException(WebServiceErrorCode::$LEAD_ALREADY_CONVERTED, $translateAlreadyConvertedError);
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

				$entityObjectValues = [];
				$entityObjectValues['assigned_user_id'] = $entityvalues['assignedTo'];
				$entityObjectValues = static::vtwsPopulateConvertLeadEntities($entityvalue, $entityObjectValues, $recordModel, $leadInfo);

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

			static::vtwsConvertLeadTransferHandler($leadIdComponents, $entityIds, $entityvalues);

			$relatedId = $entityIds[$entityvalues['transferRelatedRecordsTo']];
			\WebservicesUtils::vtwsGetRelatedActivities($leadIdComponents, $accountId, $contactId, $relatedId);
			static::vtwsUpdateConvertLeadStatus($entityIds, $entityvalues['leadId'], $user);

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

	/**
	 * Populate the entity fields with the lead info.
	 * If mandatory field is not provided populate with '????'.
	 *
	 * @param array               $entityvalue
	 * @param string              $entity
	 * @param Vtiger_Record_Model $recordModel
	 * @param string              $leadinfo
	 *
	 * @return entity array
	 */
	public static function vtwsPopulateConvertLeadEntities($entityvalue, $entity, Vtiger_Record_Model $recordModel, $leadinfo)
	{
		$targetModuleModel = Vtiger_Module_Model::getInstance($entityvalue['name']);
		$adb = PearDatabase::getInstance();
		$entityName = $entityvalue['name'];
		$sql = 'SELECT * FROM vtiger_convertleadmapping';
		$result = $adb->pquery($sql, []);
		if ($adb->numRows($result)) {
			switch ($entityName) {
				case 'Accounts':$column = 'accountfid';
					break;
				case 'Contacts':$column = 'contactfid';
					break;
				default:$column = 'leadfid';
					break;
			}
			$row = $adb->fetchArray($result);
			$count = 1;
			foreach ($targetModuleModel->getFields() as $fieldname => $field) {
				$defaultvalue = $field->getDefaultFieldValue();
				if ($defaultvalue && $entity[$fieldname] == '') {
					$entity[$fieldname] = $defaultvalue;
				}
			}
			do {
				$entityField = \WebservicesUtils::vtwsGetFieldfromFieldId($row[$column], $targetModuleModel);
				if ($entityField === null) {
					continue;
				}
				$leadField = \WebservicesUtils::vtwsGetFieldfromFieldId($row['leadfid'], $recordModel->getModule());
				if ($leadField === null) {
					continue;
				}
				$leadFieldName = $leadField->getFieldName();
				$entityFieldName = $entityField->getFieldName();
				$entity[$entityFieldName] = $leadinfo[$leadFieldName];
				++$count;
			} while ($row = $adb->fetchArray($result));

			foreach ($entityvalue as $fieldname => $fieldvalue) {
				if (!empty($fieldvalue)) {
					$entity[$fieldname] = $fieldvalue;
				}
			}

			$entity = static::vtwsValidateConvertLeadEntityMandatoryValues($entity, $targetModuleModel);
		}

		return $entity;
	}

	/**
	 * Validate convert lead entity mandatory values.
	 *
	 * @param string              $entity
	 * @param Vtiger_Module_Model $targetModuleModel
	 *
	 * @return string
	 */
	public static function vtwsValidateConvertLeadEntityMandatoryValues($entity, Vtiger_Module_Model $targetModuleModel)
	{
		$mandatoryFields = $targetModuleModel->getMandatoryFieldModels();
		foreach ($mandatoryFields as $field => $fieldModel) {
			if (empty($entity[$field])) {
				if (($fieldModel->getFieldDataType() === 'picklist' || $fieldModel->getFieldDataType() === 'multipicklist' || $fieldModel->getFieldDataType() === 'date' || $fieldModel->getFieldDataType() === 'datetime') && $fieldModel->isEditable()) {
					$entity[$field] = $fieldModel->getDefaultFieldValue();
				} else {
					$entity[$field] = '????';
				}
			}
		}

		return $entity;
	}

	/**
	 * function to handle the transferring of related records for lead.
	 *
	 * @param int    $leadIdComponents
	 * @param int    $entityIds
	 * @param string $entityvalues
	 *
	 * @return bool
	 */
	public static function vtwsConvertLeadTransferHandler($leadIdComponents, $entityIds, $entityvalues)
	{
		try {
			$entityidComponents = $entityIds[$entityvalues['transferRelatedRecordsTo']];
			\WebservicesUtils::vtwsTransferLeadRelatedRecords($leadIdComponents, $entityidComponents, $entityvalues['transferRelatedRecordsTo']);
		} catch (Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * The function updates the status convert lead.
	 *
	 * @param int                $entityIds
	 * @param int                $leadId
	 * @param Users_Record_Model $user
	 */
	public static function vtwsUpdateConvertLeadStatus($entityIds, $leadId, Users_Record_Model $user)
	{
		$adb = PearDatabase::getInstance();

		if ($entityIds['Accounts'] != '' || $entityIds['Contacts'] != '') {
			$sql = 'UPDATE vtiger_leaddetails SET converted = 1 where leadid=?';
			$result = $adb->pquery($sql, [$leadId]);
			if ($result === false) {
				throw new WebServiceException(WebServiceErrorCode::$FAILED_TO_MARK_CONVERTED, 'Failed mark lead converted');
			}
			//update the modifiedtime and modified by information for the record
			$leadModifiedTime = $adb->formatDate(date('Y-m-d H:i:s'), true);
			$crmentityUpdateSql = 'UPDATE vtiger_crmentity SET modifiedtime=?, modifiedby=? WHERE crmid=?';
			$adb->pquery($crmentityUpdateSql, [$leadModifiedTime, $user->id, $leadId]);
		}
		$moduleArray = ['Accounts', 'Contacts'];

		foreach ($moduleArray as $module) {
			if (!empty($entityIds[$module])) {
				$id = $entityIds[$module];
				$moduleModel = Vtiger_Module_Model::getInstance($module);
				$field = $moduleModel->getFieldByName('isconvertedfromlead');
				$tablename = $field->getTableName();
				$entity = $moduleModel->getEntityInstance();
				$tableIndex = $entity->tab_name_index[$tablename];
				$adb->pquery("UPDATE $tablename SET isconvertedfromlead = ? WHERE $tableIndex = ?", [1, $id]);
			}
		}
	}
}
