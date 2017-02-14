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
require_once('modules/com_vtiger_workflow/VTEntityCache.php');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');

class VTUpdateFieldsTask extends VTTask
{

	public $executeImmediately = true;

	public function getFieldNames()
	{
		return ['field_value_mapping'];
	}

	/**
	 * Execute task
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		$util = new VTWorkflowUtils();
		$util->adminUser();


		$moduleName = $recordModel->getModuleName();
		$moduleModel = $recordModel->getModule();
		$recordId = $recordModel->getId();
		$moduleFields = $moduleModel->getFields();
		$fieldValueMapping = [];
		if (!empty($this->field_value_mapping)) {
			$fieldValueMapping = \App\Json::decode($this->field_value_mapping);
		}
		if (!empty($fieldValueMapping) && count($fieldValueMapping) > 0) {
			$util->loggedInUser();
			foreach ($fieldValueMapping as $fieldInfo) {
				$fieldName = $fieldInfo['fieldname'];
				$fieldValueType = $fieldInfo['valuetype'];
				$fieldValue = trim($fieldInfo['value']);
				$fieldInstance = $moduleFields[$fieldName];
				if ($fieldValueType == 'expression') {
					require_once 'modules/com_vtiger_workflow/expression_engine/include.php';
					$parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($fieldValue)));
					$expression = $parser->expression();
					$exprEvaluater = new VTFieldExpressionEvaluater($expression);
					$fieldValue = $exprEvaluater->evaluate($recordModel);
					//for Product Unit Price value converted with based product currency
					if ($fieldInstance && $fieldInstance->getFieldDataType() == 'currency' && $fieldName == 'unit_price') {
						$fieldValue = $this->calculateProductUnitPrice($fieldValue);
					} else {
						$fieldValue = $this->convertValueToUserFormat($fieldInstance, $fieldValue);
					}
				} elseif ($fieldValueType !== 'fieldname') {
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
				}
				$recordModel->set($fieldName, decode_html($fieldValue));
			}
			// Added as Mass Edit triggers workflow and date and currency fields are set to user format
			// When saving the information in database saveentity API should convert to database format
			// and save it. But it converts in database format only if that date & currency fields are
			// changed(massedit) other wise they wont be converted thereby changing the values in user
			// format, CRMEntity.php line 474 has the login to check wheather to convert to database format
			//  For workflows update field tasks is deleted all the lineitems.
			//	$focus->isLineItemUpdate = false;

			$recordModel->setHandlerExceptions(['disableWorkflow' => true]);
			$isNew = $recordModel->isNew();
			if ($isNew) {
				$recordModel->isNew = false;
			}
			$recordModel->save();
			if ($isNew) {
				$recordModel->isNew = true;
			}
			// Reverting back the action name as there can be some dependencies on this.
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
