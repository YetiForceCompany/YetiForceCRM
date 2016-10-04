<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ********************************************************************************** */
require_once('modules/com_vtiger_workflow/VTEntityCache.inc');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');

class VTUpdateFieldsTask extends VTTask
{

	public $executeImmediately = true;

	public function getFieldNames()
	{
		return array('field_value_mapping');
	}

	public function doTask($entity)
	{
		global $adb, $current_user, $default_charset;

		$util = new VTWorkflowUtils();
		$util->adminUser();

		$moduleName = $entity->getModuleName();
		$entityId = $entity->getId();
		$recordId = vtws_getIdComponents($entityId);
		$recordId = $recordId[1];

		$moduleHandler = vtws_getModuleHandlerFromName($moduleName, $current_user);
		$handlerMeta = $moduleHandler->getMeta();
		$moduleFields = $handlerMeta->getModuleFields();

		$fieldValueMapping = array();
		if (!empty($this->field_value_mapping)) {
			$fieldValueMapping = \includes\utils\Json::decode($this->field_value_mapping);
		}

		if (!empty($fieldValueMapping) && count($fieldValueMapping) > 0) {
			require_once('include/CRMEntity.php');
			$focus = CRMEntity::getInstance($moduleName);
			$focus->id = $recordId;
			$focus->mode = 'edit';
			$focus->retrieve_entity_info($recordId, $moduleName);
			$focus->clearSingletonSaveFields();

			$util->loggedInUser();
			$focus->column_fields = DataTransform::sanitizeDateFieldsForInsert($focus->column_fields, $handlerMeta);
			$focus->column_fields = DataTransform::sanitizeCurrencyFieldsForInsert($focus->column_fields, $handlerMeta);
			foreach ($fieldValueMapping as $fieldInfo) {
				$fieldName = $fieldInfo['fieldname'];
				$fieldValueType = $fieldInfo['valuetype'];
				$fieldValue = trim($fieldInfo['value']);

				$fieldInstance = $moduleFields[$fieldName];

				if ($fieldValueType == 'fieldname') {
					$fieldDataType = $fieldInstance->getFieldDataType();
					$rightOperandFieldInstance = $moduleFields[$fieldValue];
					if ($rightOperandFieldInstance) {
						$rightOperandFieldType = $rightOperandFieldInstance->getFieldDataType();
					}
					// while getting the focus currency fields are converted to user format
					$currencyfieldValueInDB = $focus->column_fields[$fieldValue . "_raw"];
					$currencyfieldConvertedValue = $focus->column_fields[$fieldValue . "_raw_converted"];
					$fieldValue = $focus->column_fields[$fieldValue];
					$fieldValueInDB = $fieldValue;

					// for currency field value should be in database format
					if (!empty($currencyfieldValueInDB) && $fieldDataType == "currency" && $fieldInstance->getUIType() != 72) {
						$fieldValueInDB = $currencyfieldValueInDB;
						if (!empty($currencyfieldConvertedValue)) {
							$fieldValue = $currencyfieldConvertedValue;
						}
					}
					if ($fieldDataType == 'date') {
						//Convert the DB Date Time Format to User Date Time Format
						$dateTime = new DateTimeField($fieldValue);
						$fieldValue = $dateTime->getDisplayDateTimeValue();

						$date = explode(' ', $fieldValue);
						$fieldValue = $date[0];
					}
					//for Product Unit Price value converted with based product currency
					if ($fieldDataType == 'currency' && $fieldName == 'unit_price') {
						$fieldValue = $this->calculateProductUnitPrice($fieldValue);
					}
					// for calendar time_start field db value will be in UTC format, we should convert to user format
					if (trim($fieldInfo['value']) == 'time_start' && $moduleName == 'Calendar' && $fieldDataType == 'time') {
						$date = new DateTime();
						$dateTime = new DateTimeField($date->format('Y-m-d') . ' ' . $fieldValue);
						$fieldValue = $dateTime->getDisplayTime();
					}
					if ($rightOperandFieldType == 'reference') {
						if (!empty($fieldValue)) {
							if (!empty($rightOperandFieldInstance)) {
								$referenceList = $rightOperandFieldInstance->getReferenceList();
								if ((count($referenceList) == 1) && $referenceList[0] == 'Users') {
									$fieldValue = \includes\fields\Owner::getLabel($fieldValue);
								} elseif ((count($referenceList) == 1) && $referenceList[0] == 'Currency') {
									$fieldValue = vtlib\Functions::getCurrencyName($fieldValue);
								} elseif ($rightOperandFieldInstance->getFieldName() == 'roleid') {
									$fieldValue = getRoleName($fieldValue);
								} else {
									$fieldValue = \includes\Record::getLabel($fieldValue);
								}
							} else {
								$fieldValue = \includes\Record::getLabel($fieldValue);
							}
						} else {
							//Not value is there for reference fields . So skip this field mapping
							continue;
						}
					}
					// End
				} elseif ($fieldValueType == 'expression') {
					require_once 'modules/com_vtiger_workflow/expression_engine/include.inc';

					$parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($fieldValue)));
					$expression = $parser->expression();
					$exprEvaluater = new VTFieldExpressionEvaluater($expression);
					$fieldValue = $exprEvaluater->evaluate($entity);
					$fieldValueInDB = $fieldValue;
					//for Product Unit Price value converted with based product currency
					if ($fieldInstance && $fieldInstance->getFieldDataType() == 'currency' && $fieldName == 'unit_price') {
						$fieldValue = $this->calculateProductUnitPrice($fieldValue);
					} else {
						$fieldValue = $this->convertValueToUserFormat($fieldInstance, $fieldValue);
					}
				} else {
					if (preg_match('/([^:]+):boolean$/', $fieldValue, $match)) {
						$fieldValue = $match[1];
						if ($fieldValue == 'true') {
							$fieldValue = '1';
						} else {
							$fieldValue = '0';
						}
					}
					//for Product Unit Price value converted with based product currency
					if ($fieldInstance && $fieldInstance->getFieldDataType() == 'currency' && $fieldName == 'unit_price') {
						$fieldValue = $this->calculateProductUnitPrice($fieldValue);
					}
					$fieldValueInDB = $fieldValue;
				}

				if ($fieldInstance && $fieldInstance->getFieldDataType() === 'owner') {
					if (!is_numeric($fieldValue)) {
						//If name is given
						$userId = getUserId_Ol($fieldValue);
						$groupId = getGrpId($fieldValue);

						if ($userId == 0 && $groupId == 0) {
							$fieldValue = $focus->column_fields[$fieldName];
						} else {
							$fieldValue = ($userId == 0) ? $groupId : $userId;
						}

						if ($userId == 0) {
							$webserviceObject = VtigerWebserviceObject::fromName($adb, 'Groups');
							$fieldValueInDB = vtws_getId($webserviceObject->getEntityId(), $fieldValue);
						} else {
							$webserviceObject = VtigerWebserviceObject::fromName($adb, 'Users');
							$fieldValueInDB = vtws_getId($webserviceObject->getEntityId(), $fieldValue);
						}
					} else {
						$ownerType = vtws_getOwnerType($fieldValue);
						$webserviceObject = VtigerWebserviceObject::fromName($adb, $ownerType);
						$fieldValueInDB = vtws_getId($webserviceObject->getEntityId(), $fieldValue);
					}
				}

				$focus->column_fields[$fieldName] = $fieldValue;
				$entity->data[$fieldName] = $fieldValueInDB;
			}

