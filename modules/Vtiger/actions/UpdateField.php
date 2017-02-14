<?php

/**
 * Update field with current time
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Vtiger_UpdateField_Action extends Vtiger_BasicAjax_Action
{

	public function checkPermission(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		$fieldName = $request->get('fieldName');
		if (!App\Privilege::isPermitted($moduleName, 'EditView', $recordId)) {
			throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		if (!$recordModel->isEditable()) {
			throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
		if (!App\Field::getFieldPermission($moduleName, $fieldName)) {
			throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$fieldName = $request->get('fieldName');
		$fieldModel = Vtiger_Field_Model::getInstance($fieldName, Vtiger_Module_Model::getInstance($moduleName));
		$updateField = Vtiger_UpdaterField_Helper::getInstance();
		$updateField->setFieldModel($fieldModel);
		$value = $updateField->getValue();
		$recordModel = Vtiger_Record_Model::getInstanceById($request->get('record'), $moduleName);
		$recordModel->set($fieldName, $value);
		$recordModel->save();
		$result[$fieldName] = $value;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
