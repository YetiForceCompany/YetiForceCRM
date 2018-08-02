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
	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$recordModel = Settings_AdvancedPermission_Record_Model::getInstance($request->getInteger('record'));
		$recordModel->delete();
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
