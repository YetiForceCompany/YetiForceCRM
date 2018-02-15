<?php

/**
 * Advanced permission delete action model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_AdvancedPermission_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	public function process(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$recordModel = Settings_AdvancedPermission_Record_Model::getInstance($request->getInteger('record'));
		$recordModel->delete();

		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		header("Location: {$moduleModel->getDefaultUrl()}");
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateReadAccess();
	}
}
