<?php

/**
 * Update field with current time
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Vtiger_UpdateDateField_Action extends Vtiger_BasicAjax_Action
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordId = $request->get('record');
		$fieldName = $request->get('fieldName');
		$fieldModel = Vtiger_Field_Model::getInstance($fieldName, $moduleModel);
		$fieldType = $fieldModel->getFieldDataType();
		$value = '';
		if ($fieldType = 'date') {
			$value = date('Y-m-d');
		} else if ($fieldType = 'datetime') {
			$value = date('Y-m-d H:i:s');
		} else if ($fieldType = 'time') {
			$value = date('H:i:s');
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$recordModel->set($fieldName, $value);
		$recordModel->save();
		$result[$fieldName] = $value;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
