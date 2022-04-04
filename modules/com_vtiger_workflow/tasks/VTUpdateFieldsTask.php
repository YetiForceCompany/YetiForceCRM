<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

class VTUpdateFieldsTask extends VTTask
{
	public $executeImmediately = true;

	public function getFieldNames()
	{
		return ['field_value_mapping'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $rawRecordModel
	 */
	public function doTask($rawRecordModel)
	{
		$recordModel = \Vtiger_Record_Model::getCleanInstance($rawRecordModel->getModuleName());
		$recordModel->setData($rawRecordModel->getData());
		$recordModel->ext = $rawRecordModel->ext;
		$recordModel->isNew = false;
		$moduleFields = $recordModel->getModule()->getFields();
		$fieldValueMapping = [];
		if (!empty($this->field_value_mapping)) {
			$fieldValueMapping = \App\Json::decode($this->field_value_mapping);
		}
		if (!empty($fieldValueMapping) && \count($fieldValueMapping) > 0) {
			foreach ($fieldValueMapping as $fieldInfo) {
				$fieldName = $fieldInfo['fieldname'];
				if (!isset($moduleFields[$fieldName]) || !$moduleFields[$fieldName]->isActiveField()) {
					continue;
				}
				$fieldValueType = $fieldInfo['valuetype'];
				$fieldValue = trim($fieldInfo['value']);
				if ('expression' === $fieldValueType) {
					require_once 'modules/com_vtiger_workflow/expression_engine/include.php';
					$parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($fieldValue)));
					$expression = $parser->expression();
					$exprEvaluater = new VTFieldExpressionEvaluater($expression);
					$fieldValue = $exprEvaluater->evaluate($recordModel);
				} elseif ('fieldname' === $fieldValueType) {
					$fieldValue = $recordModel->get($fieldValue);
				} else {
					if (preg_match('/([^:]+):boolean$/', $fieldValue, $match)) {
						$fieldValue = $match[1];
						if ('true' == $fieldValue) {
							$fieldValue = '1';
						} else {
							$fieldValue = '0';
						}
					}
				}
				$recordModel->set($fieldName, App\Purifier::decodeHtml($fieldValue));
			}
			if ($recordModel->getPreviousValue()) {
				$recordModel->setHandlerExceptions(['disableHandlerClasses' => ['Vtiger_Workflow_Handler']]);
				$recordModel->save();
				foreach (array_keys($recordModel->getPreviousValue()) as $fieldName) {
					$rawRecordModel->set($fieldName, $recordModel->get($fieldName));
				}
			}
		}
	}
}
