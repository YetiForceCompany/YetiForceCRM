<?php

/**
 * Advanced permission delete action model class
 * @package YetiForce.Settings.Action
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_AdvancedPermission_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{

	public function process(Vtiger_Request $request)
	{
		$record = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);
		$recordModel = Settings_AdvancedPermission_Record_Model::getInstance($record);
		$recordModel->delete();

		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		header("Location: {$moduleModel->getDefaultUrl()}");
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateReadAccess();
	}
}
