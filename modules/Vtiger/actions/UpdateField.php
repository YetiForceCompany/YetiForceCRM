<?php

/**
 * Update field with current time
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Vtiger_UpdateField_Action extends Vtiger_BasicAjax_Action
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$moduleName = $request->getModule();
		$fieldName = $request->get('fieldName');
		if (!App\Privilege::isPermitted($moduleName, 'EditView', $recordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		if (!$recordModel->isEditable()) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
		if (!App\Field::getFieldPermission($moduleName, $fieldName)) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Process
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$fieldName = $request->get('fieldName');
		$fieldModel = Vtiger_Field_Model::getInstance($fieldName, Vtiger_Module_Model::getInstance($moduleName));
		$updateField = Vtiger_UpdaterField_Helper::getInstance();
		$updateField->setFieldModel($fieldModel);
		$value = $updateField->getValue();
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
		$recordModel->set($fieldName, $value);
		$recordModel->save();
		$response = new Vtiger_Response();
		$response->setResult([$fieldName => $value]);
		$response->emit();
	}
}
