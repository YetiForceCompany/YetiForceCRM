<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ******************************************************************************* */

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
		\App\Log::trace('Start ' . __METHOD__);
		if (empty($entityvalues['assignedTo'])) {
			$entityvalues['assignedTo'] = $user->id;
		}
		if (empty($entityvalues['transferRelatedRecordsTo'])) {
			$entityvalues['transferRelatedRecordsTo'] = 'Accounts';
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($entityvalues['leadId']);
		$leadInfo = $recordModel->getData();
		$leadIdComponents = $entityvalues['leadId'];
		if ((new \App\Db\Query())->select(['converted'])->from('vtiger_leaddetails')->where(['converted' => 1, 'leadid' => $leadIdComponents])->exists()) {
			$translateAlreadyConvertedError = \App\Language::translate('LBL_LEAD_ALREADY_CONVERTED', 'Leads');
			\App\Log::error('Error converting a lead: ' . $translateAlreadyConvertedError);
			throw new WebServiceException('LEAD_ALREADY_CONVERTED', $translateAlreadyConvertedError);
		}

		$eventHandler = new App\EventHandler();
		$eventHandler->setParams(['entityValues' => $entityvalues, 'user' => $user, 'leadInfo' => $leadInfo]);
		$eventHandler->trigger('EntityBeforeConvertLead');

		$entityIds = [];
		$availableModules = ['Accounts', 'Contacts'];

		if (empty($entityvalues['entities']['Accounts']['create']) && empty($entityvalues['entities']['Contacts']['create'])) {
			return null;
		}

		foreach ($availableModules as $entityName) {
			if (!empty($entityvalues['entities'][$entityName]['create'])) {
				$entityvalue = $entityvalues['entities'][$entityName];

				$entityObjectValues = [];
				$entityObjectValues['assigned_user_id'] = $entityvalues['assignedTo'];
				$entityObjectValues = static::vtwsPopulateConvertLeadEntities($entityvalue, $entityObjectValues, $recordModel, $leadInfo);

				//update the contacts relation
				if ('Contacts' == $entityvalue['name'] && !empty($entityIds['Accounts'])) {
					$entityObjectValues['parent_id'] = $entityIds['Accounts'];
				}

				try {
					$create = true;
					if ('Accounts' == $entityvalue['name'] && !empty($entityvalue['convert_to_id']) && \is_int($entityvalue['convert_to_id'])) {
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
								if ('' !== $defaultValue) {
									$recordModel->set($fieldName, $defaultValue);
								}
							}
						}
						$recordModel->save();
						$entityIds[$entityName] = $recordModel->getId();
					}
				} catch (Exception $e) {
					\App\Log::error('Error converting a lead: ' . $e->getMessage());
					throw new WebServiceException('UNKNOWN_OPERATION', $e->getMessage() . ' : ' . $entityvalue['name']);
				}
			}
		}

		try {
			$accountId = $entityIds['Accounts'];
			$contactId = $entityIds['Contacts'] ?? null;
			static::vtwsConvertLeadTransferHandler($leadIdComponents, $entityIds, $entityvalues);

			$relatedId = $entityIds[$entityvalues['transferRelatedRecordsTo']];
			\WebservicesUtils::vtwsGetRelatedActivities($leadIdComponents, $accountId, $contactId, $relatedId);
			static::vtwsUpdateConvertLeadStatus($entityIds, $entityvalues['leadId'], $user);

			$eventHandler->addParams('entityIds', $entityIds);
			$eventHandler->trigger('EntityAfterConvertLead');
		} catch (Exception $e) {
			\App\Log::error('Error converting a lead: ' . $e->getMessage());
			foreach ($entityIds as $id) {
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
		$entityName = $entityvalue['name'];
		$dataReader = (new \App\Db\Query())->from('vtiger_convertleadmapping')->createCommand()->query();
		if ($dataReader->count()) {
			switch ($entityName) {
				case 'Accounts':
					$column = 'accountfid';
					break;
				case 'Contacts':
					$column = 'contactfid';
					break;
				default:
					$column = 'leadfid';
					break;
			}
			$row = $dataReader->read();
			$count = 1;
			foreach ($targetModuleModel->getFields() as $fieldname => $field) {
				$defaultvalue = $field->getDefaultFieldValue();
				if ($defaultvalue && empty($entity[$fieldname])) {
					$entity[$fieldname] = $defaultvalue;
				}
			}
			do {
				$entityField = \WebservicesUtils::vtwsGetFieldfromFieldId($row[$column], $targetModuleModel);
				if (null === $entityField) {
					continue;
				}
				$leadField = \WebservicesUtils::vtwsGetFieldfromFieldId($row['leadfid'], $recordModel->getModule());
				if (null === $leadField) {
					continue;
				}
				$leadFieldName = $leadField->getFieldName();
				$entityFieldName = $entityField->getFieldName();
				$entity[$entityFieldName] = $leadinfo[$leadFieldName];
				++$count;
			} while ($row = $dataReader->read());
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
				if (('picklist' === $fieldModel->getFieldDataType() || 'multipicklist' === $fieldModel->getFieldDataType() || 'date' === $fieldModel->getFieldDataType() || 'datetime' === $fieldModel->getFieldDataType()) && $fieldModel->isEditable()) {
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
		$db = \App\Db::getInstance();
		if ('' != $entityIds['Accounts'] || '' != $entityIds['Contacts']) {
			\App\Cache::delete('Leads.converted', $leadId);
			$result = $db->createCommand()
				->update('vtiger_leaddetails', ['converted' => 1], ['leadid' => $leadId])
				->execute();
			if (false === $result) {
				throw new WebServiceException('FAILED_TO_MARK_LEAD_CONVERTED', 'Failed mark lead converted');
			}
			//update the modifiedtime and modified by information for the record
			$db->createCommand()
				->update('vtiger_crmentity', ['modifiedtime' => date('Y-m-d H:i:s'), 'modifiedby' => $user->getId()], ['crmid' => $leadId])
				->execute();
		}
		$moduleArray = ['Accounts', 'Contacts'];
		foreach ($moduleArray as $module) {
			if (!empty($entityIds[$module])) {
				$moduleModel = Vtiger_Module_Model::getInstance($module);
				$tablename = $moduleModel->getFieldByName('isconvertedfromlead')->getTableName();
				$db->createCommand()
					->update($tablename, ['isconvertedfromlead' => 1], [$moduleModel->getEntityInstance()->tab_name_index[$tablename] => $entityIds[$module]])
					->execute();
			}
		}
	}
}
