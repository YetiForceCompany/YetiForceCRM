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

class Vtiger_SaveAjax_Action extends Vtiger_Save_Action
{

	public function process(Vtiger_Request $request)
	{
		$recordModel = $this->saveRecord($request);

		$fieldModelList = $recordModel->getModule()->getFields();
		$result = [];
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			$recordFieldValue = $recordModel->get($fieldName);
			if (is_array($recordFieldValue) && $fieldModel->getFieldDataType() == 'multipicklist') {
				$recordFieldValue = implode(' |##| ', $recordFieldValue);
			} elseif (is_array($recordFieldValue) && $fieldModel->getFieldDataType() == 'sharedOwner') {
				$recordFieldValue = implode(',', $recordFieldValue);
			} elseif (is_array($recordFieldValue) && $fieldModel->getFieldDataType() == 'taxes') {
				$recordFieldValue = implode(',', $recordFieldValue);
			}
			$fieldValue = $displayValue = Vtiger_Util_Helper::toSafeHTML($recordFieldValue);
			if ($fieldModel->getFieldDataType() == 'currency') {
				$recordFieldValue = $fieldModel->getDBInsertValue($recordFieldValue);
				$displayValue = Vtiger_Util_Helper::toSafeHTML($fieldModel->getDisplayValue($recordFieldValue, $recordModel->getId()));
			} elseif ($fieldModel->getFieldDataType() !== 'datetime' && $fieldModel->getFieldDataType() !== 'date') {
				$displayValue = $fieldModel->getDisplayValue($fieldValue, $recordModel->getId(), $recordModel);
			}

			$result[$fieldName] = array('value' => $fieldValue, 'display_value' => $displayValue);
		}

		//Handling salutation type
		if ($request->get('field') === 'firstname' && in_array($request->getModule(), array('Contacts'))) {
			$salutationType = $recordModel->getDisplayValue('salutationtype');
			$firstNameDetails = $result['firstname'];
			$firstNameDetails['display_value'] = $salutationType . " " . $firstNameDetails['display_value'];
			if ($salutationType != '--None--')
				$result['firstname'] = $firstNameDetails;
		}

		$result['_recordLabel'] = $recordModel->getName();
		$result['_recordId'] = $recordModel->getId();
		$recordModel->clearPrivilegesCache();
		$result['isEditable'] = $recordModel->isEditable();

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	public function getRecordModelFromRequest(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		if (!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$recordModel->set('id', $recordId);
			$recordModel->set('mode', 'edit');

			$fieldModelList = $recordModel->getModule()->getFields();
			foreach ($fieldModelList as $fieldName => $fieldModel) {
				//For not converting craetedtime and modified time to user format
				$uiType = $fieldModel->get('uitype');
				if ($uiType == 70) {
					$fieldValue = $recordModel->get($fieldName);
				} elseif (in_array($uiType, [71, 72])) { // currency ui types
					$fieldValue = $recordModel->get($fieldName);
					$fieldValue = CurrencyField::convertToUserFormat($fieldValue, null, true);
				} else {
					$fieldValue = $fieldModel->getUITypeModel()->getUserRequestValue($recordModel->get($fieldName), $recordId);
				}

				if ($fieldName === $request->get('field')) {
					$fieldValue = $request->get('value');
				}
				$fieldDataType = $fieldModel->getFieldDataType();
				if ($fieldDataType == 'time') {
					$fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
				}
				if ($fieldValue !== null) {
					if (!is_array($fieldValue)) {
						$fieldValue = trim($fieldValue);
					}
					$recordModel->set($fieldName, $fieldValue);
				}
				$recordModel->set($fieldName, $fieldValue);
			}
		} else {
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$recordModel->set('mode', '');

			$fieldModelList = $moduleModel->getFields();
			foreach ($fieldModelList as $fieldName => $fieldModel) {
				if ($request->has($fieldName) && $fieldModel->get('uitype') == 300) {
					$fieldValue = $request->getForHtml($fieldName, null);
				} else if ($request->has($fieldName)) {
					$fieldValue = $request->get($fieldName, null);
				} else {
					$fieldValue = $fieldModel->getDefaultFieldValue();
				}
				$fieldDataType = $fieldModel->getFieldDataType();
				if ($fieldDataType == 'time') {
					$fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
				}
				if ($fieldValue !== null) {
					if (!is_array($fieldValue)) {
						$fieldValue = trim($fieldValue);
					}
					$recordModel->set($fieldName, $fieldValue);
				}
			}
		}

		return $recordModel;
	}
}