			foreach ($focus->column_fields as $fieldName => $fieldValue) {
				$focus->column_fields[$fieldName] = html_entity_decode($fieldValue, ENT_QUOTES, $default_charset);
			}
			AppRequest::set('file', '');
			AppRequest::set('ajxaction', '');

			// Added as Mass Edit triggers workflow and date and currency fields are set to user format
			// When saving the information in database saveentity API should convert to database format
			// and save it. But it converts in database format only if that date & currency fields are
			// changed(massedit) other wise they wont be converted thereby changing the values in user
			// format, CRMEntity.php line 474 has the login to check wheather to convert to database format
			$actionName = AppRequest::get('action');
			AppRequest::set('action', '');
			// For workflows update field tasks is deleted all the lineitems.
			$focus->isLineItemUpdate = false;

			$focus->saveentity($moduleName);

			// Reverting back the action name as there can be some dependencies on this.
			AppRequest::set('action', $actionName);
			$util->revertUser();
		}
		$util->revertUser();
	}

	//Function use to convert the field value in to current user format
	public function convertValueToUserFormat($fieldObj, $fieldValue)
	{
		$current_user = vglobal('current_user');
		if (!empty($fieldObj)) {
			// handle the case for Date field
			if ($fieldObj->getFieldDataType() == "date") {
				if (!empty($fieldValue)) {
					$dateFieldObj = new DateTimeField($fieldValue);
					$fieldValue = $dateFieldObj->getDisplayDate($current_user);
				}
			}

			// handle the case for currency field
			if ($fieldObj->getFieldDataType() == "currency" && !empty($fieldValue)) {
				if ($fieldObj->getUIType() == '71') {
					$fieldValue = CurrencyField::convertToUserFormat($fieldValue, $current_user, false);
				} else if ($fieldObj->getUIType() == '72') {
					$fieldValue = CurrencyField::convertToUserFormat($fieldValue, $current_user, true);
				}
			}
		}
		return $fieldValue;
	}

	/**
	 * Function to calculate Product Unit Price.
	 * Product Unit Price value converted with based product currency
	 * @param type $fieldValue
	 */
	public function calculateProductUnitPrice($fieldValue)
	{
		$currency_details = getAllCurrencies('all');
		for ($i = 0; $i < count($currency_details); $i++) {
			$curid = $currency_details[$i]['curid'];
			$cur_checkname = 'cur_' . $curid . '_check';
			$cur_valuename = 'curname' . $curid;
			if ($cur_valuename == AppRequest::get('base_currency') && (AppRequest::get($cur_checkname) == 'on' || AppRequest::get($cur_checkname) == 1)) {
				$fieldValue = $fieldValue * $currency_details[$i]['conversionrate'];
				AppRequest::set($cur_valuename, $fieldValue);
			}
		}
		return $fieldValue;
	}
}
